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
	/* input[type=text]{
		text-transform:uppercase;
	} */
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
            <input type="text" id="dlNoInput" name="dlNoInput" placeholder="driving License No." class="form-control capitalized-text text-uppercase" required>
        </div>
        <div class="col-lg-3">
            <input type="text" id="dob" name="dob" placeholder="Date of birth"  required class="form-control" autocomplete="off" required>
        </div>
        <div class="col-lg-auto">
            <button id="submitBtn" class="btn btn-primary">Submit</button>
        </div>
        <div class="col-lg-auto error-message">
            
        </div>
    </div>
	
    
    <div class="searched-details " style="display:none;">
		<div class="d-flex  align-items-center mb-2">
            <h2 class="card-title me-auto" style="margin-top: 15px;"></h2>
			<a href="#" class="downloadPDF ms-auto pb-2" id="downloadPDF" data-content="" style="color:#a50101; border:1px solid; border-radius:20px; padding:5px 15px;"><i class="bi bi-file-pdf-fill"></i> Download </a>
            {{-- <a href="#" id="downloadPDF" data-content="" style="display:none;color:#a50101; border:1px solid; border-radius:20px; padding:5px 15px;"><i class="bi bi-file-pdf-fill"></i> Download </a> --}}
		</div>
        <div class="accordion" id="accordionExample">
            <div class="row">
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
    $(document).ready(function() { 

        var today = new Date(); // Get today's date
        $('#dob').datepicker(
            {  
                dateFormat: 'dd-mm-yy',
                maxDate: today,
                changeMonth: true,
                changeYear: true,
                yearRange: '1950:2050' // Set the year range
            }
        );


        function validateNumber(LicenseNo) {
            if (LicenseNo.trim() === '') {
                return false;
            }
            var regex = /^[A-Za-z]{2}\d{2}\s?\d{11}$/;
            var isValid = regex.test(LicenseNo);
            return isValid;
        }

        function validateDOB(date){
            if (date.trim() === '') {
                return false;
            }
            var dateRegex = /^\d{2}-\d{2}-\d{4}$/;
            return isValid = dateRegex.test(date);
        }


        $('#submitBtn').click(function() {

            event.preventDefault(); // Prevent form submission
            var dl = $('#dlNoInput').val().toUpperCase().trim();
            var dob = $('#dob').val();
            var isValidDL = validateNumber(dl);
            var isValidDOB = validateDOB(dob);
            if(isValidDL === false)
            {
                $('#validate').html('<p> Please enter valid driving lincese</p>');
                return false;
            }
            else if(isValidDOB === false)
            {
                $('#validate').html('<p> Please enter valid DOB</p>');
                return false;
            }
            else
            {   
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
                    url: "{{ route('rc.rcdlauth') }}", // Path to controller through routes/web.php
                    type: 'POST',
                    data: { dl:dl,  dob:dob },
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


            // if(isValidVehicleNumber === true && isValidVehicleNumber != '')
            // {
            //     // Show loader
            //     var loader = document.getElementById('loader');
            //     loader.style.display = 'block';
            //     $(".re_details").css('display','none');

            //     $('#validate').html();

            //      // Get the CSRF token value from the meta tag
            //     var csrfToken = $('meta[name="csrf-token"]').attr('content');

            //         // Add the CSRF token to the AJAX request headers
            //     $.ajaxSetup({
            //     headers: {
            //         'X-CSRF-TOKEN': csrfToken
            //     }
            //     });
            //     // Make the Ajax request
            //     $.ajax({
            //         url: "{{ route('rc.rcPostData') }}", // Path to controller through routes/web.php
            //         type: 'POST',
            //         data: { vehicleNo: vehicleNo },
            //         dataType: 'json',
            //         success: function(response) {
            //            // console.log(response);
            //             loader.style.display = 'none';
            //             if(response != null && response != '')
            //             {
            //                 var api_log_id = response.api_log_id;
			// 				var statusCode = response.statusCode;
            //                 var response_status  = response.response.status;
            //                 var response_message = response.response_message;
            //                 var vendor = response.vendor;
            //                 var response_msg = response.response.msg;
            //                 var responseJson = response.response;
            //                //console.log(responseJson.puccNumber);

                           
            //                 if(statusCode == 200 || statusCode == 1)
            //                 {   
            //                     $(".re_details").css('display', 'block');
            //                     $(".no-data").hide();
            //                     $(".searched-details").css('display', 'block');
            //                     // console.log(responseJson.msg["Owners Details"]["Father Name/Husband Name"]); 
            //                     if(vendor == 'authbridge'){
            //                         displayDetailsauth(responseJson.msg);
            //                     }
            //                     else if(vendor == 'signzy'){
            //                         displayDetails(responseJson.result);
            //                     }
			// 					$("#downloadPDF").attr('data-content', api_log_id);
            //                 }else if(response_message != null)
            //                 {
            //                     $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p>'+response_message+'.</p>');
            //                     $(".searched-details").css('display', 'none');
            //                     $('.error-message').html('');
            //                     $(".re_details").css('display', 'none');
            //                     $(".no-data").show();
            //                 }
            //                 else if(response_msg != null ){
            //                     $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p>'+response_msg+'.</p>');
            //                     $(".searched-details").css('display', 'none');
            //                     $('.error-message').html('');
            //                     $(".re_details").css('display', 'none');
            //                     $(".no-data").show();
            //                 }
            //             }
            //             else{
            //                 $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p> No Data Found!.</p>');
            //                 $(".searched-details").css('display', 'none');
            //                 $('.error-message').html('');
            //                 $(".re_details").css('display', 'none');
            //                 $(".no-data").show();
            //             }                        
                        
            //         },
            //         error: function(xhr, status, error) {
            //             console.log('AJAX Error:', error);
            //             loader.style.display = 'none';
            //         }
            //     });
            // }
            // else{
            //     $(".re_details").css('display', 'block');
            //     $(".searched-details").css('display', 'none');
            //      $('.error-message').html('<p> Please enter valid vehicle no </p>');
            // }
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
</script>

@endsection