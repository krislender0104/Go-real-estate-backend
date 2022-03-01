<?php

namespace App\Logic\Property;

use App\Models\Property;
use App\Models\PropertySell;
use App\Models\PropertyPlan;
use App\Models\PropertyFeature;
use App\Models\PropertyDetail;
use App\Models\PropertyStatus;
use App\Models\Application;
use App\Models\PropertyAdditionalFeature;
use App\Models\PropertyFeaturePivot;
use App\Models\PropertyStatusPivot;
use Illuminate\Support\Facades\Auth;
use App\Models\PropertyGallery;
use App\Models\Attachment;

use Illuminate\Support\Facades\Storage;
use App\Upload;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PropertyRepository
{
    public function __construct()
    {
        $this->timestamp = date("Y-m-d H:i:s");
    }
    public function searchProperties($where, $userWhere)
    {
        $query = Property::with('propertySell');
        if (count($userWhere) > 0) {
            $query = $query->whereHas('user', function($qry) use ($userWhere) {
                $qry->where($userWhere);
            });
            $query->where($where);
        } else {
            $query = $query->where($where);
        }

        return $query->get()->toArray();
    }

    public function purchasedProperties($user_id)
    {
        $properties = Application::with(['property', 'contract'])
            ->where('user_id', $user_id)
            ->get()
            ->map(function ($application) {
                $property = $application->property->toArray();
                $property['application'] = $application->toArray();
                if ($application->contract) {
                    if ($application->contract->current_payment_times == 0) {
                        $time = $application->contract->created_at->addDays(10);
                    } else {
                        $time = $application->contract->created_at->addMonths($application->contract->current_payment_times);
                    }
                    $property['next_payment_date'] = $time->format('Y-m-d');
                }
                return $property;
            });

        return $properties;
    }

    public function storePlan($params, $property_id)
    {
     
        $plan;
        if (isset($params['id'])) {

            $plans = PropertyPlan::where('property_id', $params['id'])->get();
            foreach ($plans as $plan)
               $plan->delete();
        } 
        if(!empty($params['plan'])){
            
            $params_1 = $params['plan'];
            for($i = 0; $i <= count($params_1); $i++) {
      
                if(!empty($params_1[$i]["name"] )){
                    
                    $plan = new PropertyPlan;
                    $plan->property_id = $property_id;
                    $plan->name = $params_1[$i]["name"];
                    $plan->description = $params_1[$i]["description"];
                    $plan->area = $params_1[$i]["area"];
                    $plan->rooms = $params_1[$i]["rooms"];
                    $plan->baths = $params_1[$i]["baths"];
                    $plan->save();
                    if(isset($params_1[$i]["image"]))
                    {
                        $files = $params['files'];
                        if( isset($files[$i]))
                        {
                            $file = $files[$i];
                            
                            $destinationPath =  env('APP_UPLOAD_GALLERY_PATH');
                          
                            $current_time = Carbon::now()->timestamp;
                    
                            $original_file_extension = $file->getClientOriginalExtension();
                            $avatar_url = $destinationPath. $current_time . Str::random(10) . ".$original_file_extension";
                        
                            $file->move('storage/uploads/galleries/', $avatar_url);
    
                            $att = new Attachment;
                          
                            $att->big = 'uploads/galleries/'.$file->getClientOriginalName();          
                            $att->medium = 'uploads/galleries/'.$file->getClientOriginalName();          
                            $att->small = 'uploads/galleries/'.$file->getClientOriginalName();                    
                            $att->save();
                          
                            $gallery = new PropertyGallery;
                            $gallery->property_id = $property_id;
                    
                            $gallery->plan_id = $plan->id;
                            $gallery->type = "Image";
                            $gallery->name = "";
                            $gallery->attachment_id = $att->id;
                            $gallery->save();
                        }
                        else
                        {
                          $att = new Attachment;
                          $att->small = $params_1[$i]["image"];      
                          $att->big = $params_1[$i]["image"];                                
                          $att->save();
  
                          $gallery = new PropertyGallery;
                          $gallery->property_id = $property_id;
                  
                          $gallery->plan_id = $plan->id;
                          $gallery->type = "Image";
                          $gallery->name = "";
                          $gallery->attachment_id = $att->id;
                          $gallery->save();
                        }
                        
                    }
                    
                    if(!isset($params_1[$i]["image"]) || $params_1[$i]["image"] == null)
                    {
                        if(isset($params['files'])){
                            $files = $params['files'];
                            if(file_exists($files[$i])){
                                $file = $files[$i];
                                
                                $destinationPath =  env('APP_UPLOAD_GALLERY_PATH');
                               
                                $current_time = Carbon::now()->timestamp;
                        
                                $original_file_extension = $file->getClientOriginalExtension();
                                $avatar_url = $destinationPath. $current_time . Str::random(10) . ".$original_file_extension";
                             
                                $file->move('storage/uploads/galleries/', $avatar_url);
        
                                $att = new Attachment;
                               
                                $att->big = 'uploads/galleries/'.$file->getClientOriginalName();          
                                $att->medium = 'uploads/galleries/'.$file->getClientOriginalName();          
                                $att->small = 'uploads/galleries/'.$file->getClientOriginalName();                    
                                $att->save();
                               
                                $gallery = new PropertyGallery;
                                $gallery->property_id = $property_id;
                        
                                $gallery->plan_id = $plan->id;
                                $gallery->type = "Image";
                                $gallery->name = "";
                                $gallery->attachment_id = $att->id;
                                $gallery->save();
                            }
                        }
                    }
                }

                
            }
        }
        
        return $plan;
    }

    public function storeProperty($params)
    {

        if (isset($params['id'])) {
            $property = Property::find($params['id']);
        } else {
            $property = new Property;
        }
        
        if (isset($params['title'])) {
            $property->title = $params['title'];
        }
        if (isset($params['description'])) {
            $property->description = $params['description'];
        }
        if (isset($params['property_type'])) {
            $property->property_type_id = $params['property_type'];
        }
        if (isset($params['featured'])) {
            $property->featured = $params['featured'];
        }
        if (isset($params['user_id'])) {
            $property->user_id = $params['user_id'];
        } else {
            $property->user_id = Auth::user()->id;
        }

        $property->save();


        if (isset($params['additional_features'])) {
            $afparams = $params['additional_features'];
            foreach ($afparams as $key) {
                $afeature = PropertyAdditionalFeature::where('property_id', $property->id)->where('name', $key)->first();
                if (!$afeature) {
                    $afeature = new PropertyAdditionalFeature;
                    $afeature->property_id = $property->id;
                }
                $afeature->name = $key["name"];
                $afeature->value = $key["value"];
                
                $afeature->save();
            }
        }


        $property_feature_pivots = PropertyFeaturePivot::where('property_id', $property->id)->get();
        foreach($property_feature_pivots as $property_feature_pivot){
            $property_feature_pivot->delete();
        }

        if (isset($params['features'])) {
            foreach($params['features'] as $fid) {
                $property_feature_pivot = new PropertyFeaturePivot;
                $property_feature_pivot->property_id = $property->id;
                $property_feature_pivot->property_feature_id = $fid;
                $property_feature_pivot->save();               
    
            }
        }

        $property_status_pivots = PropertyStatusPivot::where('property_id', $property->id)->get();
        foreach($property_status_pivots as $property_status_pivot){
            $property_status_pivot->delete();
        }
        if (isset($params['status'])) {
            foreach($params['status'] as $fid) {
                $property_status_pivot = new PropertyStatusPivot;
                $property_status_pivot->property_id = $property->id;
                $property_status_pivot->property_status_id = $fid;
                $property_status_pivot->save();               
               
            }
        }
        


        if (isset($params['details'])) {
            foreach ($params['details'] as $param) {
                $detail = PropertyDetail::where('property_id', $property->id)->where('name', $param["name"])->first();
                if (!$detail) {
                    $detail = new PropertyDetail;
                    $detail->property_id = $property->id;
                }
               
                $detail->name = $param["name"];
                $detail->value = $param["value"];
                $detail->save();
            }
        }
        
        $property_galleries = PropertyGallery::where('property_id', $property->id)->where('plan_id', '!=', 0)->where('type', 'Image')->get();
        foreach($property_galleries as $property_gallery){
            $property_gallery->delete();
        }

        $property_galleries = PropertyGallery::where('property_id', $property->id)->where('type', 'Video')->get();
        foreach($property_galleries as $property_gallery){
            $property_gallery->delete();
        }


        if (isset($params['galleries_video'])) {
            foreach($params['galleries_video'] as $fid) {
                $att = new Attachment;
                $att->small = $fid['link'];  
                $att->big = $fid['link'];                                
                $att->save();
                $property_gallery = new PropertyGallery;
                $property_gallery->property_id = $property->id;
                $property_gallery->plan_id = 0;
                $property_gallery->type = "Video";
                $property_gallery->name = $fid['name'];
                $property_gallery->attachment_id = $att->id;
                $property_gallery->save();               
               
            }
        }
        return $property;
    }

}
