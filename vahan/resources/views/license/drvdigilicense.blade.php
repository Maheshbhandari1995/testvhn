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
            <h1>License Details</h1>
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
		{{-- <div class="d-flex  align-items-center mb-2">
            <h2 class="card-title me-auto" style="margin-top: 15px;"></h2>
			<a href="#" class="downloadPDF ms-auto pb-2" id="downloadPDF" data-content="" style="color:#a50101; border:1px solid; border-radius:20px; padding:5px 15px;"><i class="bi bi-file-pdf-fill"></i> Download </a>
		</div> --}}
        <div class="div-data" id="data">
            <div class="result">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title mt-3">Personal Details</h2>
                        <div id="personalDetails">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <td width="30%" >Driving License Number</td>
                                        <td id="dlNumber">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Owner's Name</td>
                                        <td id="name">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Father's Name</td>
                                        <td id="fatherOrHusbandName">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Date of Birth</td>
                                        <td id="dob_r">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Address</td>
                                        <td id="completeAddress">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">date_of_issue</td>
                                        <td id="date_of_issue">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">badge_issue_date</td>
                                        <td id="badge_issue_date">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">badge_no</td>
                                        <td id="badge_no">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">class_of_vehicle</td>
                                        <td id="class_of_vehicle">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">non_transport to</td>
                                        <td id="non_transportto">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">non_transport from</td>
                                        <td id="non_transportfrom">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">hazardous_valid_till</td>
                                        <td id="hazardous_valid_till">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">transport to</td>
                                        <td id="transportto">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">transport from</td>
                                        <td id="transportfrom">NA</td>
                                    </tr>
                                    <tr>
                                        <td width="30%">hill_valid_till</td>
                                        <td id="hill_valid_till">NA</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
        </div>
        </div>
    </div>
    
    <div class="row no-data g-3">
        <div class="col-lg-12">
            <div>
                <div class="no-data-content" id="noDataFound">
                    <h4 >No Data Found</h4>
                    <p>Searched DL detail will be displayed here. <br> To search enter DL number</p>
                </div>
                <img src="assets/img/error-image.svg" alt="searching-data">
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
    
    
</div>

<!-- //Main container -->
<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script>
    $(document).ready(function() { 

        var today = new Date(); // Get today's date
        $('#dob').datepicker(
            {  
                dateFormat: 'dd/mm/yy',
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
            var dateRegex = /^\d{2}\/\d{2}\/\d{4}$/;
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
                    url: "{{ route('license.licensedrv') }}", // Path to controller through routes/web.php
                    type: 'POST',
                    data: { dl:dl,  dob:dob },
                    dataType: 'json',
                    success: function(response) {
                        loader.style.display = 'none';
                        if(response.result_code == 101){
                            console.log(response); 

                            $(".re_details").css('display', 'block');
                            $(".no-data").hide();
                            $(".searched-details").css('display', 'block');
                            loader.style.display = 'none';
                            $("#dlNumber").text(response.result['dl_number']);
                            $("#name").text(response.result.details_of_driving_licence['name']);
                            $("#fatherOrHusbandName").text(response.result.details_of_driving_licence["father_or_husband_name"]);
                            $("#dob_r").text(response.result['dob']);
                            $("#completeAddress").text(response.result.details_of_driving_licence.address_list[0]['complete_address']);
                            $("#date_of_issue").text(response.result.details_of_driving_licence['date_of_issue']);
                            $("#badge_issue_date").text(response.result.badge_details[0]['badge_issue_date']);
                            $("#badge_no").text(response.result.badge_details[0]['badge_no']);
                            $("#class_of_vehicle").text(response.result.badge_details[0]['class_of_vehicle'][0]);
                            $("#non_transportto").text(response.result.dl_validity.non_transport['to']);
                            $("#non_transportfrom").text(response.result.dl_validity.non_transport['from']);
                            $("#hazardous_valid_till").text(response.result.dl_validity['hazardous_valid_till']);
                            $("#transportto").text(response.result.dl_validity.transport['to']);
                            $("#transportfrom").text(response.result.dl_validity.transport['from']);
                            $("#hill_valid_till").text(response.result['hill_valid_till']);
                            
                        }
                        else if(response.result_code == 103){

                            $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <style="color:red;>No Data Found.</p>');
                             loader.style.display = 'none';
                                $(".searched-details").css('display', 'none');
                                $('.error-message').html('');
                                $(".re_details").css('display', 'none');
                                $(".no-data").show(); 
                        }
                        else{
                            loader.style.display = 'none';
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
        });

        

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