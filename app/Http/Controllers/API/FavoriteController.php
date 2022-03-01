<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\Property;
use App\Models\User;


class FavoriteController extends Controller 
{
    use AuthenticatesUsers;

    public function index(Request $request)
    {
        $authUser = $this->guard()->user();
        if ($authUser) {
            $user = User::find($authUser->id);
            $ids = $user->favorites()->pluck('id');
            $count = $user->favorites()->pluck('id')->count();
            $data = Property::whereIn('id', $ids)->paginate($request->input('page-size'));
            $perPage = $request->input('page-size');
            $pageNum = $request->input('page');
            $result = $this->paginator($data, $pageNum, $perPage ,$count);
            return response()->json($result);
        } else {
            $ids = explode(',', $request->input('ids'));
            $favorites = Property::whereIn('id', $ids)->get();
            return response()->json($favorites);
        }
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
    public function store(Request $request, $id)
    {
        $user = User::find($this->guard()->user()->id);
        
        $property = Property::find($id);
        if (!$property) {
            return response(json_encode([
                'success' => false,
                'message' => 'Property not found'
            ]), 404)->header('Content-Type', 'text/json');
        }

        $user->favorites()->attach($property);
        
        return response()->json(['success' => true, 'favorites' => $user->favorites]);
    }


    public function delete(Request $request, $id)
    {
        $user = User::find($this->guard()->user()->id);

        $property = Property::find($id);
        if ($property == null) {
            return response(json_encode([
                'success' => false,
                'message' => 'Property not found'
            ]), 404)->header('Content-Type', 'text/json');
        }
        $user->favorites()->detach($property);
        return response()->json(['success' => true, 'favorites' => $user->favorites]);
    }

    public function guard() 
    {
        return Auth::guard('api');
    }
}