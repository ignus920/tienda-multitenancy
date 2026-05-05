<?php

namespace App\Models\Tenant\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Quoter\VntQuote;
use App\Models\Auth\User;

class VntQuoteFollowup extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'vnt_quote_followups';

    protected $fillable = [
        'quote_id',
        'user_id',
        'status_id',
        'comment',
    ];

    public $timestamps = false; // El campo created_at es manejado por la DB

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function quote()
    {
        return $this->belongsTo(VntQuote::class, 'quote_id');
    }

    public function status()
    {
        return $this->belongsTo(VntFollowupStatus::class, 'status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
