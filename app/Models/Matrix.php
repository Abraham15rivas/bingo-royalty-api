<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};

class Matrix extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cardboards',
        'matrix_group_id'
    ];

    public function matrixGroup()
    {
        return $this->belongsTo(MatrixGroup::class);
    }
}
