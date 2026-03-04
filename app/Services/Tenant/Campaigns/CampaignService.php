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

        // 1. Validar si ya recibió regalo en esta campaña (si no es 'todas_op')
        if ($campaign->assignment_type !== 'todas_op') {
            $alreadyReceived = $campaign->customers()
                ->where('customer_id', $customer->id)
                ->exists();

            if ($alreadyReceived) {
                return false;
            }
        } else {
            // Si es 'todas_op', validar si ya recibió regalo para ESTA remisión específica
            if ($remissionId) {
                $alreadyReceivedInRemission = DB::connection('tenant')
                    ->table('cmp_campaign_customers')
                    ->where('campaign_id', $campaign->id)
                    ->where('customer_id', $customer->id)
                    ->where('delivered_at', '!=', null)
                    // Nota: Aquí necesitaríamos guardar el remission_id en la tabla pivot si queremos esta precisión
                    // Por ahora, si es todas_op, permitimos siempre o por fecha
                    ->exists();
                // Como la tabla actual no tiene remission_id, simplificamos:
                // Si es todas_op, permitimos múltiples entregas pero el usuario debe gestionar el stock
            }
        }

        // 2. Validar según tipo de asignación
        switch ($campaign->assignment_type) {
            case 'todos':
            case 'todas_op':
                return true;

            case 'manual':
                // Para manual, ya validamos arriba si está en la tabla (ya que usamos la tabla pivot para asignar)
                // Pero aquí la lógica sería: ¿Está asignado pero no ha recibido?
                // En nuestra estructura, insertamos en cmp_campaign_customers al ENTREGAR.
                // Si el usuario quiere pre-asignar, necesitaríamos una columna 'status' en el pivot.
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
            // Volver a verificar stock dentro de la transacción
            $freshCampaign = Campaign::lockForUpdate()->find($campaign->id);
            
            if ($freshCampaign->gifts_sent >= $freshCampaign->gift_quantity) {
                return false;
            }

            // Registrar entrega en el pivot
            $campaign->customers()->attach($customer->id, [
                'delivered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                // 'remission_id' => $remissionId // Opcional si añadimos la columna
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
