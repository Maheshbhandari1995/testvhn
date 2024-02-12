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
<!-- Main container -->
<div id="main" class="main">
    <div class="row">
        <div class="pagetitle">
            <h1>RC Details</h1>
        </div>
    </div>

    <div id="validate" style="color:red;"></div>
    <div class="row g-3">
        <div class="col-lg-3">
            <input type="text" id="vehicleNoInput" placeholder="" class="form-control capitalized-text text-uppercase" required>
            <label class="form-element-label" for="vehicleNoInput">Vehicle Number</label>
        </div>
        <div class="col-lg-auto">
            <button id="submitBtn" class="btn btn-primary">Submit</button>
        </div>
        <div class="col-lg-auto error-message">
            
        </div>
    </div>
    
    <div class="searched-details" style="display:none;">
        <div class="accordion" id="accordionExample">
            <div class="accordion-item">
                <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Vehicle Details
                </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Personal Details
                </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Address Details
                </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bg-colored">
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- <div class="re_details searched-details"></div> --}}
    
    <div class="row no-data g-3">
        <div class="col-lg-6">
            <div class="no-data-content">
                <div id="noDataFound">
                    <h4 >No Data Found</h4>
                    <p>Searched vehicle detail will be displayed here. <br> To search enter vehicle number</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <img src="assets/img/searching-data.png" alt="searching-data">
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
    
    
</div>
<!-- //Main container -->
<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#submitBtn').click(function() {
            var vehicleNo = $('#vehicleNoInput').val().toUpperCase().trim();
            var isValidVehicleNumber = validateVehicleNumber(vehicleNo);
            if(isValidVehicleNumber === true)
            {
                // Show loader
                var loader = document.getElementById('loader');
                loader.style.display = 'block';
                $(".re_details").css('display','none');

                $('#validate').html();

                 // Get the CSRF token value from the meta tag
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                    // Add the CSRF token to the AJAX request headers
                $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
                });
                // Make the Ajax request
                $.ajax({
                    url: "{{ route('rc.rcAuthPostData') }}", // Path to controller through routes/web.php
                    type: 'POST',
                    data: { vehicleNo: vehicleNo },
                    dataType: 'json',
                    success: function(response) {
                       // console.log(response.status);
                        // Hide loader
                        loader.style.display = 'none';
                        $(".re_details").css('display', 'block');
                        if(response.status_code == 200)
                        {   
                            $(".re_details").css('display', 'block');
                            $(".no-data").hide();
                            $(".searched-details").css('display', 'block');
                            displayDetails(response.msg);
                        }
                        else if(response.error){
                            $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p>'+response.error+'.</p>');
                            $(".searched-details").css('display', 'none');
                            $('.error-message').html('');
                            $(".re_details").css('display', 'none');
                            $(".no-data").show();
                        }
                        else if(response.status){
                            $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p>'+response.status+'.</p>');
                            $(".searched-details").css('display', 'none');
                            $('.error-message').html('');
                            $(".re_details").css('display', 'none');
                            $(".no-data").show();
                        }
                        else{
                            $('.error-message').html('<p> Vehicle no does not exist </p>');
                            }
                        //alert(response);
                        
                    },
                    error: function(xhr, status, error) {
                        // Handle the error
                        console.log('AJAX Error:', error);
                    }
                });
            }
            else{
                $(".re_details").css('display', 'block');
                $(".searched-details").css('display', 'none');
                 $('.error-message').html('<p> Please enter valid vehicle no </p>');
            }
        });


function displayDetails(response) {
    // Vehicle Details
    var vehicleDetailsHtml = '<table class="table table-borderless">';
    vehicleDetailsHtml += '<tbody>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th width="30%">Reg No</th>';
    vehicleDetailsHtml += '<td>' + response["Registration Details"]["Registration Number"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Class</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Vehicle Class"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Chassis</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Chassis Number"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Engine</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Engine Number"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Manufacturer Name</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Maker/Manufacturer"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Model / Makers Class</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Model / Makers Class"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Number</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Vehicle Number"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Status As On</th>';
    vehicleDetailsHtml += '<td>' + response["Registration Details"]["Status As On"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Type</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Body Type"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Unladen Weight</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Unloading Weight"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Category</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Vehicle Category"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Colour</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Color"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Cubic Capacity</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Engine Capacity"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Cylinders No</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["No of cylinder"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Insurance Company Name</th>';
    vehicleDetailsHtml += '<td>' + response["Insurance Details"]["Insurance Company"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Insurance Policy Number</th>';
    vehicleDetailsHtml += '<td>' + response["Insurance Details"]["Policy Number"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Insurance Upto</th>';
    vehicleDetailsHtml += '<td>' + response["Insurance Details"]["Insurance To Date/Insurance Upto"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Manufacturing Month/Year</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Manufacture Date"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Seat Capacity</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Seating Capacity"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Sleeper Capacity</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["sleeper Capacity"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Standing Capacity</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Vehicle Standing Capacity"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Vehicle Tax Upto</th>';
    vehicleDetailsHtml += '<td>' + response["Registration Details"]["Vehicle Tax Up to"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>RC Expiry Date</th>';
    vehicleDetailsHtml += '<td>' + response["Registration Details"]["Fitness Date/RC Expiry Date"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>RC Standard Cap</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Rc Standard Cap"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Reg Authority</th>';
    vehicleDetailsHtml += '<td>' + response["Registration Details"]["RTO"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Reg Date</th>';
    vehicleDetailsHtml += '<td>' + response["Registration Details"]["Registration Date"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Blacklist Status</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Blacklist Status"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Engine Capacity</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Engine Capacity"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Fuel Type</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Fuel Type"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Gross Weight</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Gross Weight"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Is Commercial</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Is Commercial"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Noc Details</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Noc Details"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Norms Type</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Norms Type"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Owner Serial Number</th>';
    vehicleDetailsHtml += '<td>' + response["Vehicle Details"]["Owner Serial Number"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>National Permit Issued By</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["National Permit Issued By"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>National Permit Number</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["National Permit Number"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>National Permit Upto</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["National Permit Upto"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Non Use From</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["Non Use From"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Non Use Status</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["Non Use Status"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Non Use To</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["Non Use To"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>PUCC NO</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["PUCC NO"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>PUCC Upto</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["PUCC Upto"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Permit Issue Date</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["Permit Issue Date"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Permit Number</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["Permit Number"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Permit Type</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["Permit Type"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Permit Vald From</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["Permit Vald From"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '<tr>';
    vehicleDetailsHtml += '<th>Permit Valid Upto</th>';
    vehicleDetailsHtml += '<td>' + response["RC Status"]["Permit Valid Upto"] + '</td>';
    vehicleDetailsHtml += '</tr>';
    vehicleDetailsHtml += '</tbody>';
    vehicleDetailsHtml += '</table>';

    $('.searched-details #collapseOne .accordion-body table').html(vehicleDetailsHtml);

    // Personal Details
    var personalDetailsHtml = '<table class="table table-borderless">';
    personalDetailsHtml += '<tbody>';
    personalDetailsHtml += '<tr>';
    personalDetailsHtml += '<th width="30%">Owner</th>';
    personalDetailsHtml += '<td>' + response["Owners Details"]["Owners Name"] + '</td>';
    personalDetailsHtml += '</tr>';
    personalDetailsHtml += '<tr>';
    personalDetailsHtml += '<th>Owner Father Name</th>';
    personalDetailsHtml += '<td>' + response["Owners Details"]["Father Name/Husband Name"] + '</td>';
    personalDetailsHtml += '</tr>';
    personalDetailsHtml += '<tr>';
    personalDetailsHtml += '<th>Mobile Number</th>';
    personalDetailsHtml += '<td>' + response["Owners Details"]["Mobile Number"] + '</td>';
    personalDetailsHtml += '</tr>';
    personalDetailsHtml += '<tr>';
    personalDetailsHtml += '<th>Status</th>';
    personalDetailsHtml += '<td>' + response["Registration Details"]["status"] + '</td>';
    personalDetailsHtml += '</tr>';
    personalDetailsHtml += '<tr>';
    personalDetailsHtml += '<th>Status As On</th>';
    personalDetailsHtml += '<td>' + response["Registration Details"]["Status As On"] + '</td>';
    personalDetailsHtml += '</tr>';
    personalDetailsHtml += '</tbody>';
    personalDetailsHtml += '</table>';

    $('.searched-details #collapseTwo .accordion-body table').html(personalDetailsHtml);

    // Address Details
    var addressDetailsHtml = '<table class="table table-bg-colored">';
    addressDetailsHtml += '<tbody>';
    addressDetailsHtml += '<tr>';
    addressDetailsHtml += '<th width="30%"> Permanent address</th>';
    addressDetailsHtml += '<td>' + response["Owners Details"]["Permanent Address"] + '</td>';
    addressDetailsHtml += '</tr>';
    addressDetailsHtml += '<tr>';
    addressDetailsHtml += '<th width="30%"> Present address</th>';
    addressDetailsHtml += '<td>' + response["Owners Details"]["Present Address"] + '</td>';
    addressDetailsHtml += '</tr>';
    addressDetailsHtml += '<tr>';
    addressDetailsHtml += '<th>City</th>';
    addressDetailsHtml += '<td>' + response["Owners Details"]["Permanant Address City"] + '</td>';
    addressDetailsHtml += '</tr>';
    addressDetailsHtml += '<tr>';
    addressDetailsHtml += '<th>Pincode</th>';
    addressDetailsHtml += '<td>' + response["Owners Details"]["Permanant Address Pincode"] + '</td>';
    addressDetailsHtml += '</tr>';
    addressDetailsHtml += '<tr>';
    addressDetailsHtml += '<th>District</th>';
    addressDetailsHtml += '<td>' + response["Owners Details"]["Permanant Address District"] + '</td>';
    addressDetailsHtml += '</tr>';
    addressDetailsHtml += '<tr>';
    addressDetailsHtml += '<th>State</th>';
    addressDetailsHtml += '<td>' + response["Owners Details"]["Permanant Address State"] + '</td>';
    addressDetailsHtml += '</tr>';
    addressDetailsHtml += '</tbody>';
    addressDetailsHtml += '</table>';

    $('.searched-details #collapseThree .accordion-body table').html(addressDetailsHtml);

    $('.searched-details').css('display', 'block');
    $('.error-message').html('');
}


});
</script>

@endsection