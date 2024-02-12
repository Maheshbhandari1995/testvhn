
@extends('layout')

@section('content')
<div id="main" class="main">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pagetitle">
                <h1>RC Bulk Report (Logic)</h1>
            </div>
        </div>
    </div>

  
    <div class="table-responsive">
        <table class="table table-striped" id="user_table">
            <thead>
                <tr>
                    <th>Sr.</th>
                    <th>Module</th>
                    <th>Organization</th>
                    <th>Data Size</th>
                    <th>Filename</th>
                    <th>Status</th>
                    <th>Created at</th>
                    <th>Download</th>
                </tr>
            </thead>
        </table>
    </div>
    
</div>

<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script>
    $(document).ready(function(){
    // Get the CSRF token value from the meta tag
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Add the "buttons" option for downloading
        $('#user_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('rcbulkreport_logic.list') }}",
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
                { data: 'api_name', name: 'api_name' },
                { data: 'client_name', name: 'client_name' },
                { data: 'count', name: 'count' },
                { data: 'filename', name: 'filename' }, 
                { 
                    data: 'is_processed', 
                    name: 'is_processed',
                    render: function (data, type, row) {
                        if (data == '1') {
                            return '<b class="text-danger">Pending</b>';
                        } else{
                            return '<b class="text-success">Completed</b>';
                        }
                    }
                },
                { data: 'created_at', name: 'created_at' },
                { 
                    data: 'downloadurl',
                    name: 'downloadurl',
                    render: function (data, type, row, meta) {
                        if (data == '(NULL)' || data == null) {
                            return '<b class="text-danger">Pending</b>';
                        } else{
                            return '<b class="text-success"><a href="' + data + '" class=""><i class="bi bi-download"></i></a></b>';
                        }
                    },
                    orderable: false,
                    searchable: false
                },
            ]
        });
});
    </script>
<!-- /#page-wrapper -->
    <!-- Your home page content goes here -->
    @endsection
