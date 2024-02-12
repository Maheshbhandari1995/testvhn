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
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Vehicle Number</h5>
            <div  class="col-lg-6 mb-4">
                <label class="form-label" for="file">Upload file</label>
                <input type="file" class="form-control" id="file" name="file" accept=".csv" required>
               @error('file') <!-- Updated field name to 'file' -->
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div class="row g-3">
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
<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script>


$(document).ready(function() {
  $('#submitBtn').click(function() {
    // Get the file input element
    var fileInput = document.getElementById('file');

    // Check if a file is selected
    if (fileInput.files.length == 0) {
      alert('Please select a file.');
      return; // Stop further execution
    }

    // Check the file type
    var fileType = fileInput.files[0].type;
    if (
      fileType !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' && // CSV
      fileType !== 'text/csv'
    ) {
      alert('Please upload a CSV file.');
      return; // Stop further execution
    }

    // Create a new FormData object
    var formData = new FormData();

    // Append the file to the FormData object
    formData.append('rcdata', fileInput.files[0]);

    // Show loader
    var loader = document.getElementById('loader');
    loader.style.display = 'block';
    $(".re_details").css('display', 'none');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

                    // Add the CSRF token to the AJAX request headers
        $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': csrfToken
        }
        });
    // Make the Ajax request
    $.ajax({
      url: "{{ route('authbridgeRCBulk.Data') }}",
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(response) {
        // Hide loader
        loader.style.display = 'none';

        //$(".re_details").css('display', 'block');
        $('#file').val('');
        console.log(response);
        // Check if the response is a file download
        if (response.download) {
    // Make an HTTP GET request to retrieve the file

          // Create a temporary <a> element to trigger the file download
          var link = document.createElement('a');
          link.href = response.file_url;
          link.download = response.file_name;
          link.style.display = 'none';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
  } else {
    // Handle the response if it's not a file download
  }
      },
      error: function(xhr, status, error) {
        // Handle the error
        console.log('AJAX Error:', error);
      }
    });
  });
});

    // $(document).ready(function() {
    //     $('#submitBtn').click(function() {
    //         // Get the file input element
    //         var fileInput = document.getElementById('fileInput');

    //         // Create a new FormData object
    //         var formData = new FormData();

    //         // Append the file to the FormData object
    //         formData.append('rcdata', fileInput.files[0]);

    //         // Show loader
    //         var loader = document.getElementById('loader');
    //         loader.style.display = 'block';
    //         $(".re_details").css('display','none');

    //         // Make the Ajax request
    //         $.ajax({
    //         url: "{{ route('authbridgeRCBulk.Data') }}",
    //         type: 'POST',
    //         data: formData,
    //         contentType: false,
    //         processData: false,
    //         success: function(response) {
    //             // Hide loader
    //             loader.style.display = 'none';
    //             $(".re_details").css('display', 'block');

    //             // Process the response
    //             if (response.status_code == 200) {
    //             displayDetails(response.message);
    //             } else {
    //             $('.re_details').html('<div class="card"><div class="card-body"><h2 class="card-title" style="color:red;">Error</h2><div>' + response.status + '</div></div></div>');
    //             }
    //         },
    //         error: function(xhr, status, error) {
    //             // Handle the error
    //             console.log('AJAX Error:', error);
    //         }
    //         });
    //     });
    // });

//     $(document).ready(function() {
//     $('#submitBtn').click(function() {
//     // Get the file input element
//     var fileInput = document.getElementById('fileInput');

//     // Create a new FormData object
//     var formData = new FormData();

//     // Append the file to the FormData object
//     formData.append('rcdata', fileInput.files[0]);

//     // Show loader
//     var loader = document.getElementById('loader');
//     loader.style.display = 'block';
//     $(".re_details").css('display','none');

//     // Make the Ajax request
//     $.ajax({
//       url: "{{ route('authbridgeRCBulk.Data') }}",
//       type: 'POST',
//       data: formData,
//       contentType: false,
//       processData: false,
//       success: function(response) {
//         // Hide loader
//         loader.style.display = 'none';
//         $(".re_details").css('display', 'block');

//         // Check if the response is a file download
//         if (response.download) {
//           // Create a temporary <a> element to trigger the file download
//           var link = document.createElement('a');
//           link.href = response.file_path;
//           link.download = response.file_name;
//           link.style.display = 'none';
//           document.body.appendChild(link);
//           link.click();
//           document.body.removeChild(link);
//         } 
//         // else {
//         //   // Process the response
//         //   if (response.status_code == 200) {
//         //     displayDetails(response.message);
//         //   } else {
//         //     $('.re_details').html('<div class="card"><div class="card-body"><h2 class="card-title" style="color:red;">Error</h2><div>' + response.status + '</div></div></div>');
//         //   }
//         // }
//       },
//       error: function(xhr, status, error) {
//         // Handle the error
//         console.log('AJAX Error:', error);
//       }
//     });
//   });
// });

</script>

@endsection