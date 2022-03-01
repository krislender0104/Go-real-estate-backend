<?php

namespace App\Models;

use Creativeorange\Gravatar\Gravatar;
use Illuminate\Database\Eloquent\Model;
class PropertyFeature extends Model
{
    protected $table = 'property_features';
    protected $fillable = [
        'id',
        'name',
        'created_at',
        'updated_at'
    ];
    public function properties()
    {
        return $this->belongsToMany('App\Models\Property', 'property_feature_pivot', 'property_id', 'property_feature_id');
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }
}