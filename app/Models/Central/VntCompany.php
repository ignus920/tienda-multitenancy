<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VntCompany extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'central';
    protected $table = 'vnt_companies';

    protected $fillable = [
        'businessName',
        'billingEmail',
        'firstName',
        'integrationDataId',
        'identification',
        'checkDigit',
        'lastName',
        'secondLastName',
        'secondName',
        'status',
        'typePerson',
        'typeIdentificationId',
        'regimeId',
        'code_ciiu',
        'fiscalResponsabilityId',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'integrationDataId' => 'integer',
            'checkDigit' => 'integer',
            'status' => 'integer',
            'typeIdentificationId' => 'integer',
            'regimeId' => 'integer',
            'fiscalResponsabilityId' => 'integer',
        ];
    }
}
