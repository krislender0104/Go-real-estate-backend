<?php

namespace App\Http\Controllers;
use Auth;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Role;
use App\Models\Company;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PropertyFeature;
use App\Models\PropertyStatus;
use Illuminate\Support\Facades\DB;
class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      
        return view('main.pages.home');
        

    }
    public function home()
    {

      $currentUser = Auth::user();

      if($currentUser->user_type == 'Customer')
      {

        auth()->logout();
        return redirect('/login');
      }
      if($currentUser->user_type == 'Admin' || $currentUser->user_type == 'CompanyAdmin')
      {
        
        if($currentUser->user_type == 'Admin') {
          $users = User::cu_users();
        }
        else {
          $users = User::where('company_id', $currentUser->company_id)->get();
        }
        // dd($users);
        $roles = Role::all();
        $role_users = UserRole::all();
        $companies = Company::all();
      
        // print_r($users);
        // exit();
        $pageTitle = "user";
        return View('usersmanagement.show-users', compact('users', 'roles',  'pageTitle', 'companies', 'role_users'));
      } 

      $properties = Property::where('user_id', $currentUser->id)->get();
      $property_companies = DB::Select('SELECT
            properties.id, companies.name
        FROM
            users, properties, companies
        where users.id = properties.user_id and users.company_id = companies.id');
        
      $property_types = PropertyType::all();
      $property_features = PropertyFeature::all();
      $users = User::all();
      $companies = Company::all();
      
      $pageTitle = "";
      
      return View('propertymanagement.show-properties', compact('pageTitle', 'properties', 'property_types', 'property_features',
        'users', 'property_companies', 'companies'));

    }
    
}
