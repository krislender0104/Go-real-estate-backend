<?php

namespace App\Http\Controllers;
use App\Models\Contract;
use App\Models\Application;
use App\Models\Property;
use App\Models\User;
use App\Models\HistoryPayment;
use Illuminate\Http\Request;
use Auth;

class ContractController extends Controller
{
    //
    public function create(Request $request) {
        $data = new Contract;

        $data->user_id = $request->input('user_id');
        $data->property_id = $request->input('property_id');
        $data->type = $request->input('type');
        $data->period = $request->input('period');
        $data->detail = $request->input('detail');
        $data->application_id = $request->input('application_id');
        $data->down_payment = $request->input('down_payment');
        $data->monthly_payment = $request->input('monthly_payment');

        $data->save();

        $data_app = Application::findOrFail($request->input('application_id'));
        $data_app->status = 1;
        $data_app->save();
        return redirect('admin/applications');
    }

    public function index() {

        $currentUser = Auth::user();

        if ($currentUser->user_type == 'Admin') 
        {
            $contracts =  Contract::all();    
        } 
        else if($currentUser->user_type == 'CompanyAdmin')
        {
          $userIds = User::where('company_id', $currentUser->company_id)->get()->pluck('id');
          $property_ids = Property::whereIn('user_id', $userIds)->get()->pluck('id');
          $application_ids = Application::whereIn('property_id', $property_ids)->get()->pluck('id');
          $contracts = Contract::whereIn('application_id', $application_ids)->get();
        }
        else
        {
          $property_ids = Property::where('user_id', $currentUser->id)->get()->pluck('id');
          $application_ids = Application::whereIn('property_id', $property_ids)->get()->pluck('id');
          $contracts = Contract::whereIn('application_id', $application_ids)->get();
        }
        return View('contract.index', compact('contracts'));
    }

    public function detail($id)
    {
        $contract = Contract::findOrFail($id);
        $pay_histories = HistoryPayment::where('contract_id', $contract->id)->get();
        $data1 = [

            'contract'        => $contract,
        ];
        $data2 = [

            'pay_histories'     =>$pay_histories,
        ];
        return view('contract.detail-contract')->with($data1)->with($data2);
    }

}
