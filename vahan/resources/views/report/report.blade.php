

@extends('layout')

@section('content')
<style>
    /* input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        } */
        
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    
    th, td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    th {
        background-color: #f2f2f2;
    }
    .re_details{
        display:none;
    }

    .loader {
      border: 16px solid #f3f3f3; /* Light grey */
      border-top: 16px solid #3498db; /* Blue */
      border-radius: 50%;
      width: 120px;
      height: 120px;
      animation: spin 2s linear infinite;
      margin: 50px auto;
    }
    .hide-cell {
            display: none;
        }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
	input[type=text]{
		text-transform:uppercase;
	}
</style>
<div id="main" class="main">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pagetitle">
                <h1>Login Activity Reports</h1>
            </div>
        </div>
    </div>

    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script> 
    {{-- @if(session('data.userRole') == 'user')

    @endif
    @if(session('data.userRole') == 'super_admin') --}}
    <!-- /.row -->
    <div class="row">
        <div class="col-md-3">
            <input type="text" id="date_from" class="form-control" placeholder="From Date" required>
        </div>
        <div class="col-md-3">
            <input type="text" id="date_to" class="form-control" placeholder="To Date" required>
        </div>
        @if(session('data.userRole') == 'admin')
            <div class="col-md-3">
                <select id="org_admin" class="form-control select2-multiple" style="width: 100%;">
                    <option value="All" selected>Select All</option>
                </select>
            </div>
        @else
            <div class="col-md-3">
                <select id="org" class="form-control select2-multiple" style="width: 100%;">
                    <!-- Add options if needed -->
                    <option value="All" selected>Select All</option>
                </select>
            </div>
        @endif

        <div class="col-md-3">
            <button id="filter_button" class="btn btn-primary">Filter</button>
            <button id="csv_export_button" class="btn btn-primary float-right">Export CSV</button>
        </div>
    </div>

     <!-- //Filters -->
    <!-- <div class="loader" style="display: none;"></div> -->
    <div id="loader" class="loader-wrapper">
        <div class="loader-container">
            <div class="loader-box">
                <div class="ring"></div>
                <div class="ring"></div>
                <div class="ring"></div>
                <div class="ring"></div>
                <div class="loading-logo">
                    <img src="{{asset('assets/img/edas-logo-light.png')}}" alt="Edas Logo">
                </div>
            </div>
        </div>
    </div>

    <hr>
    <div class="table-responsive">
        <table class="table table-striped" id="user_table">
            <thead>
                <tr>
                    <th>Sr.</th>
                    @if(session('data.userRole') != 'admin')
                    <th>Organization</th>
                    @endif
                    <th>Name</th>
                    <th>Role</th>
                    <th>Ip Address</th>
                    <th>Login status</th>
                    <th>Login time</th>
                    <th>Logout time</th>
                </tr>
            </thead>
        </table>
    </div>

    {{-- @endif --}}
</div>

<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function(){

            $('.select2-multiple').select2({
            placeholder: "Select",
            allowClear: true
        });

        var today = new Date();
        // today.setDate(today.getDate() - 1);
        var date = new Date();
		var currentMonth = date.getMonth();
		var currentDate = date.getDate();
		var currentYear = date.getFullYear();

        $('#date_from').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            minDate: new Date(currentYear, currentMonth-3, currentDate),
            maxDate: today,
            changeMonth: true,
            changeYear: false,
        });

        $('#date_to').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            minDate: new Date(currentYear, currentMonth-3, currentDate),
            maxDate: today,
            changeMonth: true,
            changeYear: false,
        });  


            // Get the CSRF token value from the meta tag
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    $('#org').on('change', function() {
        var selectedValue = $(this).val();
        
        if (selectedValue != 'All') {   
        $("#org option:selected").removeAttr("selected");
        }
        else if(selectedValue == 'All') {
        $('#org option:selected').removeAttr('selected');
        }
    });

    $('#org').select2({
        ajax: {
            url: "{{ route('organization.names') }}",
            type: "POST", 
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') 
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        },
    minimumInputLength: 0,
    multiple: true 
    });

    $.ajax({
    url: "{{ route('organization.names') }}",
    type: "POST", 
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') 
    },
    success: function(data) {
        $('#org').select2({
            data: data
        });
        }
    });

    $('#org_admin').on('change', function() {
        var selectedValue = $(this).val();
        
        if (selectedValue != 'All') {   
        $("#org_admin option:selected").removeAttr("selected");
        }
        else if(selectedValue == 'All') {
        $('#org_admin option:selected').removeAttr('selected');
        }
    });

    $('#org_admin').select2({
        ajax: {
            url: "{{ route('user.names') }}",
            type: "POST", 
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') 
            },
            success: function(data) {
            $('#org_admin').select2({
                data: data
            });
            },
                cache: true
            },
    minimumInputLength: 0,
    multiple: true 
    });

    $.ajax({
    url: "{{ route('user.names') }}",
    type: "POST", 
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') 
    },
    success: function(data) {
        $('#org_admin').select2({
            data: data
        });
        }
    });


    $('#user_table').DataTable({
        processing: false,
        serverSide: false,
        ajax: {
            url: "{{ route('report.list') }}",
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
            @if(session('data.userRole') == 'super_admin')
            { data: 'Organization', name: 'Organization' },
            @endif
            { data: 'Name', name: 'Name' },
            { data: 'role', name: 'role' },
            { data: 'ip_address', name: 'ip_address' },
            { 
                    data: 'login_status',
                    name: 'login_status',
                    render: function(data, type, row) {
                        if (data == 1) {
                            return "Login";
                        } else if (data == 2) {
                            return "Logout";
                        } else {
                            return "";
                        }
                    }
            },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' }
        ],
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print' // Add the buttons you want to enable
        ],
        createdRow: function(row, data, dataIndex) {
        var loginStatus = data.login_status;
        if (loginStatus == 1) {
            $(row).find('td:nth-child(7)').hide();
        }
    }
    });


    $("#filter_button").click(function () {
        loader.style.display = 'block';  
    var date_from = $("#date_from").val();
    var date_to = $("#date_to").val();
    var org = "{{ session('data.userRole') == 'admin' ? 'org_admin' : 'org' }}";
    var selectedOrg = $("#" + org).val();

    if (date_from == '') {
        alert('Please select from date');
        loader.style.display = 'none';
        return false;
    } else if (date_to == '') {
        alert('Please select to date');
        loader.style.display = 'none';
        return false;
    } else if (selectedOrg == '') {
        alert('Please select organization name');
        loader.style.display = 'none';
        return false;
    } else {
        $.ajax({
            url: "{{ route('report.list') }}",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken
            },
            data: {
                date_from: date_from,
                date_to: date_to,
                org: selectedOrg
            },
            success: function (response) {
                var loader = document.getElementById('loader');
               
                var dataTable = $('#user_table').DataTable();
               
                var data = response.data;
                if (data.length === 0) {
                    dataTable.clear().draw(); 
                    loader.style.display = 'none';
                    // dataTable.clear();
                    // $('#user_table').find('tbody').html('<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">No data available in table</td></tr>');
                    
                } else { 
                    // console.log(response);
                    // console.log(data);
                    loader.style.display = 'none';
                    dataTable.clear().draw(); 
                    dataTable.clear().destroy(); 
                    $('#user_table').empty(); 
                    var columnNames = Object.keys(data[0]); 
                    
                    var tableHeaders = '<thead><tr>' +
                        '<th>Sr.</th>' +
                        @if(session('data.userRole') == 'super_admin')
                        '<th>Organization</th>' +
                        @endif
                        '<th>Name</th>' +
                        '<th>Role</th>' +
                        '<th>Ip Address</th>' +
                        '<th>Login status</th>' +
                        '<th>Login time</th>' +
                        '<th>Logout time</th>' +
                        '</tr></thead>';

                    $('#user_table').append(tableHeaders); 
                    var tbody = '<tbody>';
                    data.forEach(function (rowData, index) {
                        var row = '<tr>';
                        row += '<td>' + (index + 1) + '</td>';
                        for (var i = 0; i < columnNames.length; i++) {
                            var columnName = columnNames[i];

                            if (columnName === 'Organization' && @json(session('data.userRole')) === 'admin') {
                                continue;
                            } else if (columnName == 'Login_status') {
                                var cellData = rowData[columnName];
                                if (cellData == 2) {
                                    row += '<td>Logout</td>';
                                } else {
                                    row += '<td>Login</td>';
                                }
                            } else if (columnName == 'Logout_time' && rowData['Login_status'] == 1) {
                                // Apply the class to hide the cell contents
                                row += '<td class="hide-cell">' + rowData[columnName] + '</td>';
                            } else {
                                row += '<td>' + rowData[columnName] + '</td>';
                            }
                        }
                        
                        row += '</tr>';
                        tbody += row;
                    });

                    tbody += '</tbody>';
                    $('#user_table').append(tbody);
                    dataTable = $('#user_table').DataTable(); // Initialize DataTable after modifying the table content
                }
            },
            error: function (xhr, status, error) {
                loader.style.display = 'none';
                console.log(xhr);
            }
        });
    }
});

    $("#csv_export_button").click(function () {
        var loader = document.getElementById('loader');
        loader.style.display = 'block';
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var date_from = $("#date_from").val();
        var date_to = $("#date_to").val();
        var org = "{{ session('data.userRole') == 'admin' ? 'org_admin' : 'org' }}";
    var selectedOrg = $("#" + org).val();

         if (date_from == '') {
            alert('Please select from date');
            loader.style.display = 'none';
            return false;
        } else if (date_to == '') {
            alert('Please select to date');
            loader.style.display = 'none';
            return false;
        } else if (selectedOrg == '') {
            alert('Please select organization name');
            loader.style.display = 'none';
            return false;
        } else {
            $.ajax({
                url: "{{ route('loginActivity.csv') }}",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken
                },
                data: {
                date_from: date_from,
                date_to: date_to,
                org: selectedOrg
                },
                success: function (response) {
                    loader.style.display = 'none';
                    console.log(response);
                    if (response.download) {
                    var link = document.createElement('a');
                    link.href = response.file_url;
                    link.download = response.file_name;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    }
                },
                error: function (xhr, status, error) {
                    loader.style.display = 'none';
                    console.log(xhr);
                }
            });
        }
    });
});
    </script>

    @endsection
