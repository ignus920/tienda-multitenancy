<?php

namespace App\Models\Tenant\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VntFollowupStatus extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'vnt_followup_statuses';

    protected $fillable = [
        'name',
        'class_color',
        'status',
    ];

    public $timestamps = false; // El campo created_at es manejado por la DB
}
