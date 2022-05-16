<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'attributes',
        'type_account',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
