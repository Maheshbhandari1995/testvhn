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
                        <table class="table table-borderless">
                            <tbody>
                            </tbody>
                        </table>
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
                        <table class="table table-bg-colored">
                            <tbody>
                            </tbody>
                        </table>
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
            if(isValidVehicleNumber === true && isValidVehicleNumber != '')
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
                    url: "{{ route('rc.rcSignPostData') }}", // Path to controller through routes/web.php
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
            vehicleDetailsHtml += '<td>' + response.regNo + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Class</th>';
            vehicleDetailsHtml += '<td>' + response.class + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Chassis</th>';
            vehicleDetailsHtml += '<td>' + response.chassis + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Engine</th>';
            vehicleDetailsHtml += '<td>' + response.engine + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Manufacturer Name</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleManufacturerName + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Number</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleNumber + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Status As On</th>';
            vehicleDetailsHtml += '<td>' + response.statusAsOn + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Type</th>';
            vehicleDetailsHtml += '<td>' + response.type + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Unladen Weight</th>';
            vehicleDetailsHtml += '<td>' + response.unladenWeight + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Category</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleCategory + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Colour</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleColour + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Cubic Capacity</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleCubicCapacity + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Cylinders No</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleCylindersNo + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Insurance Company Name</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleInsuranceCompanyName + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Insurance Policy Number</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleInsurancePolicyNumber + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Insurance Upto</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleInsuranceUpto + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Manufacturing Month/Year</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleManufacturingMonthYear + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Seat Capacity</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleSeatCapacity + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Sleeper Capacity</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleSleeperCapacity + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Standing Capacity</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleStandingCapacity + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Tax Upto</th>';
            vehicleDetailsHtml += '<td>' + response.vehicleTaxUpto + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Wheelbase</th>';
            vehicleDetailsHtml += '<td>' + response.wheelbase + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>RC Expiry Date</th>';
            vehicleDetailsHtml += '<td>' + response.rcExpiryDate + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>RC Financer</th>';
            vehicleDetailsHtml += '<td>' + response.rcFinancer + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>RC Standard Cap</th>';
            vehicleDetailsHtml += '<td>' + response.rcStandardCap + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Reg Authority</th>';
            vehicleDetailsHtml += '<td>' + response.regAuthority + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Reg Date</th>';
            vehicleDetailsHtml += '<td>' + response.regDate + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '</tbody>';
            vehicleDetailsHtml += '</table>';
            //   vehicleDetailsHtml += '<th>Vehicle Manufacturer Name</th>';
            //   vehicleDetailsHtml += '<td>' + response.vehicleManufacturerName + '</td>';
            //   vehicleDetailsHtml += '</tr>';
            //   vehicleDetailsHtml += '</tbody>';
            //   vehicleDetailsHtml += '</table>';

            $('.searched-details #collapseOne .accordion-body table').html(vehicleDetailsHtml);

            // Personal Details
            var personalDetailsHtml = '<table class="table table-borderless">';
            personalDetailsHtml += '<tbody>';
            personalDetailsHtml += '<tr>';
            personalDetailsHtml += '<th width="30%">Owner</th>';
            personalDetailsHtml += '<td>' + response.owner + '</td>';
            personalDetailsHtml += '</tr>';
            personalDetailsHtml += '<tr>';
            personalDetailsHtml += '<th>Owner Father Name</th>';
            personalDetailsHtml += '<td>' + response.ownerFatherName + '</td>';
            personalDetailsHtml += '</tr>';
            personalDetailsHtml += '<tr>';
            personalDetailsHtml += '<th>Mobile Number</th>';
            personalDetailsHtml += '<td>' + response.mobileNumber + '</td>';
            personalDetailsHtml += '</tr>';
            personalDetailsHtml += '<tr>';
            personalDetailsHtml += '<th>Status</th>';
            personalDetailsHtml += '<td>' + response.status + '</td>';
            personalDetailsHtml += '</tr>';
            personalDetailsHtml += '<tr>';
            personalDetailsHtml += '<th>Status As On</th>';
            personalDetailsHtml += '<td>' + response.statusAsOn + '</td>';
            personalDetailsHtml += '</tr>';
            personalDetailsHtml += '</tbody>';
            personalDetailsHtml += '</table>';

            $('.searched-details #collapseTwo .accordion-body table').html(personalDetailsHtml);

            // Address Details
            var addressDetailsHtml = '<table class="table table-bg-colored">';
            addressDetailsHtml += '<tbody>';
            addressDetailsHtml += '<tr>';
            addressDetailsHtml += '<th width="30%">Address Line</th>';
            addressDetailsHtml += '<td>' + response.splitPermanentAddress.addressLine + '</td>';
            addressDetailsHtml += '</tr>';
            addressDetailsHtml += '<tr>';
            addressDetailsHtml += '<th>City</th>';
            addressDetailsHtml += '<td>' + response.splitPermanentAddress.city[0] + '</td>';
            addressDetailsHtml += '</tr>';
            addressDetailsHtml += '<tr>';
            addressDetailsHtml += '<th>Pincode</th>';
            addressDetailsHtml += '<td>' + response.splitPermanentAddress.pincode + '</td>';
            addressDetailsHtml += '</tr>';
            addressDetailsHtml += '<tr>';
            addressDetailsHtml += '<th>District</th>';
            addressDetailsHtml += '<td>' + response.splitPermanentAddress.district[0] + '</td>';
            addressDetailsHtml += '</tr>';
            addressDetailsHtml += '<tr>';
            addressDetailsHtml += '<th>State</th>';
            addressDetailsHtml += '<td>' + response.splitPermanentAddress.state[0][0] + '</td>';
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