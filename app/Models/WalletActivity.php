<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};

class WalletActivity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
        'wallet_id'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function detail()
    {
        return $this->hasOne(WalletActivityDetail::class);
    }
}
