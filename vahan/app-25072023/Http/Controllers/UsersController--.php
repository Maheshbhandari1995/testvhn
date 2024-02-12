<?php
namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function index()
    {
        $users = Users::whereIn('status', [0,1,2])->get(); //compact('users')
        return view('users.index', $users);
    }

    public function getUserList(Request $request)
    {
        if ($request->ajax()) {
           // $data = Users::whereIn('status', [0,1,2])->latest()->get();
            $data = DB::table('users')
            ->join('clients', 'users.client_id', '=', 'clients.id')
            ->select('users.*', 'clients.name as client')
            ->whereIn('users.status', [0,1,2])
            ->whereIn('clients.status', [0,1])
            ->latest()
            ->get();
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
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'mobile' => 'required',
            'username' => 'required|unique:users',
            'password' => 'required',
            'status' => 'required',
        ]);
        //echo "<pre>"; print_r($request->all());die;
        Users::create($request->all());

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(Users $users)
    {
        return view('users.show', compact('users'));
    }

    public function edit($id)
    {
        // echo  $id;
        $users = Users::findOrFail($id);
        //echo "<pre>"; print_r($users->id);
        return view('users.edit', $users);
    }

    public function update(Request $request, $id)
    {
        $users = Users::findOrFail($id);
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required',
            'username' => 'required',
            'status' => 'required',
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
        $user->status = request('status');
        $user->save();
        return response()->json(['success' => true]);
    }
}
