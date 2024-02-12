@extends('layout')

@section('content')
<main id="main">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pagetitle justify-content-between d-flex">
                <h1>Edit User</h1>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i></a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Error!</strong> Please check the form fields.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <?php 
// echo "users<pre>"; print_r($users->role);
//     echo "role<pre>"; print_r($role);die;
?>
    <div class="card pt-5">
        <div class="card-body">
            <form action="{{ route('users.update', $users->id) }}" method="POST" id="userForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <select name="client_id" id="client_id" class="form-control">
                                <option value="">Select Organization </option>
                                @foreach ($clients as $clientId => $clientName)
                                <option value="{{ $clientId }}" {{ $users->client_id == $clientId ? 'selected' : '' }}>{{ $clientName }}</option>
                                @endforeach
                            </select>
                            <label class="form-element-label">Organization</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <select name="role" id="role" class="form-control">
                                <option value="">Select Role </option>
                               <?php //echo $users->role; ?>
                                @foreach ($role as $roleKey => $roleName)
                                <option value="{{ $roleKey }}" {{ strtolower($users->role) == strtolower($roleKey) ? 'selected' : '' }}>{{ $roleName }}</option>
                                @endforeach
                            </select>
                            <label class="form-element-label">Role</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <input type="text" name="name" value="{{ $users->name }}" class="form-control" placeholder="Name">
                            <label class="form-element-label">Name</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <input type="text" name="email" value="{{ $users->email }}" class="form-control" placeholder="Email">
                            <label class="form-element-label">Email</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <input type="text" name="mobile" value="{{ $users->mobile }}" class="form-control" placeholder="Mobile">
                            <label class="form-element-label">Mobile</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <input type="text" name="username" value="{{ $users->username }}" class="form-control" placeholder="Username">
                            <label class="form-element-label">Username</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <select name="status" class="form-control">
                                <option value="1" {{ ($users->status == '1' || strtolower($users->status) == 'active') ? 'selected' : '' }}>Active</option>
                                <option value="2" {{ ($users->status == '2'  || strtolower($users->status) == 'inactive')? 'selected' : '' }}>Inactive</option>
                            </select>
                            <label class="form-element-label">Status</label>
                        </div>
                    </div>
                    <div class="text-center mb-4">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script>
    $(document).ready(function() {
        $('#userForm').validate({
            rules: {
                client_id: 'required',
                name: 'required',
                role:'required',
                email: {
                    required: true,
                    email: true
                },
                mobile: {
                    required: true,
                    digits: true,
                    minlength: 10,
                    maxlength: 10
                },
                username: 'required',
                password: 'required',
                status: 'required'
            },
            messages: {
                // Custom error messages for each field (optional)
            },
            submitHandler: function(form) {
                // Form submission code (optional)
                form.submit();
            }
        });
    });
</script>
@endsection