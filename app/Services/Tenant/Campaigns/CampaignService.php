<?php

namespace App\Services\Tenant\Campaigns;

use App\Models\Tenant\Campaigns\Campaign;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Tenant\Customer\VntContacts;
use App\Models\Tenant\Remissions\InvRemissions;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CampaignService
{
    /**
     * Obtiene las campañas activas para la fecha actual.
     */
    public function getActiveCampaigns()
    {
        return Campaign::where('status', 'activo')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->whereColumn('gifts_sent', '<', 'gift_quantity')
            ->get();
    }

    /**
     * Valida si un cliente (Empresa o Contacto) es elegible para una campaña específica.
     */
    public function isEligible($customer, Campaign $campaign, $remissionId = null)
    {
        // Normalizar a VntCompany si recibimos un contacto
        if ($customer instanceof VntContacts) {
            $customer = $customer->company;
        }

        if (!$customer instanceof VntCompany) {
            return false;
        }

        // Validar que la campaña esté dentro del rango de fechas y activa
        $today = now()->startOfDay();
        if ($campaign->status !== 'activo'
            || $today->lt($campaign->start_date)
            || $today->gt($campaign->end_date)
        ) {
            return false;
        }

        // Si ya se asignó regalo para esta remisión específica, no asignar de nuevo
        if ($remissionId) {
            $alreadyForThisRemission = DB::connection('tenant')
                ->table('cmp_campaign_customers')
                ->where('campaign_id', $campaign->id)
                ->where('remission_id', $remissionId)
                ->exists();

            if ($alreadyForThisRemission) {
                return false;
            }
        }

        // Validar si ya recibió regalo en esta campaña (para tipos que solo permiten uno)
        if ($campaign->assignment_type !== 'todas_op') {
            $alreadyReceived = $campaign->customers()
                ->where('customer_id', $customer->id)
                ->exists();

            if ($alreadyReceived) {
                return false;
            }
        }

        // Validar según tipo de asignación
        switch ($campaign->assignment_type) {
            case 'todos':
            case 'todas_op':
                return true;

            case 'manual':
                return true;

            case 'antiguos_frecuentes':
                return $this->isOldOrFrequentCustomer($customer);

            default:
                return false;
        }
    }

    /**
     * Valida si un cliente es antiguo (90+ días) o frecuente (3+ pedidos).
     */
    public function isOldOrFrequentCustomer($customer)
    {
        // Normalizar a VntCompany si recibimos un contacto
        if ($customer instanceof VntContacts) {
            $customer = $customer->company;
        }

        if (!$customer instanceof VntCompany) {
            return false;
        }

        $daysOld = 90;
        $minOrders = 3;

        // Antigüedad por fecha de creación
        $isOld = $customer->created_at->diffInDays(now()) >= $daysOld;

        if ($isOld) {
            return true;
        }

        // Frecuencia por cantidad de remisiones (pedidos) entregados/registrados
        $orderCount = InvRemissions::whereHas('quote', function($q) use ($customer) {
                $q->where('customerId', $customer->id);
            })
            ->whereIn('status', ['ENTREGADO', 'REGISTRADO'])
            ->count();

        return $orderCount >= $minOrders;
    }

    /**
     * Registra la entrega de un regalo y actualiza el stock de la campaña.
     */
    public function registerGiftDelivery($customer, Campaign $campaign, $remissionId = null)
    {
        // Normalizar a VntCompany
        if ($customer instanceof VntContacts) {
            $customer = $customer->company;
        }

        if (!$customer instanceof VntCompany) {
            return false;
        }

        return DB::connection('tenant')->transaction(function () use ($customer, $campaign, $remissionId) {
            // Volver a verificar dentro de la transacción: stock y fechas
            $freshCampaign = Campaign::lockForUpdate()->find($campaign->id);

            $today = now()->startOfDay();
            if ($freshCampaign->status !== 'activo'
                || $today->lt($freshCampaign->start_date)
                || $today->gt($freshCampaign->end_date)
            ) {
                return false;
            }

            if ($freshCampaign->gifts_sent >= $freshCampaign->gift_quantity) {
                return false;
            }

            // Registrar entrega en el pivot
            $campaign->customers()->attach($customer->id, [
                'delivered_at'  => now(),
                'remission_id'  => $remissionId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Incrementar contador
            $freshCampaign->increment('gifts_sent');

            // Cerrar campaña si llegó al límite
            if ($freshCampaign->gifts_sent >= $freshCampaign->gift_quantity) {
                $freshCampaign->update(['status' => 'anulado']);
            }

            return true;
        });
    }

    /**
     * Busca regalos disponibles para una remisión específica.
     */
    public function checkAndAssignGift(InvRemissions $remission)
    {
        if (!$remission->quote || !$remission->quote->customer) {
            return null;
        }

        $customer = $remission->quote->customer;
        $activeCampaigns = $this->getActiveCampaigns();

        foreach ($activeCampaigns as $campaign) {
            if ($this->isEligible($customer, $campaign, $remission->id)) {
                if ($this->registerGiftDelivery($customer, $campaign, $remission->id)) {
                    return $campaign;
                }
            }
        }

        return null;
    }
}
