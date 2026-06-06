<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'oil_price',
        'capital_cost',
        'non_capital_cost',
        'opex_per_year',
        'tax_rate',
        'known_years',
        'prediction_years',
        'depreciation_years',
        'depreciation_method',
        'discount_rate',
        'total_reserve',
        'decline_rate',
        'custom_depreciation_rate',
        'decline_start_year',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productionData()
    {
        return $this->hasMany(ProductionData::class);
    }

    public function calculations()
    {
        return $this->hasMany(Calculation::class);
    }
}
