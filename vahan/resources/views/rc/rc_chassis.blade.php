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
            <h1>RC With Chassis Number</h1>
        </div>
    </div>

    <div id="validate" style="color:red;"></div>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Vahicle Chassis Number</h5>
            <div class="row g-3">
                <div class="col-auto">
                    
                    <input type="text" id="vehicleNoInput" placeholder="Enter Chassis Number" class="form-control capitalized-text text-uppercase" required>
                </div>
                <div class="col-auto">

                    <button id="submitBtn" class="btn btn-primary">Submit</button>
                </div>
                <div class="col-lg-auto error-message">
            </div>
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
                        <table class="table table-bg-colored">
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row no-data g-3">
        <div class="col-lg-6">
            <div class="no-data-content">
                <div id="noDataFound">
                    <h4 >No Data Found</h4>
                    <p>Searched chassis detail will be displayed here. <br> To search enter chassis number</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <img src="assets/img/searching-data.png" alt="searching-data">
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
    
</div>

<!-- //Main container -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
    $(document).ready(function() {
        $('#submitBtn').click(function() {
            var chassisNumber = $('#vehicleNoInput').val().toUpperCase().trim();
            var isValidchassisNumber = validateChassisNumber(chassisNumber);
            if(isValidchassisNumber === true && isValidchassisNumber != '' && chassisNumber != '')
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
                    url: "{{ route('rc.rcWithChassisPostData') }}", // Path to controller through routes/web.php
                    type: 'POST',
                    data: { chassisNumber: chassisNumber },
                    dataType: 'json',
                    success: function(response) {
                       console.log(response);
                        // Hide loader
                        loader.style.display = 'none';
                        $(".re_details").css('display', 'block');
                        if(response.code == 200)
                        {   
                            $(".re_details").css('display', 'block');
                            $(".no-data").hide();
                            $(".searched-details").css('display', 'block');
                            displayDetails(response.result);
                        }
                        else if(response.error){
                            $('#noDataFound').css('display', 'block');
                            $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p>'+response.error+'.</p>');
                            $(".searched-details").css('display', 'none');
                            $('.error-message').html('');
                            $(".re_details").css('display', 'none');
                            $(".no-data").show();
                        }
                        else if(response.status){
                            $('#noDataFound').css('display', 'block');
                            $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p>'+response.status+'.</p>');
                            $(".searched-details").css('display', 'none');
                            $('.error-message').html('');
                            $(".re_details").css('display', 'none');
                            $(".no-data").show();
                        }
                        else{
                            $('.error-message').html('<p> chassis no does not exist </p>');
                            $('#noDataFound').css('display', 'block');
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
                 $('.error-message').html('<p> Please enter valid chassis no </p>');
                 $('#noDataFound').css('display', 'block');
            }
        });

        function displayDetails(response) {
            var vehicleDetailsHtml = '<table class="table table-borderless">';
            vehicleDetailsHtml += '<tbody>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th width="30%">Vehicle_No</th>';
            vehicleDetailsHtml += '<td>' + response.data.Vehicle_Num + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Chasis_No</th>';
            vehicleDetailsHtml += '<td>' + response.data.Chasis_No + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Model</th>';
            vehicleDetailsHtml += '<td>' + response.data.Model + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Regist_Date</th>';
            vehicleDetailsHtml += '<td>' + response.data.Regist_Date + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Rto</th>';
            vehicleDetailsHtml += '<td>' + response.data.Rto + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Fuel_Type</th>';
            vehicleDetailsHtml += '<td>' + response.data.Fuel_Type + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Engine_No</th>';
            vehicleDetailsHtml += '<td>' + response.data.Engine_No + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle_Class</th>';
            vehicleDetailsHtml += '<td>' + response.data.Vehicle_Class + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Meta</th>';
            vehicleDetailsHtml += '<td>' + response.data.Meta + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>vehicle CC</th>';
            vehicleDetailsHtml += '<td>' + response.data.CC + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle color</th>';
            vehicleDetailsHtml += '<td>' + response.data.Color + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Weight</th>';
            vehicleDetailsHtml += '<td>' + response.data.Weight + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Insurance Upto</th>';
            vehicleDetailsHtml += '<td>' + response.data.Insurance_Upto + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Previous_Insurer</th>';
            vehicleDetailsHtml += '<td>' + response.data.Previous_Insurer + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Insurance_Upto</th>';
            vehicleDetailsHtml += '<td>' + response.data.Insurance_Upto + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Type</th>';
            vehicleDetailsHtml += '<td>' + response.data.Type + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle model</th>';
            vehicleDetailsHtml += '<td>' + response.data.Car.ModelName + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Vehicle Tax Upto</th>';
            vehicleDetailsHtml += '<td>' + response.data.Tax_Upto + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Fit_Upto</th>';
            vehicleDetailsHtml += '<td>' + response.data.Fit_Upto + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Puc_No</th>';
            vehicleDetailsHtml += '<td>' + response.data.Puc_No + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>RC Financier</th>';
            vehicleDetailsHtml += '<td>' + response.data.Financier + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Norms_Desc</th>';
            vehicleDetailsHtml += '<td>' + response.data.Norms_Desc + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>RC_Status</th>';
            vehicleDetailsHtml += '<td>' + response.data.RC_Status + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '<tr>';
            vehicleDetailsHtml += '<th>Manu_Date</th>';
            vehicleDetailsHtml += '<td>' + response.data.Manu_Date + '</td>';
            vehicleDetailsHtml += '</tr>';
            vehicleDetailsHtml += '</tbody>';
            vehicleDetailsHtml += '</table>';
            //   vehicleDetailsHtml += '<th>Vehicle Manufacturer Name</th>';
            //   vehicleDetailsHtml += '<td>' + response.data.vehicleManufacturerName + '</td>';
            //   vehicleDetailsHtml += '</tr>';
            //   vehicleDetailsHtml += '</tbody>';
            //   vehicleDetailsHtml += '</table>';

            $('.searched-details #collapseOne .accordion-body table').html(vehicleDetailsHtml);

            // Personal Details
            var personalDetailsHtml = '<table class="table table-borderless">';
            personalDetailsHtml += '<tbody>';
            personalDetailsHtml += '<tr>';
            personalDetailsHtml += '<th width="30%">Owner</th>';
            personalDetailsHtml += '<td>' + response.data.Owner_Name + '</td>';
            personalDetailsHtml += '</tr>';
            personalDetailsHtml += '<tr>';
            personalDetailsHtml += '<th>Owner Father Name</th>';
            personalDetailsHtml += '<td>' + response.data.Father + '</td>';
            personalDetailsHtml += '</tr>';
            personalDetailsHtml += '<tr>';
            personalDetailsHtml += '<th>Owner_Num</th>';
            personalDetailsHtml += '<td>' + response.data.Owner_Num + '</td>';
            personalDetailsHtml += '</tr>';
            personalDetailsHtml += '</tbody>';
            personalDetailsHtml += '</table>';
            $('.searched-details #collapseTwo .accordion-body table').html(personalDetailsHtml);
            // Address Details
            var addressDetailsHtml = '<table class="table table-bg-colored">';
            addressDetailsHtml += '<tbody>';
            addressDetailsHtml += '<tr>';
            addressDetailsHtml += '<th width="30%">present address</th>';
            addressDetailsHtml += '<td>' + response.data.Present_Address + '</td>';
            addressDetailsHtml += '</tr>';
            addressDetailsHtml += '<tr>';
            addressDetailsHtml += '<th>permanant address</th>';
            addressDetailsHtml += '<td>' + response.data.Permanent_Address + '</td>';
            addressDetailsHtml += '</tr>';
            addressDetailsHtml += '</tbody>';
            addressDetailsHtml += '</table>';

            $('.searched-details #collapseThree .accordion-body table').html(addressDetailsHtml);

            $('.searched-details').css('display', 'block');
            $('.error-message').html('');
        }
    });

    function validateChassisNumber(Number) {
            // Check if the vehicle number is empty
            if (Number.trim() === '') {
                return false;
            }

            // Regular expression pattern for vehicle number validation
            var regex = /^[A-HJ-NPR-Z0-9]{17}$/i;

            // Test the vehicle number against the regex pattern
            var isValid = regex.test(Number);

            return isValid;
        }
</script>

@endsection