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
        'status',
        'numbers',
        'total_collected',
        'accumulated',
        'commission',
        'referred',
        'reearnings_before_39',
        'reearnings_after_39'
    ];

    public function users()
    {
        return $this->BelongsToMany(User::class)->withTimestamps();
    }
}