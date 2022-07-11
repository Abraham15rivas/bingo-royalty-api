<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Meeting extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'cardboard_number',
        'line_play',
        'full_cardboard',
        'start',
        'end',
        'status',
        'numbers'
    ];

    public function users()
    {
        return $this->BelongsToMany(User::class);
    }
}