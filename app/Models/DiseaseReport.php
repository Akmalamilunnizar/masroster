<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiseaseReport extends Model
{
    protected $fillable = [
        'date',
        'numberof_fish',
        'numberof_sick',
        'precentage',
        'type_disease',
        'information'
    ];

    protected $table = 'disease_reports';

    public function getRecent()
    {
        return $this->limit(255)->orderBy('id', 'DESC')->get();
    }
}
