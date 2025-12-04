<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\PettyCash\PaymentQuote;

Route::get('/payment/quote/{quoteId?}', PaymentQuote::class)
    ->name('tenant.payment.quote');