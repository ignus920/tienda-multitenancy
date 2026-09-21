<?php

namespace App\Livewire\Tenant\Warranties;

use App\Models\Tenant\Sales\VntChatbotWarrantyRequest;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class PublicWarrantyRequest extends Component
{
    use WithFileUploads;

    public $company_name;
    public $reference_number; // Número de Factura
    public $advisor_name;
    public $product_details; // Nombre del producto - Cantidad
    public $description;
    public $media_files = []; // Archivos temporales subidos
    
    public $isSubmitted = false;
    public $requestFolio = '';

    protected $rules = [
        'company_name' => 'required|string|max:255',
        'reference_number' => 'required|string|max:100', // Factura
        'advisor_name' => 'nullable|string|max:150',
        'product_details' => 'required|string|max:255',
        'description' => 'required|string',
        'media_files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:10240', // Max 10MB per file
    ];

    protected $messages = [
        'company_name.required' => 'El nombre de la empresa o cliente es obligatorio.',
        'reference_number.required' => 'El número de factura es obligatorio.',
        'product_details.required' => 'Debe indicar el producto y la cantidad.',
        'description.required' => 'Por favor describa el motivo de la garantía.',
        'media_files.*.max' => 'Cada archivo no debe superar los 10 MB.',
        'media_files.*.mimes' => 'Solo se permiten imágenes (JPG, PNG) o videos (MP4, MOV).',
    ];

    public function submit()
    {
        $this->validate();

        $mediaUrls = [];
        if (!empty($this->media_files)) {
            foreach ($this->media_files as $file) {
                // Guarda en el disco 'public' dentro de la carpeta del tenant
                $path = $file->store('warranties/chatbot', 'public');
                $mediaUrls[] = $path;
            }
        }

        $folio = 'GAR-' . strtoupper(Str::random(6));

        VntChatbotWarrantyRequest::create([
            'company_name' => $this->company_name,
            'reference_number' => $this->reference_number,
            'advisor_name' => $this->advisor_name,
            'product_details' => $this->product_details,
            'description' => $this->description . "\n\n(Radicado Público: " . $folio . ")",
            'media_urls' => $mediaUrls,
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
