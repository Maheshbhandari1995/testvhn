
@extends('layout')

@section('content')
<div id="main" class="main">
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

<style>
    .box-new-design{
        background-image: -webkit-linear-gradient(180deg, #FFF 0%, rgba(255, 255, 255, 0.00) 100%);
        background-image: -webkit-radial-gradient(180deg, #FFF 0%, rgba(255, 255, 255, 0.00) 100%);
        background: linear-gradient(180deg, #FFF 0%, rgba(255, 255, 255, 0.00) 100%);
        box-shadow: 0px 4px 38px 0px rgba(161, 225, 210, 0.00);
        margin-top: 5rem;
        padding: 30px;
    }

    button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
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
    <div class="row justify-content-center">
        <div class="col-md-5 ">
            <div class="box-new-design">
                <div class="row">
                    <div class="col-lg-12 margin-tb text-center">
                        <div class="pagetitle">
                            <h1>Api Report</h1>
                        </div>
                    </div>
                    <div class="col-md-12 mb-2">
                        <input type="text" id="date_from" class="form-control" placeholder="From Date" autocomplete="off" required>
                    </div>
                    <div class="col-md-12 mb-2">
                        <input type="text" id="date_to" class="form-control" placeholder="To Date" autocomplete="off" required>
                    </div> 
                    <div class="col-md-12 ">
                        <button id="csv_export_button" class="btn btn-primary" style="width:100%;     border-radius: 6px;">Export CSV</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div> 

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

<script>
    $(document).ready(function(){

        $('.select2-multiple').select2({
            placeholder: "Select",
            allowClear: true
        });

        var today = new Date();
        today.setDate(today.getDate() - 1);
        var date = new Date();
		var currentMonth = date.getMonth();
		var currentDate = date.getDate();
		var currentYear = date.getFullYear();

        $('#date_from').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            minDate: new Date(currentYear, currentMonth-2, currentDate),
            maxDate: today,
            changeMonth: true,
            changeYear: false,
        });

        $('#date_to').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            minDate: new Date(currentYear, currentMonth-2, currentDate),
            maxDate: today,
            changeMonth: true,
            changeYear: false,
        });  

        

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    /////////////////////////////////

    $("#csv_export_button").click(function () {
        var loader = document.getElementById('loader');
        loader.style.display = 'block';
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var date_from = $("#date_from").val();
        var date_to = $("#date_to").val();

        if (date_from == '') {
            alert('Please select from date');
            loader.style.display = 'none';
            return false;
        } else if (date_to == '') {
            alert('Please select to date');
            loader.style.display = 'none';
            return false;
        }else {

            $.ajax({
                    url: "{{ route('apireport.csv') }}",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken
                    },
                    data: {
                    date_from: date_from,
                    date_to: date_to,
                    },
                    success: function (response) {
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
                        loader.style.display = 'none';
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr);
                    }
                });
            }
    });

});
    </script>
    @endsection
