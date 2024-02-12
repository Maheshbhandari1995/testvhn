@extends('layout')
@section('content')
<style>
    tbody tr {
    word-break: break-word;
}
</style>
<main id="main" > 
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pagetitle justify-content-between d-flex">
                <h1>User List</h1>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('users.create') }}"><i class='bi bi-plus-lg'></i> Create User</a>
            </div>
        </div>
    </div>

    <div class="card pt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="user_table">
                    <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Name</th>
                            @if(session('data.userRole') != 'admin')
                            <th>Organization</th>
                            <th>Username</th>
                            @endif
                            <th>Email ID</th>
                            <th>Mobile No.</th>
                            <th style="width: 50px;">Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th style="width: 70px;">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        $(document).ready(function(){
            // Get the CSRF token value from the meta tag
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $('#user_table').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: "{{ route('users.list') }}",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken // Include the CSRF token in the headers
                    }
                },
                columns: [
                    { 
                        data: 'id',
                        render: function (data, type, row, meta) {
                            // Calculate the serial number using the row index
                            var srNo = meta.row + 1;

                            return srNo;
                        },
                        orderable: false,
                        searchable: false
                    },
                    { data: 'name', name: 'name' },
                    @if(session('data.userRole') != 'admin')
                    { data: 'client', name: 'client' },
                    { data: 'username', name: 'username' },
                    @endif
                    { data: 'email', name: 'email' },
                    { data: 'mobile', name: 'mobile' },
                    { 
                        data: 'role', 
                        name: 'role',
                        render: function (data, type, row) {
                            if (data == 'admin') {
                                return '<b class="text-danger">Admin</b>';
                                
                            }else if(data == 'mis'){
                                return '<b class="text-primary">Mis</b>';
                            }
                            else{
                                return '<b class="text-info">User</b>';
                            }
                        }
                    },
                    { 
                        data: 'status', 
                        name: 'status',
                        render: function (data, type, row) {
                            if (data == 1 || data == 0) {
                                return '<b class="text-success">Active</b>';
                            } else{
                                return '<b class="text-danger">Inactive</b>';
                            }
                        }
                    },
                    { 
                        data: 'created_at', 
                        name: 'created_at',
                        render: function (data) {
                            // Format the date using moment.js
                            return moment(data).format('MMM DD, YYYY');
                        }
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            var editUrl = "{{ url('users') }}/" + data + "/edit";
                            var resetUrl = "{{ url('password/reset_req') }}/" + data;
                            var editButton = "<a href='" + editUrl + "' class='' title='Update user' ><i class='bi bi-pencil-square'></i></a>";
                            var deleteForm = "<a class='text-danger status-select' title='Delete user'  style='cursor: pointer;' key-value = "+data+"  onclick='confirmDelete(event)'><i class='bi bi-trash3-fill'></i></a>";
                            var resetLink = "<a href='" + resetUrl + "' title='Reset password for the user' class='text-warning' style='cursor: pointer;' key-value = "+data+" ><i class='bi bi-key-fill'></i></a>";

                            return editButton + " " + resetLink + " " + deleteForm ;
                        },
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });

        function confirmDelete(event) {
                event.preventDefault();
                var deleteUrl = event.currentTarget.getAttribute('href');
                var keyValue = event.currentTarget.getAttribute('key-value');

                Swal.fire({
                    title: "Are you sure?",
                    text: "You are about to delete the record with ID: " + keyValue,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Delete",
                }).then((result) => {
                    if (result.isConfirmed) {
                       // var userId = $(this).attr('key-value');
                        $.ajax({
                            url: "{{ url('users') }}/" + keyValue + "/status",
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                req_type: 'delete', status: '3'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Reload the datatable or perform any necessary updates
                                    $('#user_table').DataTable().ajax.reload();
                                }
                            }
                        });
                    }
                });
            }
    </script>

@endsection