<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionData extends Model
{
    protected $table = 'production_data';

    protected $fillable = [
        'project_id',
        'year',
        'production',
        'is_predicted',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
