@extends('layout')

@section('content')

   <link  rel="stylesheet" href="{{asset('assets/css/custome-api.css')}}"/>

<!-- Main container -->
<div id="main" class="main">
    <div class="row">
        <div class="pagetitle">
            <h1>RC Details</h1>
        </div>
    </div>

    <div id="validate" style="color:red;"></div>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Vehicle Number</h5>
            <div class="row g-3">
                <div class="col-auto">
                    
                    <input type="text" id="vehicleNoInput" placeholder="Enter Vehicle Number" class="form-control capitalized-text">
                </div>
                <div class="col-auto">

                    <button id="submitBtn" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>
   
    <!-- //Filters -->
    <div id="loader" class="loader" style="display: none;"></div>
    <div class="re_details"></div>
    
</div>

<!-- //Main container -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
    $(document).ready(function() {
        $('#submitBtn').click(function() {
            var vehicleNo = $('#vehicleNoInput').val().toUpperCase().trim();
            var isValidVehicleNumber = validateVehicleNumber(vehicleNo);
            if(isValidVehicleNumber)
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
                    url: "{{ route('vehicles.data') }}", // Path to controller through routes/web.php
                    type: 'POST',
                    data: { vehicleNo: vehicleNo },
                    dataType: 'json',
                    success: function(response) {
                       // console.log(response);
                        // Hide loader
                        loader.style.display = 'none';
                        $(".re_details").css('display', 'block');
                        if(response.status_code == 200)
                        { 
                            displayDetails(response.msg);
                        }
                        else{
                            $('.re_details').html('<div class="card"><div class="card-body"><h2 class="card-title" style="color:red;">Error  </h2> <div> No Record Found. </div></div></div>');
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
                 $('.re_details').html('<p> Please enter valid vehicle no </p>');
            }
        });

       
        function displayDetails(response) {

            var vehicleDetailsHtml = "";
            $.each(response, function(key, vehicleDetails) {
                vehicleDetailsHtml += '<div class="card"><div class="card-body"><h2 class="card-title">'+key+'</h2><div>';
                
                vehicleDetailsHtml += '<table class="table">';
                //vehicleDetailsHtml += '<tr><th>Field</th><th>Value</th></tr>';
                $.each(vehicleDetails, function(index, detail) {
                    var fieldName = index;
                    var fieldValue = detail;
                    vehicleDetailsHtml += '<tr><td width="30%">' + fieldName + '</td><td>' + fieldValue + '</td></tr>';
                });
                vehicleDetailsHtml += '</table> </div></div></div>';
            });
            $('.re_details').html(vehicleDetailsHtml);
        }

        function validateVehicleNumber(vehicleNumber) {
            // Check if the vehicle number is empty
            if (vehicleNumber.trim() === '') {
                return false;
            }

            // Regular expression pattern for vehicle number validation
            var regex = /^[A-Z]{2}[0-9]{1,2}[A-Z]{1,2}[0-9]{1,4}$/;

            // Test the vehicle number against the regex pattern
            var isValid = regex.test(vehicleNumber);

            return isValid;
        }
    });
</script>

@endsection