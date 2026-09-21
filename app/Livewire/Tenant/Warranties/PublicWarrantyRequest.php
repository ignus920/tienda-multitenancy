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
    public $advisor_name;

    // Paso 3
    public $description;
    public $media_files = [];
    
    public $isSubmitted = false;
    public $requestFolio = '';

    protected $messages = [
        'nit.required' => 'El NIT de la empresa es obligatorio.',
        'invoice_number.required' => 'El número de factura es obligatorio.',
        'description.required' => 'Por favor describa el motivo de la garantía.',
        'media_files.*.max' => 'Cada archivo no debe superar los 10 MB.',
        'media_files.*.mimes' => 'Solo se permiten imágenes (JPG, PNG) o videos (MP4, MOV).',
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
        // Haremos una búsqueda flexible: validamos si el NIT existe en la BD
        $customer = Customer::where('identification_number', $this->nit)->first();
        if (!$customer) {
            $this->addError('nit', 'El NIT ingresado no está registrado en el sistema.');
            return;
        }

        // Validación simple aprobada
        $this->invoice_id = $invoice->id;
        $this->company_name = $customer->business_name ?? $customer->name;
        
        // Cargar productos de la factura
        $this->foundProducts = [];
        $this->selectedProducts = [];
        
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

    public function validateStep2()
    {
        // Verificar que al menos un producto fue seleccionado
        $hasSelected = false;
        foreach ($this->selectedProducts as $id => $data) {
            if (isset($data['selected']) && $data['selected'] == true) {
                $hasSelected = true;
                
                // Validar cantidades
                if (!isset($data['qty']) || $data['qty'] < 1) {
                    $this->addError("selectedProducts.{$id}.qty", 'La cantidad debe ser mayor a 0');
                    return;
                }
            }
        }

        if (!$hasSelected) {
            $this->addError('general_products', 'Debe seleccionar al menos un producto para aplicar la garantía.');
            return;
        }

        $this->currentStep = 3;
    }
    
    public function previousStep()
    {
        $this->currentStep--;
    }

    public function submit()
    {
        $this->validate([
            'description' => 'required|string',
            'media_files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
        ]);

        $mediaUrls = [];
        if (!empty($this->media_files)) {
            foreach ($this->media_files as $file) {
                $path = $file->store('warranties/chatbot', 'public');
                $mediaUrls[] = $path;
            }
        }

        $folio = 'GAR-' . strtoupper(Str::random(6));

        // Construir string legible para el detalle de productos (retrocompatibilidad)
        $productDetailsString = [];
        $jsonProducts = [];
        foreach ($this->foundProducts as $prod) {
            $id = $prod['id'];
            if (isset($this->selectedProducts[$id]) && $this->selectedProducts[$id]['selected']) {
                $qty = $this->selectedProducts[$id]['qty'];
                $productDetailsString[] = "{$qty} x {$prod['name']} ({$prod['reference']})";
                
                $jsonProducts[] = [
                    'id' => $id,
                    'name' => $prod['name'],
                    'qty' => $qty
                ];
            }
        }

        $detailsText = implode("\n", $productDetailsString);

        // Guardar
        $request = VntChatbotWarrantyRequest::create([
            'company_name' => $this->company_name . ' (NIT: ' . $this->nit . ')',
            'reference_number' => $this->invoice_number,
            'advisor_name' => $this->advisor_name,
            'product_details' => $detailsText, // Guarda el texto para la vista
            'description' => $this->description . "\n\n(Radicado Público: " . $folio . ")",
            'media_urls' => $mediaUrls,
            'status' => 'pending',
        ]);

        // Si quisieras agregar un campo JSON en el futuro para enlazar automáticamente los items:
        // $request->products_json = json_encode($jsonProducts);
        // $request->save();

        $this->requestFolio = $folio;
        $this->isSubmitted = true;
    }

    public function render()
    {
        return view('livewire.tenant.warranties.public-warranty-request')
            ->layout('layouts.guest');
    }
}
