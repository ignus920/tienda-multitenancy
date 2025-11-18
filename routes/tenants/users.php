<?php

use Illuminate\Support\Facades\Route;

Route::get('/users', function () {
    return view('livewire/tenant/Users/users');
})->name('users.users');
