<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationStatus extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'inv_reservation_statuses';

    protected $fillable = [
        'name',
    ];
}
