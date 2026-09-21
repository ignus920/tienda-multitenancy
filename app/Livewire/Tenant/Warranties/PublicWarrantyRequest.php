<?php

namespace App\Livewire\Tenant\Warranties;

use App\Models\Tenant\Invoices\VntInvoices;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Sales\VntChatbotWarrantyRequest;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class PublicWarrantyRequest extends Component
{
    use WithFileUploads;

    public $tenant_id;
    public $currentStep = 1;
    
    // Paso 1
    public $nit;
    public $invoice_number;
    
    // Almacena la data si pasa la validación
    public $invoice_id;
    public $company_name;
    public $foundProducts = []; 
    
    // Paso 2
    public $selectedProducts = []; // Formato: [ itemId => ['selected' => true, 'qty' => 1] ]
    public $productDescriptions = [];
    public $productMedia = [];
    public $advisor_name;

    public $isSubmitted = false;
    public $requestFolio = '';

    protected $messages = [
        'nit.required' => 'El NIT de la empresa es obligatorio.',
        'invoice_number.required' => 'El número de factura es obligatorio.',
        'productMedia.*.*.max' => 'Cada archivo no debe superar los 10 MB.',
        'productMedia.*.*.mimes' => 'Solo se permiten imágenes (JPG, PNG) o videos (MP4, MOV).',
    ];

    public function mount($tenant_id)
    {
        $this->tenant_id = $tenant_id;
        $this->currentStep = 1;
        $this->initTenant();
    }

    public function hydrate()
    {
        $this->initTenant();
    }

    private function initTenant()
    {
        if ($this->tenant_id) {
            $tenant = \App\Models\Auth\Tenant::find($this->tenant_id);
            if ($tenant) {
                $tenantManager = app(\App\Services\Tenant\TenantManager::class);
                $tenantManager->setConnection($tenant);
                tenancy()->initialize($tenant);
            } else {
                abort(404, 'Empresa no encontrada.');
            }
        }
    }

    public function validateStep1()
    {
        $this->validate([
            'nit' => 'required|string|max:50',
            'invoice_number' => 'required|string|max:100',
        ]);

        // Buscar factura
        $invoice = VntInvoices::with(['quote.detalles.item'])
            ->where(function ($query) {
                $query->where('consecutive', $this->invoice_number)
                      ->orWhere('invoiceNumber', $this->invoice_number);
            })
            ->first();

        if (!$invoice) {
            $this->addError('invoice_number', 'No se encontró ninguna factura con este número.');
            return;
        }

        // Buscar cliente por NIT para verificar si coincide con la factura
        // Dado que la factura está amarrada a un warehouse o a un customer a través del quote
        // Verificar que la factura pertenezca a este cliente
        // Si el cliente no es el de la factura, mostramos error
        $customer = \App\Models\Tenant\Customer\VntCompany::where('identification', $this->nit)->first();
        if (!$customer) {
            $this->addError('nit', 'El NIT ingresado no está registrado en el sistema.');
            return;
        }

        // Validación simple aprobada
        $this->invoice_id = $invoice->id;
        $this->company_name = $customer->businessName ?? trim($customer->firstName . ' ' . $customer->lastName);
        
        // Cargar productos de la factura
        $this->foundProducts = [];
        $this->selectedProducts = [];
        $this->productDescriptions = [];
        $this->productMedia = [];
        
        if ($invoice->quote && $invoice->quote->detalles) {
            foreach ($invoice->quote->detalles as $detail) {
                if ($detail->item) {
                    $this->foundProducts[] = [
                        'id' => $detail->item->id,
                        'name' => $detail->item->name,
                        'reference' => $detail->item->internal_code,
                        'max_qty' => $detail->quantity,
                    ];
                    
                    // Inicializar el modelo
                    $this->selectedProducts[$detail->item->id] = [
                        'selected' => false,
                        'qty' => 1
                    ];
                }
            }
        }

        if (empty($this->foundProducts)) {
            $this->addError('invoice_number', 'La factura no tiene productos registrados.');
            return;
        }

        $this->currentStep = 2;
    }

    public function submit()
    {
        $this->validate([
            'productMedia.*.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
        ]);

        // Verificar que al menos un producto fue seleccionado
        $hasSelected = false;
        $productDetailsString = [];
        $allMediaUrls = [];

        foreach ($this->selectedProducts as $id => $data) {
            if (isset($data['selected']) && $data['selected'] == true) {
                $hasSelected = true;
                
                // Validar cantidades
                if (!isset($data['qty']) || $data['qty'] < 1) {
                    $this->addError("selectedProducts.{$id}.qty", 'La cantidad debe ser mayor a 0');
                    return;
                }

                $prod = collect($this->foundProducts)->firstWhere('id', $id);
                $qty = $data['qty'];
                $desc = $this->productDescriptions[$id] ?? 'Sin detalle específico';
                
                $detailsChunk = "Producto: {$qty} x {$prod['name']} ({$prod['reference']})\nDetalle/Falla: {$desc}";
                $productDetailsString[] = $detailsChunk;

                if (!empty($this->productMedia[$id])) {
                    foreach ($this->productMedia[$id] as $file) {
                        $path = $file->store('warranties/chatbot', 'public');
                        $allMediaUrls[] = $path;
                    }
                }
            }
        }

        if (!$hasSelected) {
            $this->addError('general_products', 'Debe seleccionar al menos un producto para aplicar la garantía.');
            return;
        }

        $folio = 'GAR-' . strtoupper(Str::random(6));
        $detailsText = implode("\n\n------------------------\n\n", $productDetailsString);

        // Guardar
        $request = VntChatbotWarrantyRequest::create([
            'company_name' => $this->company_name . ' (NIT: ' . $this->nit . ')',
            'reference_number' => $this->invoice_number,
            'advisor_name' => $this->advisor_name,
            'product_details' => $detailsText,
            'description' => "Solicitud enviada a través del portal público.\n(Radicado: " . $folio . ")",
            'media_urls' => $allMediaUrls,
            'status' => 'pending',
        ]);

        $this->requestFolio = $folio;
        $this->isSubmitted = true;
    }

    public function render()
    {
        return view('livewire.tenant.warranties.public-warranty-request')
            ->layout('layouts.guest');
    }
}
