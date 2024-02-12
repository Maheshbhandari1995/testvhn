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

    <div class="card pt-4">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <label class="form-label">Organization :</label>
                            <select name="client_id" id="client_id" class="form-control">
                                <option value="">Select Organization </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <label class="form-label">Role :</label>
                            <select name="role" id="role" class="form-control">
                                <option value="">Select Role </option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                                
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <label class="form-label">Name:</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Name">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <label class="form-label">Email:</label>
                            <input type="text" name="email" id="email"  class="form-control" placeholder="Email">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <label class="form-label">Mobile:</label>
                            <input type="text" name="mobile" id="mobile"  class="form-control" placeholder="Mobile">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <label class="form-label">Username:</label>
                            <input type="text" name="username" id="username" class="form-control" placeholder="Username">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4"> 
                            <label class="form-label">Password:</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password" value="{{$password}}"><span id="toggle-password" class="eye-icon"></span>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <label class="form-label">Status:</label>
                            <select name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                            </select>
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
<script>

    $(document).ready(function() {
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
    });
</script>
@endsection