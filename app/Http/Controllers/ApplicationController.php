<?php

namespace App\Http\Controllers;
use App\Models\Application;
use App\Models\User;
use App\Models\Property;
use Stripe\Charge;
use Illuminate\Http\Request;
use Auth;

class ApplicationController extends Controller
{
    //
    public function index() {
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'Admin')
        {
            $applications =  Application::all();    
        }
        else if ($currentUser->user_type == 'CompanyAdmin') 
        {
          $userIds = User::where('company_id', $currentUser->company_id)->get()->pluck('id');
          $property_ids = Property::whereIn('user_id', $userIds)->get()->pluck('id');
          $applications = Application::whereIn('property_id', $property_ids)->get();
        } 
        else 
        {
          $property_ids = Property::where('user_id', $currentUser->id)->get()->pluck('id');
          $applications = Application::whereIn('property_id', $property_ids)->get();
        }
       
        return View('application.index', compact('applications'));
    }

    public function detail($id)
    {
        $application = Application::findOrFail($id);
        
        $data = [

            'application' => $application,
            
        ];

        return view('application.detail-application')->with($data);
    }

    public function update(Request $request, $id) {
        $application = Application::findOrFail($id);

        $application->reject_reason = $request->input('reason');
        $application->status = 2;
        $application->save();
        return redirect('admin/applications');
    }
}
