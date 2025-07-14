<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParameterReport extends Model
{
    protected $fillable = [
        'parameter',
        'normal_range',
        'weekly_status',
        'information'
    ];

    protected $table = 'parameter_reports';

    public function getRecent()
    {
        return $this->limit(10)->orderBy('id', 'DESC')->get();
    }
}

