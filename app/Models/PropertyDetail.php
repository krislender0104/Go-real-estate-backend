<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PropertyDetail extends Model
{
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public $dates = [
      'created_at',
      'updated_at',
    ];

    public function property()
    {
        return $this->belongsTo('App\Models\Property');
    }
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }
}