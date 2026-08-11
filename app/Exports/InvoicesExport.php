<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InvoicesExport implements FromView, ShouldAutoSize
{
    protected $invoices;
    protected $methodPayments;
    protected $dateTitle;

    public function __construct($invoices, $methodPayments, $dateTitle)
    {
        $this->invoices = $invoices;
        $this->methodPayments = $methodPayments;
        $this->dateTitle = $dateTitle;
    }

    public function view(): View
    {
        return view('livewire.tenant.invoices.invoices-excel', [
            'invoices' => $this->invoices,
            'methodPayments' => $this->methodPayments,
            'dateTitle' => $this->dateTitle
        ]);
    }
}
