<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calculation extends Model
{
    protected $fillable = [
        'project_id',
        'year',
        'production',
        'income',
        'capital',
        'non_capital',
        'opex',
        'depreciation',
        'taxable_income',
        'tax',
        'ncf',
        'cumulative_ncf',
        'discount_factor',
        'pv_ncf',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
