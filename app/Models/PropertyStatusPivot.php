<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PropertyStatusPivot extends Model
{
    protected $table = 'property_status_pivot';
    public $timestamps = false; 
    
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }
}
