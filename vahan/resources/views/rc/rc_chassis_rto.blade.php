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
            <input type="text" id="chassisNoInput" placeholder="" class="form-control capitalized-text text-uppercase" required>
            <label class="form-element-label" for="chassisNoInput">Chassis Number</label>
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
            {{-- <div class="accordion-item">
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
            </div> --}}
        </div>
    </div>
    
    {{-- <div class="re_details searched-details"></div> --}}

    <div class="row no-data g-3">
        <div class="col-lg-12">
            <div>
                <div class="no-data-content">
                    <h4 >No Data Found</h4>
                    <p id="noDataFound">Searched vehicle detail will be displayed here. <br> To search enter vehicle number</p>
                </div>
                <img src="assets/img/error-image.svg" alt="searching-data">
            </div>
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
            var chassisNo = $('#chassisNoInput').val().toUpperCase().trim();
            var isValidVehicleNumber = validateChassisNumber(chassisNo);
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
                    url: "{{ route('rc.rcChassisRTOPostdata') }}", // Path to controller through routes/web.php
                    ///required engine number 
                    type: 'POST',
                    data: { chassisNo: chassisNo },
                    dataType: 'json',
                    success: function(response) {
                       // console.log(response.status);
                        // Hide loader
                        loader.style.display = 'none';
                        $(".re_details").css('display', 'block');
                        if(response.code == 200)
                        {   
                            $(".re_details").css('display', 'block');
                            $(".no-data").hide();
                            $(".searched-details").css('display', 'block');
                            displayDetails(response.data.data);
                        }
                        else{
                            $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p>'+response.message+'.</p>');
                            $(".searched-details").css('display', 'none');
                            $('.error-message').html('');
                            $(".re_details").css('display', 'none');
                            $(".no-data").show();
                        }
                        
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
    $.each(response, function(key, data){
        vehicleDetailsHtml += '<tr>';
        vehicleDetailsHtml += '<th>'+data.name+'</th>';
        vehicleDetailsHtml += '<td>' + data.value + '</td>';
        vehicleDetailsHtml += '</tr>';
        
    });
    vehicleDetailsHtml += '</tbody>';
    vehicleDetailsHtml += '</table>';

    $('.searched-details #collapseOne .accordion-body table').html(vehicleDetailsHtml);

    $('.searched-details').css('display', 'block');
    $('.error-message').html('');
}


});
</script>

@endsection