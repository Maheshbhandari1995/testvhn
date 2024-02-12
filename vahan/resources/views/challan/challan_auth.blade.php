@extends('layout')

@section('content')
<style>       
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
    .challan_details{
        display:none;
    }
    .re_details_error{
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
</style>
<!-- Main container -->
<div id="main" class="main">
    <div class="row">
        <div class="pagetitle">
            <h1>Challan Details</h1>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-3">
            <input type="text" id="challanNo" placeholder="" class="form-control text-uppercase" required>
            <label class="form-element-label" for="challanNo">Vehicle Number</label>
        </div>
        <div class="col-lg-auto">
            <button id="submitBtn" class="btn btn-primary">Submit</button>
        </div>
        <div class="col-lg-auto error-message"  id="validate">
        </div>
    </div>

    <div class="container-fluid">
        <div class="row no-data">
            <div class="col-lg-6">
                <div class="no-data-content">
                    <div>
                        <h4>No Data Found</h4>
                        <p id="noDataFound">Searched vehicle detail will be displayed here. <br> To search enter vehicle number</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <img src="assets/img/searching-data.png" alt="searching-data">
            </div>
        </div>
    </div>

    <!-- //Filters -->
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
    
    <div class="challan_details">
        <h2 class="card-title">Challan Number</h2>
        <div id="challanDetails"></div>
    </div>
    <div class="re_details_error">
        <div id="error"></div>
    </div>
</div>

<!-- //Main container -->
<script>
    $(document).ready(function() {
        
        $('#submitBtn').click(function() {
            var vehicle_No = $('#challanNo').val().toUpperCase().trim();
            var isValidChallanNumber = validateVehicleNumber(vehicle_No);
            //console.log(isValidChallanNumber);
            if(isValidChallanNumber !== false)
            {
                // Show loader
                var loader = document.getElementById('loader');
                $(".challan_details").css('display','none');
                //$("#validate").css('display','none');
                //$(".re_details_error").css('display','none');
                loader.style.display = 'block';
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
                    url: "{{ route('challan.challanAuthPostData') }}",// Path to controller through routes/web.php
                    type: 'POST',
                    data: { vehicleNo: vehicle_No },
                    dataType: 'json',
                    success: function(response) {
                        // Hide loader
                        loader.style.display = 'none';
                        if (response.code == 200) {
                            $(".challan_details").css('display', 'block');
                            $('#validate').html('');
                            displayVehicleDetails(response.message.result.challanDetails);
                        }
                        else{
                            $('#validate').html('');
                            $('#noDataFound').html(response.message).css("color", "red");
                            $('.re_details_error').css('display', 'block');
                        }
                    },
                    error: function(xhr, status, error) {
                        loader.style.display = 'none';
                        $('#noDataFound').html('An error occurred: ' + error).css("color", "red");
                        $('.re_details_error').css('display', 'block');
                    }
                });
            }
            else{
                $('#validate').html('<p> Please enter valid challan no </p>');
            }
        });




        function displayVehicleDetails(vehicleDetails) 
        {
            $('#challanDetails').html('');
            var vehicleDetailsHtml = '<div>';
            $.each(vehicleDetails, function(i, data) {
                vehicleDetailsHtml += '<div><table>';
                vehicleDetailsHtml += '<tr><th colspan="2"></th></tr>';
                $.each(data, function(index, detail) {
                    console.log(data);
                    var fieldName = index;
                    var fieldValue = detail;
                    vehicleDetailsHtml += '<tr><td width="30%">' + fieldName + '</td><td>' + fieldValue + '</td></tr>';
                });
                vehicleDetailsHtml += '</table></div>';
            });
            vehicleDetailsHtml += '</div>';
            //console.log(vehicleDetailsHtml);
            $('#challanDetails').html(vehicleDetailsHtml);
        }

    });
</script>

@endsection