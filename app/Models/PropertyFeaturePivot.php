<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PropertyFeaturePivot extends Model
{
    protected $table = 'property_feature_pivot';
    public $timestamps = false; 
    
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }
}
