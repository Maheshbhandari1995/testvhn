
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
                <h1>Module Activity Report</h1>
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
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.html5.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css"/>
<link href='https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css' rel='stylesheet' type='text/css'>

    @if(session('data.userRole') == 'admin')
    <div class="row">
        <div class="col-md-3">
            <input type="text" id="date_from" class="form-control" placeholder="From Date" autocomplete="off" required>
        </div>
        <div class="col-md-3">
            <input type="text" id="date_to" class="form-control" placeholder="To Date" autocomplete="off" required>
        </div>
        <div class="col-md-3">
            <select id="org_admin" class="form-control select2-multiple" style="width: 100%;">
                <option value="All" selected>Select All</option>
            </select>
        </div>
        <div class="col-md-3">
            <button id="filter_button" class="btn btn-primary">Filter</button>
            <button id="csv_export_button" class="btn btn-primary">Export CSV</button>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-md-3">
            <input type="text" id="date_from" class="form-control" placeholder="From Date" autocomplete="off" required>
        </div>
        <div class="col-md-3">
            <input type="text" id="date_to" class="form-control" placeholder="To Date" autocomplete="off" required>
        </div>
        <div class="col-md-3">
            <select id="org" class="form-control select2-multiple" style="width: 100%;">
                <!-- Add options if needed -->
                <option value="All" selected>Select All</option>
            </select>
        </div>

        <div class="col-md-3">
            <button id="filter_button" class="btn btn-primary">Filter</button>
            <button id="csv_export_button" class="btn btn-primary">Export CSV</button>
        </div>
    </div>
    @endif

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

    @if(session('data.userRole') == 'admin')
    <table class="table table-striped" id="user_table">
        <thead>
            <tr>
                <th>Sr.No</th>
                <th>Organization</th>
                <th>Username</th>
                <th>Module</th>
                <th>Request</th>
                <th>Status</th>
                <th>Timestamp</th>
            </tr>
        </thead>
    </table>
    @else
        <table class="table table-striped" id="user_table">
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Organization</th>
                    <th>Username</th>
                    <th>Vendor</th>
                    <th>Module</th>
                    <th>Request</th>
                    <th>Status</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
        </table>
    @endif
</div> 
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


    var csrfToken = $('meta[name="csrf-token"]').attr('content');

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


    $.ajax({
    url: "{{ route('user.names') }}",
    type: "POST", 
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') 
    },
    success: function(data) {
        $('#org_admin').select2({
            data: data,
            minimumInputLength: 0,
            multiple: true
        });
    },
    cache: true
});


    // $('#org_admin').select2({
    //     ajax: {
    //         url: "{{ route('user.names') }}",
    //         type: "POST", 
    //         headers: {
    //             "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') 
    //         },
    //         success: function(data) {
    //         $('#org_admin').select2({
    //             data: data
    //         });
    //         },
    //             cache: true
    //         },
    // minimumInputLength: 0,
    // multiple: true 
    // });

    // $.ajax({
    // url: "{{ route('user.names') }}",
    // type: "POST", 
    // headers: {
    //     "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') 
    // },
    // success: function(data) {
    //     $('#org_admin').select2({
    //         data: data
    //     });
    //     }
    // });



// Code for Select2 AJAX data retrieval
// $('#org_admin').select2({
//     ajax: {
//         url: "{{ route('user.names') }}",
//         type: "POST", 
//         headers: {
//             "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content') 
//         },
//         processResults: function(data) {
//             // Assuming data is an array of user objects with 'id' and 'text' properties
//             var userOptions = data.map(function(user) {
//                 return { id: user.username, text: user.username };
//             });

//             return {
//                 results: userOptions
//             };
//         },
//         cache: true
//     },
//     minimumInputLength: 0,
//     multiple: true 
// });


    $('#org').on('change', function() {
        var selectedValue = $(this).val();
        
        if (selectedValue != 'All') {   
        $("#org option:selected").removeAttr("selected");
        }
        else if(selectedValue == 'All') {
        $('#org option:selected').removeAttr('selected');
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


$("#filter_button").click(function () {
    
    var date_from = $("#date_from").val();
    var date_to = $("#date_to").val();
    var org = "{{ session('data.userRole') == 'admin' ? 'org_admin' : 'org' }}";
    var selectedOrg = $("#" + org).val();
    console.log(selectedOrg);

    if (date_from == '') {
        alert('Please select from date');
        return false;
    } else if (date_to == '') {
        alert('Please select to date');
        return false;
    } else if (selectedOrg == '') {
        alert('Please select valid name');
        return false;
    } else {
        $.ajax({
            url: "{{ route('dashboardreport.list') }}",
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
                loader.style.display = 'block';
                // var dataTable = $('#user_table').DataTable();
                var dataTable = $('#user_table').DataTable();
                dataTable.clear().draw(); 
                loader.style.display = 'none';
                var data = response.data;
                console.log(data);
                if (data.length == 0) {
                    //dataTable.clear().destroy(); 
                    //$('#user_table').empty(); 
                    // $('#user_table.dataTables_empty').html('<p>No data available.</p>');
                    //$('#user_table tbody').empty();
                   // $('#user_table tbody tr').html('<p>No data available.</p>');
                } else { 

                    dataTable.clear().destroy(); 
                    $('#user_table').empty(); 
                    var columnNames = Object.keys(data[0]); 
                    var tableHeaders = '<thead><tr>' +
                        '<th>Sr.No</th>' +
                        '<th>organization</th>'+
                        '<th>username</th>';
                    // Conditionally add the "Vendor" column based on user role
                    @if(session('data.userRole') != 'admin')
                        tableHeaders += '<th>Vendor</th>';
                    @endif
                        tableHeaders += '<th>Module</th>' +
                        '<th>Request</th>' +
                        '<th>Status</th>' +
                        '<th>timestamp</th>' +
                        '</tr></thead>';

                    $('#user_table').append(tableHeaders); 
                    var tbody = '<tbody>';
                    data.forEach(function (rowData, index) {
                        var row = '<tr>';
                        row += '<td>' + (index + 1) + '</td>';

                        @if(session('data.userRole') === 'super_admin')

                        for (var i = 0; i < columnNames.length; i++) {
                            var columnName = columnNames[i];
                            if (columnName === 'response_status_code') {
                                // Apply custom rendering for 'response_status_code' column
                                var cellData = rowData[columnName];
                                if (cellData == 200 || cellData == 201) {
                                    row += '<td><b class="text-success">Success</b></td>';
                                } else {
                                    row += '<td><b class="text-danger">Failed</b></td>';
                                }
                            }
                            else {
                                row += '<td>' + rowData[columnName] + '</td>';
                            }
                        }

                        @else 
                        
                        for (var i = 0; i < columnNames.length; i++) {
                            var columnName = columnNames[i];
                            if (columnName === 'response_status_code') {
                                // Apply custom rendering for 'response_status_code' column
                                var cellData = rowData[columnName];
                                if (cellData == 200 || cellData == 201) {
                                    row += '<td><b class="text-success">Success</b></td>';
                                } else {
                                    row += '<td><b class="text-danger">Failed</b></td>';
                                }
                            } else if (columnName === 'vendor') {
                                // Skip adding the column if the user role is admin
                                @if(session('data.userRole') === 'admin')
                                continue;
                                @endif
                            } else {
                                row += '<td>' + rowData[columnName] + '</td>';
                            }
                        }
                        @endif

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


var table = $('#user_table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('dashboardreport.list') }}",
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
            { data: 'organization', name: 'organization' },
            { data: 'Username', name: 'Username' },
            @if(session('data.userRole') != 'admin')
                    { data: 'vendor', name: 'vendor' },
            @endif
            { data: 'Module', name: 'Module' },
            { data: 'request', name: 'request' },
            { 
                data: 'response_status_code', 
                name: 'response_status_code',
                render: function (data, type, row) {
                    if (data == 200 || data == 201) {
                        return '<b class="text-success">Success</b>';
                    } else{
                        return '<b class="text-danger">Failed</b>';
                    }
                }
            },
            { data: 'timestamp', name: 'timestamp' },
        ]
    });

    /////////////////////////////////

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
            return false;
        } else if (date_to == '') {
            alert('Please select to date');
            return false;
        } else if (selectedOrg == '') {
            alert('Please select valid name');
            return false;
        } else {

            $.ajax({
                    url: "{{ route('dashboardreport.csv') }}",
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
                        //console.log(response);
                        if (response.download) {
            // Create a temporary <a> element to trigger the file download
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
