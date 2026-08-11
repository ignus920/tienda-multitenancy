<?php

namespace App\Models\Tenant\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrdUsersProcess extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'prd_users_process';

    protected $fillable = [
        'operator_user_id',
        'process_id',
        'status',
    ];
}
