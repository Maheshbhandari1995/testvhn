<?php
namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Rules\PasswordPolicy;
use Illuminate\Validation\Rule;
use App;
class UsersController extends Controller
{

    // public function __construct()
    // {
    //     Route::get('/users', [UsersController::class, 'index'])->middleware('auth.custom');
    // }
    public function index()
    {
        $users = Users::whereIn('status', [0,1,2])->get(); //compact('users')
        return view('users.index', $users);
    }

    public function getUserList(Request $request)
    {
        $sessionData = session('data');
       // echo "<pre>"; print_r($sessionData);die;
        if ($request->ajax()) {
           // $data = Users::whereIn('status', [0,1,2])->latest()->get();
           if(isset($sessionData) && $sessionData['userRole'] == 'admin')
           {
            
                $Client_id = $sessionData['Client_id'];  

                $data = DB::table('users')
                ->leftJoin('clients', 'users.client_id', '=', 'clients.id')
                ->select('users.*', 'clients.name as client')
                ->whereIn('users.status', [0,1,2])
                ->whereIn('clients.status', [0,1])
                ->where('users.role','!=','mis')
                ->where('clients.del_status', 1)
                ->where('users.client_id', $Client_id)
                ->latest()
                ->get();
           }else{
                $data = DB::table('users')
                ->leftJoin('clients', 'users.client_id', '=', 'clients.id')
                ->select('users.*', 'clients.name as client')
                ->whereIn('users.status', [0,1,2])
                ->whereIn('clients.status', [0,1])
                ->where('clients.del_status', 1)
                ->latest()
                ->get();
           }
            
            // $sql = $data->toSql();
            // echo $sql;die;
            // echo "<pre>"; print_r($data);die;
            return DataTables::of($data)
                ->addColumn('action', function($row){
                    $editUrl = route('users.edit', $row->id);
                    $btn = '<a href="'.$editUrl.'" class=""><i class="bi bi-pencil-square"></i></a>';
                    $btn = '<a class="text-danger" key-value = "'.$row->id.'"><i class="bi bi-trash3-fill"></i></a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return abort(404);
    }

    public function create()
    {   
        $sessionData = session('data');
        //print_r($sessionData);
        if($sessionData['userRole'] == 'admin'){
             //print_r($sessionData);
            $clientID =  $sessionData['Client_id'];  
            $organizations = Company::where('id', $clientID)->pluck('name', 'id');
            //print_r($organizations);
        }else{
            $organizations = Company::pluck('name', 'id');
        }
        return view('users.create', array('organizations' =>$organizations));
    }

    public function store(Request $request)
    {
        $organizations = Company::pluck('name', 'id')->toArray();
       
        $request->validate([
            'client_id' => 'required',
            'name' => 'required',
            'role' => 'required',
            'email' => 'required|email|unique:users',
            'mobile' => ['required', 'regex:/^[0-9]{10}$/'],
            'username' => 'required|unique:users',
            'password' => ['required', 'string', new PasswordPolicy],
            'status' => 'required',
        ]);

        $postData = $request->all();
        //dd($postData);
        $postData['password'] = Hash::make($postData['password']);
        $request->merge(['password' => $postData['password']]);
        try {
            $user = Users::create($request->all());
            //dd($user);
            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            //dd($e);
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create user. Please try again.'])->with('organizations', $organizations);
        }
    }

    public function show(Users $users)
    {
        return view('users.show', compact('users'));
    }

    public function edit($id)
    {
        $sessionData = session('data');
        //print_r($sessionData);
        if($sessionData['userRole'] == 'admin' || $sessionData['userRole'] == 'mis'){
             //print_r($sessionData);
            $clientID =  $sessionData['Client_id'];  
             $clients = Company::where('id', $clientID)->pluck('name', 'id');
            //print_r( $clients);
        }else{
             $clients = Company::pluck('name', 'id');
        }
        if(session('data.userRole') === 'super_admin'){
            $role = array('admin'=>'Admin', 'user'=>'User','mis' => 'Mis');
        }else{
            $role = array('user'=>'User');
        } 
        $users = Users::findOrFail($id);
        //return $users;
        //echo "<pre>"; print_r($users->status);die;
        // compact('users', 'clients')
        return view('users.edit', compact('users', 'clients', 'role'));
    }

    public function update(Request $request, $id)
    {
        $users = Users::findOrFail($id);

        // echo "<pre>"; print_r($request->all());die;
        $request->validate([
            'client_id' => 'required',
            'name' => 'required',
            'role' => 'required',
            'mobile' => 'required',
            'status' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($id),
            ],
            'username' => [
                'required',
                'min:3',
                'max:255',
                Rule::unique('users')->ignore($id),
            ]
        ]);

        $users->update($request->all());

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, Users $users)
    {
       //echo "<pre>"; print_r($request);die;
       $status = $users->delete();
      // print_r($status);
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function updateStatus($id)
    {
        $user = Users::findOrFail($id);
        $user->delete();
        // $user->status = request('status');
        // $user->save();
        return response()->json(['success' => true]);
    }

    public function getClientList()
    {
        //echo "Heysfgsdfgadfgadfgafga";die;
        //$clients = Company::pluck('name', 'id'); // Assuming 'name' is the field for the client name and 'id' is the field for the client ID
        
        $sessionData = session('data');
        //print_r($sessionData);
        if($sessionData['userRole'] == 'admin'){
             //print_r($sessionData);
            $clientID =  $sessionData['Client_id'];  
             $clients = Company::where('id', $clientID)->pluck('name', 'id');
            //print_r( $clients);
        }else{
             $clients = Company::pluck('name', 'id');
        }


        return response()->json($clients);
    }

    public function getClientDetails(Request $request)
    {
        $clientID = $request->input('clientID');

        $clientsData = DB::table('clients')
            ->select('name', 'email')
            ->whereIn('status', [0,1])
            ->where('id', $clientID)
            ->where('del_status', 1)
            ->get()
            ->first();
        //echo $clientID."<pre>"; print_r($clientsData);
        return response()->json(['status' => 'success', 'data' => $clientsData]);
    }
}
