<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\Creditslog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Module;


class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //show active client or company on index page
        $company = Company::all();
      return view ('company.index')->with('company', $company);
    }

    //         $addcredits = $request->input('addcredits');
    //         $dataId = $request->input('dataId');
    //         //$sessionData = session('data');
    //         $allowcredits = DB::select("SELECT max_count FROM `clients` WHERE id='$dataId'  AND del_status='1'");
    //         $value = $allowcredits[0]->max_count;

    //         // echo var_dump($value);
    //         // return $value;
    //         $creditslog = new Creditslog();
    //         $creditslog->client_id = $dataId;
    //         $creditslog->credits = $value+(int)$addcredits;
    //         $ip = $request->ip();
    //         $creditslog->created_by = $ip;
    //         $creditslog->status = '1' ;
    //         $creditslog->save();

    public function getCompanyList(Request $request)
    {
        
        if ($request->ajax()) {
            $data = Company::whereIn('del_status', [1,0])->latest()->get();
            return DataTables::of($data)
                ->addColumn('action', function($row){
                    $editUrl = route('company.edit', $row->id);
                    $deleteUrl = route('company.delete',$row->id);
                    $btn = '<a href="'.$editUrl.'" class="btn btn-sm btn-info">Edit</a>';
                    $btn .= '<a href="'.$deleteUrl.'" class="btn btn-sm btn-info status-select" key-value = "'.$row->id.'">Delete</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        //creating client or company
        return view('company.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {      
        $sessionData = session('data');
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'envtype' => 'required',
            'max_count' => 'required|numeric',
            'module.*' => 'nullable',
            'primary_vendor.*' => 'required_with:module.*',
            'secondary_vendor.*' => 'nullable',
            'date' => 'nullable|date',
            'status' => 'required',
        ], [
            'name.required' => 'The company name is required.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'max_count.required' => 'The allowed credits field is required.',
            'max_count.numeric' => 'Please enter a numeric value for allowed credits.',
            'primary_vendor.*.required_with' => 'The primary vendor field is required when module is selected.',
            'date.date' => 'Please enter a valid date.',
            'status.required' => 'The status field is required.',
        ]);


        $company = Company::create([
            'name'      => $request->input('name'),
            'email'     => $request->input('email'),
            'max_count' => $request->input('max_count'),
            'envtype'   => $request->input('envtype'),
            'status'    => $request->input('status'),
            'del_status' => 1,            
            'created_at' => now(),  
            'created_by' => $sessionData['userID'],
        ]);
        if(!empty($company->id))
        {
            $modules = $request->input('module', []);
            $primaryVendors = $request->input('primary_vendor', []);
            $secondaryVendors = $request->input('secondary_vendor', []);

            foreach ($modules as $index => $moduleName) {
                $primaryVendor      = $primaryVendors[$index] ? $primaryVendors[$index] : '';
                $secondaryVendor    = $secondaryVendors[$index] ? $secondaryVendors[$index] : '';
                
                if(!empty($moduleName) && !empty($primaryVendor))
                {
                    $api_master = DB::table('api_master')
                                ->select('*')
                                ->whereIn('status', [0,1])
                                ->where('api_name', $moduleName)
                                ->where('vender', $primaryVendor)
                                ->get()
                                ->first();

                    $checkExist = DB::table('api_list')
                                ->select('id')
                                ->whereIn('status', [0,1])
                                ->where('apiname', $moduleName)
                                ->where('vendorname', $primaryVendor)
                                ->where('client_id', $company->id)
                                ->get()
                                ->first();

                    if(empty($checkExist->id) && !empty($api_master))
                    {
                        $module =  Module::create([
                            'client_id'     => $company->id,
                            'apiname'       => $moduleName,
                            'view_filename' => $api_master->view_filename,
                            'api_alias'     => $api_master->api_alias,
                            'company'       => $company->name,
                            'vendorname'    => $primaryVendor,
                            'sec_vendor'    => $secondaryVendor,
                            'apiurl'        => $api_master->api_url,
                            'status'        => $request->input('status'),
                            'del_status'    => 1,            
                            'created_at'    => now(),  
                            'created_by'    => $sessionData['userID'],
                        ]);
                    }else{
                        //
                    }
                }
            }

            $creditslog = new Creditslog();
            $creditslog->client_id = $company->id; // Assign the company ID to the client_id of Creditslog
            $creditslog->credits = $request->input('max_count');
            $creditslog->created_at = now();
            $creditslog->created_by = $sessionData['userID'];
            $creditslog->status = '1' ;
            $creditslog->save();
            return redirect()->route('company.index')->with('success', 'Company created successfully.');
        }
        else{
            return redirect()->route('company.index')->with('error', 'Company creation failed.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Company::find($id);
        return view('company/show')->with('company', $company);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = DB::table('clients')
            ->select('*')
            ->whereIn('status', [0,1])
            ->where('id', $id)
            ->get()
            ->first();

        $modules = DB::table('api_list')
            ->select('id','apiname', 'vendorname', 'sec_vendor')
            ->whereIn('status', [0,1])
            ->where('client_id', $id)
            ->get();

        $moduleMaster = DB::table('api_master')
            ->select('api_name')
            ->distinct()
            ->whereIn('status', [0,1])
            ->get();

        $vendors = [];
        if(!empty($modules))
        {
            foreach($modules as $k => $val)
            {
                $vendors[$val->apiname] = DB::table('api_master')
                    ->select('vender')
                    ->whereIn('status', [0,1])
                    ->where('api_name', $val->apiname)
                    ->get();
            }
        }
        
        // echo "<pre>"; print_r($clients); 
         //echo "<pre>"; print_r($vendors);       die; 
        $company = Company::find($id);
        // return view('company.edit')->with('company', $company);
        return view('company.edit', compact('modules', 'company', 'moduleMaster', 'vendors'));

 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $sessionData = session('data');
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'envtype' => 'required',
            'module.*' => 'nullable',
            'primary_vendor.*' => 'required_with:module.*',
            'secondary_vendor.*' => 'nullable',
            'date' => 'nullable|date',
            'status' => 'required',
        ], [
            'name.required' => 'The company name is required.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'max_count.required' => 'The allowed credits field is required.',
            'max_count.numeric' => 'Please enter a numeric value for allowed credits.',
            'primary_vendor.*.required_with' => 'The primary vendor field is required when module is selected.',
            'date.date' => 'Please enter a valid date.',
            'status.required' => 'The status field is required.',
        ]);

        $sessionData = session('data');
        //return $sessionData;
        $company = Company::findOrFail($id);
       // echo "company : <pre>"; print_r($company);
        $company->name = $request->input('name');
        $company->email = $request->input('email');
        $credit_value = intval($request->input('add_more_credit'));
        $company->max_count = $company->max_count + $credit_value;
        $company->envtype = $request->input('envtype');
        if ($request->input('envtype') === 'preproduction') {
            $validatedDate = $request->input('date');
            if (!empty($validatedDate)) {
                $company->expirydate = $validatedDate;
            } else {
                $company->expirydate = null;
            }
        } 
        else if($request->input('envtype') === 'production'){
            $company->expirydate =null;
        }
        $company->status = $request->input('status');
        $company->updated_by = $sessionData['userID'];
        $company->updated_at = now();
       // echo $credit_value." : <pre>"; print_r($company);die;
        $company->save();

        if(!empty($id))
        {
            $modules = $request->input('module', []);
            $primaryVendors = $request->input('primary_vendor', []);
            $secondaryVendors = $request->input('secondary_vendor', []);
            $isExist = $request->input('isExist', []);

            foreach ($modules as $index => $moduleName) {
                $primaryVendor      = $primaryVendors[$index] ? $primaryVendors[$index] : '';
                $secondaryVendor    = $secondaryVendors[$index] ? $secondaryVendors[$index] : '';
                
                if(!empty($moduleName) && !empty($primaryVendor))
                {
                    $api_master = DB::table('api_master')
                                ->select('*')
                                ->whereIn('status', [0,1])
                                ->where('api_name', $moduleName)
                                ->where('vender', $primaryVendor)
                                ->get()
                                ->first();

                    if(!empty($api_master))
                    {
                        $query = Module::updateOrCreate(
                            ['id' => $isExist[$index]],
                            [
                                'client_id'     => $company->id,
                                'apiname'       => $moduleName,
                                'view_filename' => $api_master->view_filename,
                                'api_alias'     => $api_master->api_alias,
                                'company'       => $company->name,
                                'vendorname'    => $primaryVendor,
                                'sec_vendor'    => $secondaryVendor,
                                'apiurl'        => $api_master->api_url,
                                'status'        => 1,
                                'del_status'    => 1,
                                'created_at'    => date('Y-m-d'),
                                'updated_at'    => date('Y-m-d'),
                                'created_by'    => $sessionData['userID'],
                                'updated_by'    => $sessionData['userID']
                            ]
                        );
                    }
                }
            }

            if($credit_value != 0){
                $creditslog             = new Creditslog();
                $creditslog->client_id  = $id;
                $creditslog->credits    = $credit_value;
                $creditslog->created_by = $sessionData['userID'];
                $creditslog->created_at = now();
                $creditslog->status     = '1' ;
                $creditslog->save();
            }
            return redirect()->route('company.index')->with('success', 'Company updated successfully.');
        }
        else{
            return redirect()->route('company.index')->with('error', 'Company updation failed.');
        }
    }

    public function delete($id){
        $company = Company::find($id);
            $company->delete();
            return Redirect()->back()->with('success','Company deleted successfully.');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Company::destroy($id);
        return redirect('company')->with('success', 'Company deleted successfully.');
    }

    public function getModules()
    {
        $module = DB::table('api_master')
        ->select('api_name')
        ->distinct()
        ->whereIn('status', [0,1])
        ->get(); //->first()
        //echo "<pre>"; print_r($module->api_name);die;
        return response()->json(['module' => $module]);
    }

    public function getPrimaryVendors(Request $request)
    {
        $module = $request->input('module');
        $vendors = DB::table('api_master')
        ->select('vender')
        ->whereIn('status', [0,1])
        ->where('api_name', $module)
        ->get();

        return response()->json(['vendors' => $vendors]);
    }

    public function getSecondaryVendors(Request $request)
    {
        $module = $request->input('module');
        $vendor = $request->input('vendor');
        $vendorArr = explode(',', $vendor);
        $vendors = DB::table('api_master')
        ->select('vender')
        ->whereIn('status', [0,1])
        ->where('api_name', $module)
        ->whereNotIn('vender',  $vendorArr)
        ->get();

        return response()->json(['vendors' => $vendors]);
    }

    public function removeModule(Request $request)
    {
        $id = $request->input('id');
        DB::table('api_list')->where('id', $id)->delete();
        return response()->json(['msg' => "Deleted Successfully"]);
    }
}
