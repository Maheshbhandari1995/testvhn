@extends('layout')
<style>
.eye-icon {
    position: absolute;
    right: 25px;
    top: 52%;
    transform: translateY(-50%);
    cursor: pointer;
    width: 25px;
    height: 25px;
    background-image: url("{{asset('assets/img/eye-password.png')}}"); /* Replace with your eye icon image */
    background-size: cover;
}
.input-group-append{
    position: absolute;
    right: 0px;
    z-index: 9;
    top: 0px;
}
.password-set{
    position: relative;
}

</style>
@section('content')
<main id="main">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pagetitle justify-content-between d-flex">
                <h1>Create User</h1>
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
    {{-- <pre>
        <?php// print_r($organizations[19]); print_r(old('client_id')); ?>
    </pre> --}}
    <div class="card pt-5">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST" id="userForm">
                @csrf

                <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <select name="client_id" id="client_id" class="form-control">
                                <option value="">Select Organization</option>
                                <!-- Add logic to pre-select the option -->
                                @foreach ($organizations as $key => $roleName)
                                <option value="{{ $key }}" {{ (old('client_id') == $key) ? 'selected' : '' }}>{{ $roleName }}</option>
                                @endforeach
                                
                            </select>
                            <label class="form-element-label">Organization</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">

                            <select name="role" id="role" class="form-control">
                                <option value="">Select Role</option>
                                @if(session('data.userRole') == 'super_admin')
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                                    <option value="mis" {{ old('role') == 'mis' ? 'selected' : '' }}>Mis</option>
                                @else
                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                                @endif
                            </select>
                            <label class="form-element-label">Role</label>
                        </div>
                       
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <input type="text" name="name" id="name" class="form-control" placeholder="Name" value="{{ old('name') }}">
                            <label class="form-element-label">Name</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <input type="text" name="email" id="email"  class="form-control" placeholder="Email" value="{{ old('email') }}">
                            <label class="form-element-label">Email</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <input type="number" name="mobile" id="mobile"  class="form-control" placeholder="Mobile" value="{{ old('mobile') }}">
                            <label class="form-element-label">Mobile</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <input type="text" name="username" id="username" class="form-control" placeholder="Username" value="{{ old('username') }}">
                            <label class="form-element-label">Username</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4 password-set"> 
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                            <label class="form-element-label">Password</label>
                            <p class="error-message">Password incorrect</p>

                            <div class="input-group-append">
                                <button class="btn" type="button" id="password-toggle">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-lg-6">
                        <div class="mb-4"> 
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                            <label class="form-element-label">Password</label>
                            <p class="error-message">Password incorrect</p>
                            <div class="password-toggle">
                                <input type="checkbox" id="password-toggle">
                                <label for="password-toggle" class="password-toggle-label">Show Password</label>
                            </div>
                        </div>
                    </div> --}}

       
                    {{-- <div class="col-lg-6">
                        <div class="mb-4"> 
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control">
                                <label class="form-element-label">Password</label>
                                <div class="input-group-append">
                                    <button class="btn" type="button" id="password-toggle">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="error-message">Password incorrect</p>
                        </div>
                    </div> --}}
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <select name="status" class="form-control">
                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <label class="form-element-label">Status</label>
                        </div>
                    </div>
                    <div class="text-center">
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


        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        $('#toggle-password').click(function() {
            var passwordInput = $('#password');
            var passwordFieldType = passwordInput.attr('type');
            if (passwordFieldType === 'password') {
                passwordInput.attr('type', 'text');
                //  $('#toggle-password').css('background-image', url("{{asset('assets/img/eye-o.png')}}"));
            } else {
                // $('#toggle-password').css('background-image', url("{{asset('assets/img/eye-password.png')}}"));
                passwordInput.attr('type', 'password');
            }
        });

        $("#role").change(function(){
            var role = $(this).val();
            var clientID = $("#client_id").val();
            if(role == 'admin')
            {
                getClientDetails(clientID);
            }
            else if(role == 'mis'){
                clearInput();
            }
            else{
                clearInput();
            }
        })

        $("#client_id").change(function(){
            var clientID = $(this).val();
            getClientDetails(clientID);
           
        })

        $.ajax({
            url: "{{ route('users.client_list') }}",
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var options = '<option value="">Select Organization </option>';
                $.each(data, function(key, value) {
                    options += '<option value="' + key + '">' + value + '</option>';
                });
                $('#client_id').html(options);
            }
        });

        function clearInput()
        {
            $("#name").val('');
            $("#email").val('');
            $("#mobile").val('');
            $("#username").val('');
        }

        function getClientDetails(clientID)
        {
            $.ajax({
                url: "{{ route('users.get_client_details')}}",
                type: 'POST',
                data: {clientID: clientID},
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response.data.name);
                    if(response.status == 'success')
                    {
                        $("#name").val(response.data.name);
                        $("#email").val(response.data.email);
                        $("#username").val(response.data.email);
                        $('#role').val('admin');
                    }
                }
            });
        }


        const passwordInput = document.getElementById("password");
        const passwordToggle = document.getElementById("password-toggle");

        passwordToggle.addEventListener("click", function () {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordToggle.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                passwordInput.type = "password";
                passwordToggle.innerHTML = '<i class="fa fa-eye"></i>';
            }
        });
    });
</script>
@endsection