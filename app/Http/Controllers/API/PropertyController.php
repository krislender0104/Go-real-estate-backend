<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Property;
use App\Models\PropertyGallery;
use App\Models\PropertyPlan;
use App\Models\PropertySell;
use App\Models\Application;
use App\Models\Contract;
use App\Models\HistoryPayment;
use App\Models\PropertyDetail;
use App\Models\User;
use App\Models\PropertyType;
use App\Models\PropertyStatus;
use App\Models\PropertyStatusPivot;
use App\Models\PropertyFeature;
use App\Models\PropertyFeaturePivot;

use App\Logic\Property\PropertyRepository;


class PropertyController extends Controller 
{
    protected $repository;

    public function __construct(PropertyRepository $repo)
    {
        $this->repository = $repo;
    }

    public function index(Request $request)
    {
        $agentId = intval($request->input('agent-id'));
        $area_from = intval($request->input('area-from'));
        $area_to = intval($request->input('area-to'));
        $bathrooms_from = intval($request->input('bathrooms-from'));
        $bathrooms_to = intval($request->input('bathrooms-to'));
        $bedrooms_from = intval($request->input('bedrooms-from'));
        $bedrooms_to = intval($request->input('bedrooms-to'));
        $garages_from = intval($request->input('garages-from'));
        $garages_to = intval($request->input('garages-to'));
        $price_from = intval($request->input('price-from'));
        $price_to = intval($request->input('price-to'));
        $yearBuilt_from = intval($request->input('yearBuilt-from'));
        $yearBuilt_to = intval($request->input('yearBuilt-to'));

        $company = intval($request->input('company'));
        $propertyType = intval($request->input('propertyType'));

        $city = $request->input('city');
        $neighborhood = $request->input('neighborhood');
        $street = $request->input('street');

        $zipCode = intval($request->input('zipCode'));

        $propertyStatus = $request->input('propertyStatus');

        $feautres = $request->input('features');

        $property = Property::query();
        $property_ids = null;

        if($agentId)
        {
          $user_type = User::where('id', $agentId)->get()->pluck('user_type');
          
          if($user_type[0] == 'Realtor' || $user_type[0] == 'PropertyManager')
          {
            $property = Property::where('user_id', $agentId);
          }
        }

        if($area_from || $area_to || $bathrooms_from || $bathrooms_to || $bedrooms_from || $bedrooms_to 
          || $garages_from || $garages_to || $price_from || $price_to || $yearBuilt_from || $yearBuilt_to || $company || $propertyType 
          || $city || $neighborhood || $street || $zipCode || $propertyStatus || $feautres)
        {
          if($area_from)
          {
            $property_ids = PropertyDetail::where('name','area')->where('value', '>=', $area_from)->where('value', '<=', $area_to)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }
          if($bathrooms_from)
          {
            $property_ids = PropertyDetail::where('name','bathrooms')->where('value', '>=', $bathrooms_from)->where('value', '<=', $bathrooms_to)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }
          
          if($bedrooms_from)
          {
            $property_ids = PropertyDetail::where('name','bedrooms')->where('value', '>=', $bedrooms_from)->where('value', '<=', $bedrooms_to)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }
          
          if($garages_from)
          {
            $property_ids = PropertyDetail::where('name','garages')->where('value', '>=', $garages_from)->where('value', '<=', $garages_to)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }
          
          if($price_from)
          {
            $property_ids = PropertyDetail::where('name','price_dollar_sale')->where('value', '>=', $price_from)->where('value', '<=', $price_to)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }
          
          if($yearBuilt_from)
          {
            $property_ids = PropertyDetail::where('name','year')->where('value', '>=', $yearBuilt_from)->where('value', '<=', $yearBuilt_to)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }
          
          if($company)
          {
            $user_ids = User::where('company_id', $company)->get()->pluck('id');
            $property = $property->whereIn('user_id', $user_ids);
          }

          if($propertyType)
          {
            $property = $property->where('property_type_id', $propertyType);
          }
          
          if($city)
          {
            $property_ids = PropertyDetail::where('name','city')->where('value',$city)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }

          if($street)
          {
            $property_ids = PropertyDetail::where('name','street')->where('value',$street)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }

          if($neighborhood)
          {
            $property_ids = PropertyDetail::where('name','neighborhood')->where('value',$neighborhood)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }

          if($zipCode)
          {
            $property_ids = PropertyDetail::where('name','zipcode')->where('value',$zipCode)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }

          if($propertyStatus)
          {
            $propertyStatus_ids = explode(',', $propertyStatus);
            $property_ids = PropertyStatusPivot::whereIn('property_status_id', $propertyStatus_ids)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }

          if($feautres)
          {
            $feautres_ids = explode(',', $feautres);
            $property_ids = PropertyFeaturePivot::whereIn('property_feature_id', $feautres_ids)->get()->pluck('property_id');
            $property = $property->whereIn('id', $property_ids);
          }
        }
        $count = $property->count();
        $data = $property->paginate($request->input('page-size'));
        $pageNum = $request->input('page');
        $perPage = $request->input('page-size');
        $result = $this->paginator($data, $pageNum, $perPage ,$count);
        
        return response()->json($result);
    }

    public function paginator($items, $page, $perPage, $length) 
    { 

      $paginatedItems = $items;
      
      $totalPages = $length / $perPage;
      $result['data'] = $paginatedItems;
      $result['pagination']['page'] = $page;
      $result['pagination']['perPage'] = $perPage;
      $result['pagination']['prePage'] = $page - 1 ? $page - 1 : null;
      $result['pagination']['nextPage'] = ($totalPages > $page) ? $page + 1 : null;
      $result['pagination']['total'] = $length;
      $result['pagination']['totalPages'] = $totalPages;
      return $result;
    }

    
    public function application($id) 
    {
        $application = Application::find($id);
        $contract = $application->contract;
        if ($contract) {
            $application = $application->toArray();
            $property = $contract->property;
            $history_payment = $contract->historyPayment;
            $application['contract'] = $contract->toArray();
            $application['history_payment'] = $history_payment->toArray();
            $application['property'] = $property->toArray();
        }
        else
        {
            $res = $application;
            $application = $application->toArray();
            $application['property'] = $res->property->toArray();
        }
        return response()->json($application);
    }
    public function purchasedProperties(Request $request)
    {
        $user = auth('api')->user();
        $result = $this->repository->purchasedProperties($user->id);
        return response()->json($result);
    }

    public function stopRent(Request $request, $id)
    {
        $user = auth('api')->user();
        $sell = PropertySell::where('user_id', $user->id)
            ->where('property_id', $id)
            ->first();
        
        if (is_null($sell)) {
            return response()->json(['message' => 'Can\'t stop rent'], 400);
        }

        $sell->delete();
    }

    public function related(Request $request, $id)
    {
        $result = $this->repository->searchProperties([], []);
        return response()->json($result);
    }

    public function show($id)
    {
        $property = Property::find($id);
        if ($property) {
            return response()->json($property);
        } else {
            return response(json_encode([
                'success' => false,
                'message' => 'Property not found'
            ]), 404)->header('Content-Type', 'text/json');
        }
    }

    public function store(Request $request)
    {
        dd($request->all());
        $input = $request->only([
            'id', 'title', 'description', 'featured', 'user', 'property_type', // basic
            'additional_features', 'features', 'status', // associated
            'details'
        ]);

        if (isset($input['id'])) {
            $property = Property::find($input['id']);
            if ($property == null) {
                return response(json_encode([
                    'success' => false,
                    'message' => 'Property not found'
                ]), 404)->header('Content-Type', 'text/json');
            }
        }
    }

    public function storePlan(Request $request, $property_id)
    {
        $input = $request->only(['id', 'name', 'description', 'area', 'rooms', 'baths', 'attachment_id']);
        $property = Property::find($property_id);
        if ($property == null) {
            return response(json_encode([
                'success' => false,
                'message' => 'Property not found'
            ]), 404)->header('Content-Type', 'text/json');
        }
        if (isset($input['id'])) {
            $plan = PropertyPlan::find($input['id']);
            if ($plan == null) {
                return response(json_encode([
                    'success' => false,
                    'message' => 'Plan not found'
                ]), 404)->header('Content-Type', 'text/json');
            }
        }
        
        $plan = $this->repository->storePlan($input, $property_id);
        return response()->json(['success' => true, 'plan' => $plan]);
    }

    public function update(Request $request, $id)
    {

    }

    public function delete(Request $request, $id)
    {
        $property = Property::find($id);
        if ($property == null) {
            return response(json_encode([
                'success' => false,
                'message' => 'Property not found'
            ]), 404)->header('Content-Type', 'text/json');
        }
        $property->delete();
        return response()->json(['success' => true]);
    }

    public function deletePlan(Request $request, $id)
    {
        $plan = PropertyPlan::find($id);
        if ($plan == null) {
            return response(json_encode([
                'success' => false,
                'message' => 'Plan not found'
            ]), 404)->header('Content-Type', 'text/json');
        }
        $plan->gallery()->delete();
        $plan->delete();
        return response()->json(['success' => true]);
    }

    public function deleteGallery(Request $request, $id)   
    {
        $gallery = PropertyGallery::find($id);
        if ($gallery == null) {
            return response(json_encode([
                'success' => false,
                'message' => 'Gallery not found'
            ]), 404)->header('Content-Type', 'text/json');
        }
        $gallery->delete();
        return response()->json(['success' => true]);
    }
}