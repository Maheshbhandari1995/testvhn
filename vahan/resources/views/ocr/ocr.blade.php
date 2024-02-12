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
    .re_details{
        
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
	
	
	.upload-btn {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }
    /* Style for file input */
    input[type="file"] {
        display: none;
    }
    /* Style for image preview */
    .image-preview {
        display: inline-block;
        margin-top: 10px;
        width: 200px;
        height: auto;
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
        <form id="upload-form" enctype="multipart/form-data">
			<input type="file" id="front-image" accept="image/*" onchange="previewImage('front-image', 'front-preview')">
			<img id="front-preview" class="image-preview" src="#" alt="Front Image Preview">
			<br>
			<input type="file" id="back-image" accept="image/*" onchange="previewImage('back-image', 'back-preview')">
			<img id="back-preview" class="image-preview" src="#" alt="Back Image Preview">
			<br>
			<button type="button" class="upload-btn" onclick="uploadImages()">Upload Images</button>
		</form>
    </div>
	
    
    <div class="searched-details " style="display:none;">
		<div class="d-flex  align-items-center mb-2">
            <h2 class="card-title me-auto" style="margin-top: 15px;"></h2>
			<a href="#" class="downloadPDF ms-auto pb-2" id="downloadPDF" data-content="" style="color:#a50101; border:1px solid; border-radius:20px; padding:5px 15px;"><i class="bi bi-file-pdf-fill"></i> Download </a>
            {{-- <a href="#" id="downloadPDF" data-content="" style="display:none;color:#a50101; border:1px solid; border-radius:20px; padding:5px 15px;"><i class="bi bi-file-pdf-fill"></i> Download </a> --}}
		</div>
        <div class="accordion" id="accordionExample">

{{-- 

            <div class="row">
                <div class="col-md-8">
                    <div class="table-heading">
                        <h3>Vehicle Details</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-striped dataTable no-footer nodata-table">
                            <tbody>
                                <tr>
                                    <th width="30%">Reg No</th>
                                    <td>MH47U0571</td>
                                    <th>Class</th>
                                    <td>M-Cycle/Scooter</td>
                                </tr>
                                <tr>
                                    <th>Chassis</th>
                                    <td>ME4JF509BHU186130</td>
                                    <th>Engine</th>
                                    <td>JF50EU6186185</td>
                                </tr>
                             
                                <tr>
                                    <th>Vehicle Manufacturer Name</th>
                                    <td>HONDA CARS INDIA LTD</td>
                                    <th>Vehicle Number</th>
                                    <td>MH47U0571</td>
                                </tr>
                                
                                <tr>
                                    <th>Status As On</th>
                                    <td>04/08/2023</td>
                                    <th>Type</th>
                                    <td>PETROL</td>
                                </tr>
                             
                                <tr>
                                    <th>Unladen Weight</th>
                                    <td>112</td>
                                    <th>Vehicle Category</th>
                                    <td>2WN</td>
                                </tr>
                               
                                <tr>
                                    <th>Vehicle Colour</th>
                                    <td>T BLUE-M</td>
                                    <th>Vehicle Cubic Capacity</th>
                                    <td>109</td>
                                </tr>
                            
                                <tr>
                                    <th>Vehicle Cylinders No</th>
                                    <td>1</td>
                                    <th>Vehicle Insurance Company Name</th>
                                    <td>ACKO GENERAL INSURANCE LIMITED</td>
                                </tr>
                           
                                <tr>
                                    <th>Vehicle Insurance Policy Number</th>
                                    <td>DBTR00404995486/00</td>
                                    <th>Vehicle Insurance Upto</th>
                                    <td>01/07/2024</td>
                                </tr>
                              
                                <tr>
                                    <th>Vehicle Manufacturing Month/Year</th>
                                    <td>02/2017</td>
                                    <th>Vehicle Seat Capacity</th>
                                    <td>2</td>
                                </tr>
                             
                                <tr>
                                    <th>Vehicle Sleeper Capacity</th>
                                    <td></td>
                                    <th>Vehicle Standing Capacity</th>
                                    <td></td>
                                </tr>
                            
                                <tr>
                                    <th>Vehicle Tax Upto</th>
                                    <td>LTT</td>
                                    <th>Wheelbase</th>
                                    <td></td>
                                </tr>
                            
                                <tr>
                                    <th>RC Expiry Date</th>
                                    <td>17/03/2032</td>
                                    <th>RC Financer</th>
                                    <td></td>
                                </tr>
                             
                                <tr>
                                    <th>RC Standard Cap</th>
                                    <td></td>
                                    <th>Reg Authority</th>
                                    <td>DY.R.T.O.BORIVALI</td>
                                </tr>
                         
                                <tr>
                                    <th>Reg Date</th>
                                    <td>18/03/2017</td>
                                    <th>Reg Date</th>
                                    <td>18/03/2017</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div> 

                <div class="col-md-4">
                    <div class="table-heading">
                        <h3>Personal Details</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-striped dataTable no-footer nodata-table">
                            <tbody>
                                <tr>
                                    <th width="30%">Owner</th>
                                    <td>KRUNAL D WANKHEDE</td>
                                </tr>
                                <tr>
                                    <th>Owner Father Name</th>
                                    <td>.</td>
                                </tr>
                                <tr>
                                    <th>Mobile Number</th>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>NA</td>
                                </tr>
                                <tr>
                                    <th>Status As On</th>
                                    <td>04/08/2023</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-heading">
                        <h3>Address Details</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless table-striped dataTable no-footer nodata-table">
                            <tbody>
                                <tr>
                                    <th width="30%">Address Line</th>
                                    <td>RM NO 5 SAI PRASAD CHAWL,DAMU NAGAR AKURLI ROAD,KANDIVALI EAST</td>
                                </tr>
                                <tr>
                                    <th>City</th>
                                    <td>MUMBAI</td>
                                </tr>
                                <tr>
                                    <th>Pincode</th>
                                    <td>400101</td>
                                </tr>
                                <tr>
                                    <th>District</th>
                                    <td>MUMBAI</td>
                                </tr>
                                <tr>
                                    <th>State</th>
                                    <td>MAHARASHTRA</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>


                </div>
            </div>  --}}
            <div class="row">
                <!-- <div class="col-md-6">
                        <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Vehicle Details
                        </button>
                        </h2>

                        <div id="collapseOne" >
                            <div class="accordion-body">
                                <div class="table-heading">
                                    <h3>Vehicle Details</h3>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-borderless table-striped dataTable no-footer nodata-table">
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bg-colored">
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div> -->



                <!-- <div class="col-md-6">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Personal Details
                        </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
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
                        <div id="collapseThree" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
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
                </div> -->




            </div>

        </div>
    </div>
    
    {{-- <div class="re_details searched-details"></div> --}}
    
    <div class="row no-data g-3">
        <div class="col-lg-12">
            <div>
                <div class="no-data-content" id="noDataFound">
                    <h4 >No Data Found</h4>
                    <p>Searched vehicle detail will be displayed here. <br> To search enter vehicle number</p>
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
function previewImage(inputId, previewId) {
    var input = document.getElementById(inputId);
    var preview = document.getElementById(previewId);

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
        };

        reader.readAsDataURL(input.files[0]);
    }
}

function uploadImages() {
    var frontImage = document.getElementById('front-image').files[0];
    var backImage = document.getElementById('back-image').files[0];

    if (!frontImage || !backImage) {
        alert("Please select both front and back images.");
        return;
    }

    var formData = new FormData();
    formData.append('front_image', frontImage);
    formData.append('back_image', backImage);
    formData.append('tsTransID', 'TS-PFC-113103');
    formData.append('secretToken', '0eFoVJ84y9M4BTih8U2PJA==:PSkcIMauf0kednh4YXr1nA==');

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

    $.ajax({
        url: 'https://www.truthscreen.com/api/v2.2/idocr/verify',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('username', 'test@edas.tech');
            xhr.setRequestHeader('Cookie', 'CAKEPHP=5ga3k1dbfrj8076pe6kdevf772');
        },
        success: function(response) {
            alert('Images uploaded successfully.');
            // Handle response if needed
        },
        error: function() {
            alert('Error uploading images. Please try again later.');
            // Handle error if needed
        }
    });
}

/* 
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
                    url: "{{ route('rc.rcPostData') }}", // Path to controller through routes/web.php
                    type: 'POST',
                    data: { vehicleNo: vehicleNo },
                    dataType: 'json',
                    success: function(response) {
                       // console.log(response);
                        loader.style.display = 'none';
                        if(response != null && response != '')
                        {
                            var api_log_id = response.api_log_id;
							var statusCode = response.statusCode;
                            var response_status  = response.response.status;
                            var response_message = response.response_message;
                            var vendor = response.vendor;
                            var response_msg = response.response.msg;
                            var responseJson = response.response;
                           //console.log(responseJson.puccNumber);

                           
                            if(statusCode == 200 || statusCode == 1)
                            {   
                                $(".re_details").css('display', 'block');
                                $(".no-data").hide();
                                $(".searched-details").css('display', 'block');
                                // console.log(responseJson.msg["Owners Details"]["Father Name/Husband Name"]); 
                                if(vendor == 'authbridge'){
                                    displayDetailsauth(responseJson.msg);
                                }
                                else if(vendor == 'signzy'){
                                    displayDetails(responseJson.result);
                                }
								$("#downloadPDF").attr('data-content', api_log_id);
                            }else if(response_message != null)
                            {
                                $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p>'+response_message+'.</p>');
                                $(".searched-details").css('display', 'none');
                                $('.error-message').html('');
                                $(".re_details").css('display', 'none');
                                $(".no-data").show();
                            }
                            else if(response_msg != null ){
                                $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p>'+response_msg+'.</p>');
                                $(".searched-details").css('display', 'none');
                                $('.error-message').html('');
                                $(".re_details").css('display', 'none');
                                $(".no-data").show();
                            }
                        }
                        else{
                            $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p> No Data Found!.</p>');
                            $(".searched-details").css('display', 'none');
                            $('.error-message').html('');
                            $(".re_details").css('display', 'none');
                            $(".no-data").show();
                        }                        
                        
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', error);
                        loader.style.display = 'none';
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

            var vehicleDetailsHtml = '<div class="row"><div class="col-md-8"><div class="table-heading"><h3>Vehicle Details</h3></div>';

            vehicleDetailsHtml += '<div class="table-responsive"><table class="table table-borderless table-striped dataTable no-footer nodata-table"><tbody>';

            vehicleDetailsHtml += '<tr> <th width="30%">Reg.No.</th><td>' + response.regNo + '</td> <th>Class</th>  <td>' + response.class + '</td></tr>';

            vehicleDetailsHtml += ' <tr> <th>Chassis</th><td>' + response.chassis + '</td> <th>Engine No.</th><td>' + response.engine + '</td>  </tr>';
                                    
            vehicleDetailsHtml += ' <tr> <th>Vehicle Manufacturer Name</th><td>' + response.vehicleManufacturerName + '</td><th>Vehicle Number</th> <td>' + response.vehicleNumber + '</td> </tr>';
                                        
            vehicleDetailsHtml += ' <tr> <th>Status As On</th>  <td>' + response.statusAsOn + '</td> <th>Type</th>   <td>' + response.type + '</td>  </tr>';
                                    
            vehicleDetailsHtml += '<tr> <th>Unladen Weight</th> <td>' + response.unladenWeight + '</td> <th>Vehicle Category</th>  <td>' + response.vehicleCategory + '</td> </tr>';
                                    
            vehicleDetailsHtml += '<tr> <th>Vehicle Colour</th>  <td>' + response.vehicleColour + '</td>  <th>Vehicle Cubic Capacity</th>  <td>' + response.vehicleCubicCapacity + '</td>   </tr>';
                                    
            vehicleDetailsHtml += ' <tr>  <th>Vehicle Cylinders No.</th>   <td>' + response.vehicleCylindersNo + '</td>  <th>Vehicle Insurance Company Name</th> <td>' + response.vehicleInsuranceCompanyName + '</td>   </tr>';
                                
            vehicleDetailsHtml += ' <tr> <th>Vehicle Insurance Policy Number</th><td>' + response.vehicleInsurancePolicyNumber + '</td>  <th>Vehicle Insurance Upto</th> <td>' + response.vehicleInsuranceUpto + '</td></tr>';
                                    
            vehicleDetailsHtml += ' <tr>  <th>Vehicle Manufacturing Month/Year</th>   <td>' + response.vehicleManufacturingMonthYear + '</td>  <th>Vehicle Seat Capacity</th>   <td>' + response.vehicleSeatCapacity + '</td> </tr>';
                                    
            vehicleDetailsHtml += ' <tr> <th>Vehicle Sleeper Capacity</th> <td>' + response.vehicleSleeperCapacity + '</td>    <th>Vehicle Standing Capacity</th>    <td>' + response.vehicleStandingCapacity + '</td>     </tr>';
                                
            vehicleDetailsHtml += '  <tr> <th>Vehicle Tax Upto</th> <td>' + response.vehicleTaxUpto + '</td>   <th>Wheelbase</th>   <td>' + response.wheelbase + '</td>  </tr>';
                                
            vehicleDetailsHtml += ' <tr>  <th>RC Expiry Date</th>  <td>' + response.rcExpiryDate + '</td>   <th>RC Financer</th>   <td>' + response.rcFinancer + '</td>   </tr>';
                                    
            vehicleDetailsHtml += ' <tr> <th>RC Standard Cap</th> <td>' + response.rcStandardCap + '</td> <th>Reg Authority</th> <td>' + response.regAuthority + '</td>  </tr>';

            vehicleDetailsHtml += ' <tr> <th>Reg. Date</th> <td>' + response.regDate + '</td> <th>Gross Vehicle Weight</th> <td>' + response.grossVehicleWeight + '</td>  </tr>';

            vehicleDetailsHtml += ' <tr> <th>Pucc Number</th> <td>' + response.puccNumber + '</td> <th>Pucc Upto</th> <td>' + response.puccUpto + '</td>  </tr>';

            vehicleDetailsHtml += ' <tr> <th>Blacklist Status</th> <td>' + response.blacklistStatus + '</td> <th>Permit Issue Date</th> <td>' + response.permitIssueDate + '</td>  </tr>';

            vehicleDetailsHtml += ' <tr> <th>Permit Number</th> <td>' + response.permitNumber + '</td> <th>Permit Type</th> <td>' + response.permitType + '</td>  </tr>';
            
            vehicleDetailsHtml += ' <tr> <th>Permit Valid From</th> <td>' + response.permitValidFrom + '</td> <th>Permit Valid Upto</th> <td>' + response.permitValidUpto + '</td>  </tr>';

            
            vehicleDetailsHtml += ' <tr> <th>Non Use Status</th> <td>' + response.nonUseStatus + '</td> <th>Non Use From</th> <td>' + response.nonUseFrom + '</td>  </tr>';

            
            vehicleDetailsHtml += ' <tr> <th>Non Use To</th> <td>' + response.nonUseTo + '</td> <th>National Permit Number</th> <td>' + response.nationalPermitNumber + '</td>  </tr>';

            
            vehicleDetailsHtml += ' <tr> <th>National Permit Upto</th> <td>' + response.nationalPermitUpto + '</td> <th>National Permit Issued By</th> <td>' + response.nationalPermitIssuedBy + '</td>  </tr>';

            vehicleDetailsHtml += ' <tr> <th>Commercial Status</th> <td>' + response.isCommercial + '</td> <th>Noc Details</th> <td>' + response.nocDetails + '</td>  </tr>';
			vehicleDetailsHtml += ' <tr> <th>Model / Makers Class</th> <td>' + response.model + '</td> <th></th> <td></td>  </tr>';


            vehicleDetailsHtml += '  </tbody> </table>  </div>  </div> ';

            vehicleDetailsHtml += '  <div class="col-md-4"><div class="table-heading">     <h3>Personal Details</h3>   </div>';
            vehicleDetailsHtml += '   <div class="table-responsive"> <table class="table table-borderless table-striped dataTable no-footer nodata-table">  <tbody>';

            vehicleDetailsHtml += '   <tr>  <th width="30%">Owner</th>    <td>' + response.owner + '</td>    </tr>   <tr>   <th>Owner Father Name</th> <td>' + response.ownerFatherName + '</td>    </tr>   <tr> <th>Mobile Number</th> <td>' + response.mobileNumber + '</td> </tr> <tr>    <th>Status</th>  <td>' + response.status + '</td> </tr> </tbody>  </table> </div>';

            vehicleDetailsHtml += ' <div class="table-heading">  <h3>Address Details</h3> </div>';
            vehicleDetailsHtml += '  <div class="table-responsive"> <table class="table table-borderless table-striped dataTable no-footer nodata-table">  <tbody>';
                
            vehicleDetailsHtml += ' <tr> <th width="30%">Address Line</th>  <td>' + response.splitPermanentAddress.addressLine + '</td>  </tr>  <tr>  <th>City</th> <td>' + response.splitPermanentAddress.city[0] + '</td>  </tr>  <tr>  <th>Pincode</th> <td>' + response.splitPermanentAddress.pincode + '</td> </tr>  <tr> <th>District</th>  <td>' + response.splitPermanentAddress.district[0] + '</td>  </tr> <tr> <th>State</th> <td>' + response.splitPermanentAddress.state[0][0] + '</td> </tr>  </tbody>  </table>  </div>  </div> </div> ';


            $('#accordionExample').html(vehicleDetailsHtml);

            $('.searched-details').css('display', 'block');
            $('.error-message').html('');
        }

        function displayDetailsauth(response){

            var vehicleDetailsHtml = '<div class="row"><div class="col-md-8"><div class="table-heading"><h3>Vehicle Details</h3></div>';
            vehicleDetailsHtml += '<div class="table-responsive"><table class="table table-borderless table-striped dataTable no-footer nodata-table"><tbody>';
            vehicleDetailsHtml += ' <tr><th width="30%">Reg. No.</th><td>' + response["Registration Details"]["Registration Number"] + '</td><th>Class</th><td>' + response["Vehicle Details"]["Vehicle Class"] + '</td></tr>';
            vehicleDetailsHtml += '<tr> <th>Chassis No.</th><td>' + response["Vehicle Details"]["Chassis Number"] + '</td><th>Engine Capacity</th><td>' + response["Vehicle Details"]["Engine Capacity"] + '</td> </tr>';
            vehicleDetailsHtml += ' <tr> <th>Vehicle Manufacturer Name</th><td>' + response["Vehicle Details"]["Maker/Manufacturer"] + '</td><th>Vehicle No.</th> <td>'+ response["Vehicle Details"]["Vehicle Number"] +'</td></tr>'; 
            vehicleDetailsHtml += '  <tr><th>Status As On</th> <td>' + response["Vehicle Details"]["Status As On"] + '</td><th>Type</th><td>' + response["Vehicle Details"]["Body Type"] + '</td></tr>';                           
            vehicleDetailsHtml += '<tr> <th>Unladen Weight</th><td>' + response["Vehicle Details"]["Unloading Weight"] + '</td><th>Vehicle Category</th><td>' + response["Vehicle Details"]["Vehicle Category"] + '</td> </tr>';

            vehicleDetailsHtml += ' <tr><th>Vehicle Colour</th><td>' + response["Vehicle Details"]["Color"] + '</td><th>Vehicle Cylinders No.</th><td>' + response["Vehicle Details"]["No of cylinder"] + '</td></tr>';

            vehicleDetailsHtml += '  <tr> <th>Vehicle Insurance Company Name</th>  <td>' + response["Insurance Details"]["Insurance Company"] + '</td> <th>Vehicle Insurance Policy Number</th><td>' + response["Insurance Details"]["Policy Number"] + '</td> </tr>';

            vehicleDetailsHtml += '   <tr> <th>Vehicle Insurance Upto</th> <td>' + response["Insurance Details"]["Insurance To Date/Insurance Upto"] + '</td> <th>Vehicle Manufacturing Month/Year</th>    <td>' + response["Vehicle Details"]["Manufacture Date"] + '</td>  </tr>'; 
            vehicleDetailsHtml += ' <tr> <th>Vehicle Seat Capacity</th> <td>' + response["Vehicle Details"]["Seating Capacity"] + '</td>    <th>Vehicle Sleeper Capacity</th>  <td>' + response["Vehicle Details"]["sleeper Capacity"] + '</td> </tr>';

            vehicleDetailsHtml += ' <tr> <th>Vehicle Standing Capacity</th> <td>' + response["Vehicle Details"]["Vehicle Standing Capacity"] + '</td> <th>Vehicle Tax Upto</th>  <td>' + response["Vehicle Details"]["Tax Upto"] + '</td> </tr>';

            vehicleDetailsHtml += '  <tr><th>Wheelbase</th>  <td>' + response["Registration Details"]["RTO"] + '</td><th>RC Expiry Date</th>  <td>' + response["Registration Details"]["Fitness Date/RC Expiry Date"] + '</td>  </tr>';

            vehicleDetailsHtml += ' <tr>  <th>RC Financer</th>  <td>' + response["Hypothecation Details"]["Financed"] + '</td>  <th>PUCC No.</th> <td>' + response["RC Status"]["PUCC NO"] + '</td> </tr>';

            vehicleDetailsHtml += '  <tr>  <th>PUCC Upto</th>  <td>' + response["RC Status"]["PUCC Upto"] + '</td>    <th>Norms Type</th><td>' + response["Vehicle Details"]["Norms Type"] + '</td>   </tr>';

            vehicleDetailsHtml += '   <tr> <th>Reg. Date</th><td>' + response["Registration Details"]["Registration Date"] + '</td> <th>Blacklist Status</th>  <td>' + response["Vehicle Details"]["Blacklist Status"] + '</td> </tr>';

            vehicleDetailsHtml += '   <tr> <th>Permit Valid Upto</th><td>' + response["RC Status"]["Permit Valid Upto"] + '</td> <th>Fuel Type</th>  <td>' + response["Vehicle Details"]["Fuel Type"] + '</td> </tr>';

            vehicleDetailsHtml += '   <tr> <th>Gross Weight</th><td>' + response["Vehicle Details"]["Gross Weight"] + '</td> <th>Commercial Status</th>  <td>' + response["Vehicle Details"]["Is Commercial"] + '</td> </tr>';

            vehicleDetailsHtml += '   <tr> <th>Noc Details</th><td>' + response["Vehicle Details"]["Noc Details"] + '</td> <th>Owner Serial Number</th>  <td>' + response["Vehicle Details"]["Owner Serial Number"] + '</td> </tr>';

            vehicleDetailsHtml += '   <tr> <th>National Permit Issued By</th><td>' + response["RC Status"]["National Permit Issued By"] + '</td> <th>National Permit Number</th>  <td>' + response["RC Status"]["National Permit Number"] + '</td> </tr>';

            vehicleDetailsHtml += '   <tr> <th>National Permit Upto</th><td>' + response["RC Status"]["National Permit Upto"] + '</td> <th>Non Use From</th>  <td>' + response["RC Status"]["Non Use From"] + '</td> </tr>';

            vehicleDetailsHtml += '   <tr> <th>Non Use Status</th><td>' + response["RC Status"]["Non Use Status"] + '</td> <th>Non Use To</th>  <td>' + response["RC Status"]["Non Use To"] + '</td> </tr>';

            vehicleDetailsHtml += '   <tr> <th>Permit Issue Date</th><td>' + response["RC Status"]["Permit Issue Date"] + '</td> <th>Permit Number</th>  <td>' + response["RC Status"]["Permit Number"] + '</td> </tr>';

            vehicleDetailsHtml += '   <tr> <th>Permit Type</th><td>' + response["RC Status"]["Permit Type"] + '</td> <th>Permit Valid From</th>  <td>' + response["RC Status"]["Permit Vald From"] + '</td> </tr>';
			vehicleDetailsHtml += '   <tr> <th>Model / Makers Class</th><td>' + response["Vehicle Details"]["Model / Makers Class"] + '</td> <th></th>  <td></td> </tr>';
            // vehicleDetailsHtml += '   <tr> <th>Financer Name</th><td>' + response["Hypothecation Details"]["Financer Name"] + '</td></tr></tbody> </table> </div></div> ';
            vehicleDetailsHtml += '   <tr> <th>Financer Name</th><td>' + response["Hypothecation Details"]["Financer Name"] + '</td> <th>Engine Number</th>  <td>' + response["Vehicle Details"]["Engine Number"] + '</td> </tr></tbody> </table> </div></div>';

            // vehicleDetailsHtml += '</tbody> </table> </div></div> ';

            vehicleDetailsHtml += ' <div class="col-md-4"> ';
            vehicleDetailsHtml += '  <div class="table-heading"> <h3>Personal Details</h3> </div>';
            vehicleDetailsHtml += ' <div class="table-responsive"><table class="table table-borderless table-striped dataTable no-footer nodata-table"> <tbody>';
            vehicleDetailsHtml += '  <tr><th width="30%">Owner</th>  <td>' + response["Owners Details"]["Owners Name"] + '</td></tr> <tr>   <th>Owner Father Name</th> <td>' + response["Owners Details"]["Father Name/Husband Name"] + '</td></tr>  <tr> <th>Owners Number</th> <td>' + response["Owners Details"]["Owners Number"] + '</td> </tr>  <tr>  <th>Owner Serial Number</th> <td>' + response["Vehicle Details"]["Owner Serial Number"] + '</td> </tr>';
            vehicleDetailsHtml += '</tbody></table></div>';
            vehicleDetailsHtml += ' <div class="table-heading"> <h3>Address Details</h3> </div>';
            vehicleDetailsHtml += '<div class="table-responsive"><table class="table table-borderless table-striped dataTable no-footer nodata-table"><tbody>';  
            vehicleDetailsHtml += ' <tr> <th width="30%">Address Line</th> <td>' + response["Owners Details"]["Permanent Address"] + '</td></tr> <tr> <th>Present address</th> <td>' + response["Owners Details"]["Present Address"] + '</td> </tr> <tr>  <th>City</th> <td>' + response["Owners Details"]["Permanant Address City"] + '</td>  </tr><tr> <th>Pincode</th> <td>' + response["Owners Details"]["Permanant Address Pincode"] + '</td>  </tr> <tr> <th>District</th><td>' + response["Owners Details"]["Permanant Address District"] + '</td> </tr>   <tr> <th>District</th> <td>' + response["Owners Details"]["Permanant Address District"] + '</td></tr><tr>  <th>State</th><td>' + response["Owners Details"]["Permanant Address State"]+ '</td> </tr>';
            vehicleDetailsHtml += '  </tbody></table></div></div></div> ';


            $('#accordionExample').html(vehicleDetailsHtml);

            $('.searched-details').css('display', 'block');
            $('.error-message').html('');
        }

	$('#downloadPDF').click(function(){
			var id = $(this).attr('data-content');
			console.log(id);
			// alert(id);
			$.ajax({
				url: "{{ route('rc.downloadPDF') }}", // Path to controller through routes/web.php
				type: 'POST',
				data: { id: id },
				dataType: 'json',
				success: function(response) {
				   // console.log(response);
					loader.style.display = 'none';
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
				error: function(xhr, status, error) {
					console.log('AJAX Error:', error);
                    loader.style.display = 'none';
				}
			});
	});

});

 */
</script>

@endsection