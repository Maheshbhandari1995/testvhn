
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

    tbody tr {
        word-break: break-word;
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


    .card {
        /* margin-bottom: -30px !important; */
        border: none;
        border-radius: 5px;
        box-shadow: 0px 0 30px rgba(1, 41, 112, 0.1);
    }


    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
	input[type=text]{
		text-transform:uppercase;
	}
</style>
<!-- Main container -->
<div id="main" class="main">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pagetitle">
                <h1>RC Bulk Report</h1>
            </div>
        </div>
    </div>
   
    <div class="alert alert-success" id="alertMessage" style="display:none;">
        <p id="successMSG"></p>
    </div>
    @if(session('data.userRole') == 'user')
    <div id="validate" style="color:red;"></div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <label class="form-label mt-3" for="file">Upload file</label>
                <div class="col-lg-6">
                    {{-- <label class="form-label" for="file">Upload file</label> --}}
                    <input type="file" class="form-control file_upload" id="file" name="file" accept=".csv" required>
                    @error('file') 
                        <span>{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="col-lg-6">
                    {{-- <label class="mt-2">&nbsp;</label> --}}
                    <div class="d-flex">
                        <div class="col-lg-auto me-3">
                            <button id="submitBtn" class="btn btn-primary">Submit</button>
                        </div>
                        <div class="col-lg-auto">
                            <a href="{{ asset('/storage/app/public/uploads/rcbulk/sample.csv') }}" class="btn btn-outline-primary">Sample CSV</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    </div>
    @endif
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
    <div class="re_details"></div>
    <div class="row">
    </div>
    <div class="table-responsive">
        <table class="table table-striped" id="user_table">
            <thead>
                <tr>
                    <th>Sr.</th>
                    <th>Module</th>
                    <th>Organization</th>
                    <th style="width:200px;">Filename</th>
                    <th>Uploaded Count</th>
                    <th>Processed Count</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 116px;">Created at</th>
                    <th style="width: 70px;">Download</th>
                    {{-- <th>Re-push</th> --}}
                </tr>
            </thead>
        </table>
    </div>
</div>

<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script>

// window.addEventListener('beforeunload', function (e) {
//             e.preventDefault();
//             e.returnValue = '';
//         });

$(document).ready(function(){
    
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var sessionData = @json(session()->all());
        var userRole = sessionData.data.userRole;
        $('#user_table').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('rcbulkreport.list') }}",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken // Include the CSRF token in the headers
                }
            },
            order: [[7, 'desc']],
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
                { data: 'filename', name: 'filename' },
                { data: 'count', name: 'count' },
                { data: 'processed_count', name: 'processed_count' }, 
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
                            <?php

                            $url = request()->root();  //root url including scheme ,host and path(EX: https://172.30.10.102/vahan)
                            $parsedUrl = parse_url($url);
                            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];

                            ?>
                            data = "{{$baseUrl}}" + data;
                            return '<b class="text-success"><a href="' + data + '" class=""><i class="bi bi-download"></i></a></b>';
                        }
                    },
                    orderable: false,
                    searchable: false
                },
                // {
                //     data: 'id',
                //     render: function(data, type, row) { 
                //         var recall = "";   
                //         if(userRole == 'admin' || userRole == 'super_admin'){   
                //             recall = "<a class='text-danger re-call-script' title='Re call the script'  style='cursor: pointer;' key-value = "+data+"  onclick='reCallScript(event)'><i class='bi bi-bootstrap-reboot'></i></a>";
                //         }
                //         return recall;
                //     },
                //     orderable: false,
                //     searchable: false
                // }
            ]
        });


        setInterval(function() {
            $('#user_table').DataTable().ajax.reload(null, false);
        }, 10000);


    $('#submitBtn').click(function() {
    // Get the file input element
    var fileInput = document.getElementById('file');


    if (fileInput.files.length == 0) {
      alert('Please select a file.');
      return; // Stop further execution
    }

    var fileType = fileInput.files[0].type;
    if (fileType == 'text/csv') {

      
        var formData = new FormData();

       
        formData.append('rcdata', fileInput.files[0]);

       
        var loader = document.getElementById('loader');
        loader.style.display = 'block';
        $(".re_details").css('display', 'none');
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

                    
            $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': csrfToken
            }
            });
       
        $.ajax({
          url: "{{ route('rcAuthBulk.postData') }}",
          type: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function(response) {
            if(response.status == 'success'){
                
                showAlert(response.status,response.msg, true);

                loader.style.display = 'none';
                
                $('#file').val('');
            
                if (response.download) {

                var link = document.createElement('a');
                link.href = response.file_url;
                link.download = response.file_name;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                }
            }else{
                showAlert(response.status,response.msg, true);
                loader.style.display = 'none';
            }
          },
          error: function(xhr, status, error) {
              console.log('AJAX Error:', error);
              loader.style.display = 'none';
          }
        });

    }
    else{
        alert('Please upload a CSV file.');
      return; // Stop further execution
      return false; // Stop further execution
    }
  });

});

    function reCallScript(event) {
        event.preventDefault();
        var keyValue = event.currentTarget.getAttribute('key-value');

        Swal.fire({
            title: "Are you sure?",
            text: "You are about to re call the script: " + keyValue,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "RE-PUSH",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rcbulkreport') }}/" + keyValue + "/recall",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        req_type: 'recallBulk'
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            // Reload the datatable or perform any necessary updates
                            $('#user_table').DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    }

    function showAlert(status,message, isSuccess) {
        var alertElement = $("#alertMessage");
        var messageElement = $("#successMSG");

       
        messageElement.html(message);

       
        if (status == 'success') {
            alertElement.addClass('alert-success').removeClass('alert-danger');
        } else {
            alertElement.addClass('alert-danger').removeClass('alert-success');
        }

        alertElement.fadeIn();

        setTimeout(function() {
           
            alertElement.fadeOut();
        }, 11000); 
    }

</script>
@endsection
