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
	/* input[type=text]{
		text-transform:uppercase;
	} */
</style>
<!-- Main container -->
<div id="main" class="main">
    <div class="row">
        <div class="pagetitle">
            <h1>Pancard Details</h1>
        </div>
    </div>

    <div id="validate" style="color:red;"></div>
    <div class="row g-3">
        <div class="col-lg-3">
            <input type="text" id="InputNumber" placeholder="" class="form-control capitalized-text text-uppercase" required>
            <label class="form-element-label" for="InputNumber">Pancard Number</label>
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
                                        <td width="30%">Owner's Name</td>
                                        <td id="name"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Date of Birth</td>
                                        <td id="dob_r"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">aadhaar_linked</td>
                                        <td id="aadhaar_linked"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Email address</td>
                                        <td id="email"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">less info</td>
                                        <td id="less_info"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">masked_aadhaar</td>
                                        <td id="masked_aadhaar"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Phone number</td>
                                        <td id="phone_number"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Category</td>
                                        <td id="category"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Gender</td>
                                        <td id="gender"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Address</td>
                                        <td id="full"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Street name</td>
                                        <td id="street_name"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">country</td>
                                        <td id="country"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">state</td>
                                        <td id="state"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">city</td>
                                        <td id="city"></td>
                                    </tr>
                                    <tr>
                                        <td width="30%">Pincode</td>
                                        <td id="zip"></td>
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
		function validatePanNumber(panNumber)
		{
			var regex = /[A-Z]{5}[0-9]{4}[A-Z]{1}/;
			return  regex.test(panNumber);
		}
		
        $('#submitBtn').click(function() {
            var input = $('#InputNumber').val().toUpperCase().trim();
            var isValidVehicleNumber = validatePanNumber(input);
            // var isValidVehicleNumber = 1;
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
                    url: "{{ route('pancard.pancardPostData') }}", // Path to controller through routes/web.php
                    type: 'POST',
                    data: { input: input},
                    dataType: 'json',
                    success: function(response) {
                        loader.style.display = 'none';
                        if(response.status == 1)
                        {
                            console.log(response);
                            $(".re_details").css('display', 'block');
                            $(".no-data").hide();
                            $('.error-message').html('');
                            $(".searched-details").css('display', 'block');

                            $("#name").text(response.data["full_name"]);
                            $("#dob_r").text(response.data['dob']);
                            $("#less_info").text(response.data['less_info']);
                            $("#masked_aadhaar").text(response.data['masked_aadhaar']);
                            $("#phone_number").text(response.data['phone_number']);
                            $("#category").text(response.data['category']);
                            $("#gender").text(response.data['gender']);
                            $("#full").text(response.data.address['full']);
                            $("#street_name").text(response.data.address['street_name']);
                            $("#country").text(response.data.address['country']);
                            $("#state").text(response.data.address['state']);
                            $("#email").text(response.data['email']);
                            $("#city").text(response.data.address['city']);
                            $("#zip").text(response.data.address['zip']);
                            $("#aadhaar_linked").text(response.data.aadhaar_linked);

                        }
                        else if(response.status != 1){
                            $('#noDataFound').html('<h4 style="color:red;">Error  </h4> <p>'+response.message+'.</p>');
                            $(".searched-details").css('display', 'none');
                            $('.error-message').html('');
                            $(".re_details").css('display', 'none');
                            $(".no-data").show();
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
                 $(".no-data").show();
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