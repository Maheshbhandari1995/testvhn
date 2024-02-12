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

    table.dataTable.no-footer, .table-striped>tbody>tr:nth-of-type(odd)>*, .table>:not(caption)>*>*, table.dataTable thead th, table.dataTable thead td{border-bottom: none; padding: 2px 5px;}

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
</style>
<!-- Main container -->
<div id="main" class="main">
    <div class="row">
        <div class="pagetitle">
            {{-- <img src="{{asset('assets/img/edas-logo-light.png')}}" alt="Amygb"> --}}
            {{-- <h1>Challan Details</h1> --}}
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-3">
            <input type="text" id="vehicleNo" placeholder="" class="form-control capitalized-text text-uppercase" required>
            <label class="form-element-label" for="vehicleNo">Vehicle Number</label>
        </div>
        <div class="col-lg-auto">
            <button id="submitBtn" class="btn btn-primary">Submit</button>
        </div>
        <div class="col-lg-auto error-message" id="validate"></div>
    </div>


    <div class="row no-data g-3">
        <div class="col-lg-12">
            <div>
                <div class="no-data-content">
                    <h4 >No Data Found</h4>
                    <p id="noDataFound" >Searched vehicle detail will be displayed here. <br> To search enter vehicle number</p>
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

    <div class="challan_details">
        <div class="">
            <div class="">
                <div class="d-flex align-items-center mb-2">
                    <h2 class="card-title me-auto" style="margin-top: 15px;">Challan Details</h2>

                    <a href="#" id="downloadPDF" data-content="" style="display:none;color:#a50101; border:1px solid; border-radius:20px; padding:5px 15px;"><i class="bi bi-file-pdf-fill"></i> Download </a>
                    {{-- bi bi-file-pdf-fill --}}
                    {{-- bi bi-filetype-csv --}}
                    {{-- generatePDF --}}
                    {{-- downloadPDF --}}
                </div>
            
                <div id="challanDetails"></div>
            </div>
        </div>
    </div>
    {{-- <div class="challan_details">
        <h2 class="card-title" style="
        margin-top: 15px;
    ">Challan Details</h2>
        <div id="challanDetails"></div>

    </div> --}}
    <div class="re_details_error">
        <div id="error"></div>
    </div>
</div>

<script src="{{asset('assets/socialmedia/emoji/js/jquery-3.2.1.min.js')}}"></script>
<script src="{{asset('assets/socialmedia/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/socialmedia/js/FileSaver.min.js')}}"></script>
<script src="{{asset('assets/socialmedia/js/html2canvas.min.js')}}"></script>
<script src="{{asset('assets/socialmedia/js/jspdf.min.js')}}"></script>
<script src="{{asset('assets/socialmedia/js/jspdf.umd.min.js')}}"></script>
<script src="{{asset('assets/socialmedia/emoji/js/jquery.emojiarea.js')}}"></script>
<script src="{{asset('assets/socialmedia/js/html2pdf.js')}}"></script>

<script>
    $(document).ready(function() {

        let pdfresponse = null;

        $('#submitBtn').click(function() {
            var vehicle_No = $('#vehicleNo').val().toUpperCase().trim();
            // console.log(vehicle_No);
            var isValidChallanNumber = validateVehicleNumber(vehicle_No);
            //console.log(isValidChallanNumber);
            if(isValidChallanNumber !== false || isValidChallanNumber != '')
            {
                var loader = document.getElementById('loader');
                $(".challan_details").css('display','none');
                loader.style.display = 'block';
                 // Get the CSRF token value from the meta tag
                 var csrfToken = $('meta[name="csrf-token"]').attr('content');
                    $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': csrfToken
                    }
                    });
                $.ajax({
                    url: "{{ route('data.schallandata') }}",// Path to controller through routes/web.php
                    type: 'POST',
                    data: { vehicle_No: vehicle_No },
                    dataType: 'json',
                    success: function(responses) {
                        
                        var api_log_id = responses.api_log_id;
                        var response = responses.return;
                        // Hide loader
                        console.log(response.message.result.challanDetails);
                        console.log("api_log_id : "+api_log_id);
                        loader.style.display = 'none';
                        if (response.code == 200) {
                            var challanDetails = response.message.result.challanDetails;

                            if (!challanDetails || (Array.isArray(challanDetails) && challanDetails.length === 0)) {

                                $('#validate').html('');
                                $('.no-data').css('display', 'flex');
                                $('#noDataFound').html(response.message.result.message).css("color", "red");
                                $('.re_details_error').css('display', 'block');
                                loader.style.display = 'none';

                            } else {
                                loader.style.display = 'none';
                                $(".challan_details").css('display', 'block');
                                $('.no-data').css('display', 'none');
                                $('#validate').html('');
                                $('#vehicleNo').val('');
                                displayVehicleDetails(response.message.result.challanDetails);
                                pdfresponse = response.message.result.challanDetails;
                                $('#downloadPDF').attr('data-content', api_log_id);
                                //console.log(pdfresponse);
                                $('#downloadPDF').css('display', 'block');
                                $('#collapse0').addClass('show');
                            }
                        }
                        else{
                            loader.style.display = 'none';
                            $('#validate').html('');
                            $('.no-data').css('display', 'flex');
                            $('#noDataFound').html(response.Error).css("color", "red");
                            $('#noDataFound').html(response.message).css("color", "red");
                            $('.re_details_error').css('display', 'block');
                        }
                    },
                    error: function(xhr, status, error) {
                        loader.style.display = 'none';
                        $('.no-data').css('display', 'flex');
                        $('#noDataFound').html('An error occurred: ' + error).css("color", "red");
                        $('.re_details_error').css('display', 'block');
                    }
                });
            }
            else{
                //$('#noDataFound').html('<p style="color:red;"> Please enter valid challan no </p>');
                loader.style.display = 'none';
                $('#validate').html('<p> Please enter valid challan no </p>');
                $('.no-data').css('display', 'flex');
                $('#noDataFound').css('display', 'block');
                $(".challan_details").css('display', 'none');
            }
        });



        $('#downloadPDF').click(function(){
			var id = $(this).attr('data-content');
			//console.log(id);
			//alert(id);
			$.ajax({
				url: "{{ route('rc.downloadChallanPDF') }}", // Path to controller through routes/web.php
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

    //     $('#downloadPDF').click(function() {
    //         downloadpdf(); // Call downloadpdf() when the button is clicked
    //     });


    // function downloadpdf() {

    //         if (pdfresponse) {
    //         // Create a new jsPDF instance
    //         var doc = new jsPDF();

    //         // Set initial position
    //         var x = 20;
    //         var y = 10;

    //         // Determine headers dynamically based on all unique keys in the response
    //         var allKeys = pdfresponse.reduce((keys, obj) => keys.concat(Object.keys(obj)), []);
    //         var uniqueHeaders = [...new Set(allKeys)];

    //         // Set font size and style
    //         doc.setFontSize(9);
    //         doc.setFontStyle('normal');


    //         // Loop through each record
    //         pdfresponse.forEach((record, rowIndex) => {
    //             // Check if there is enough space on the page for the current record
    //             if (y + uniqueHeaders.length * 7 > doc.internal.pageSize.height - 7) {
    //                 // Add a new page
    //                 doc.addPage();
    //                 // Reset y position for the new page
    //                 y = 7;
    //             }

    //             // Calculate column widths based on the page width
    //             var pageWidth = doc.internal.pageSize.width;
    //             var columnWidth = pageWidth / 2 - 10; // Two columns with padding


    //             // Loop through each header
    //             uniqueHeaders.forEach((header) => {
    //                 console.log("header : "+header);
    //                 console.log("record : "+record[header]);

    //                 // Print property name on the left
    //                 doc.text(header, x, y);
    //                 doc.setFontStyle('normal');

    //                 // Print corresponding value on the right
    //                 var cellValue = record[header] || ''; // Use empty string if property is not present
    //                 var lines = doc.splitTextToSize(cellValue.toString(), columnWidth);
    //                 doc.text(lines, x + 50, y);
    //                 // Move to the next line
    //                 y += 7;

    //                 // Add a line to separate records
    //                 // doc.line(20, y, pageWidth - 10, y);
    //                 // y += 1; // Adjust the space after the line
    //         });

    //             y += 7;
    //         });

    //         // Save the document
    //             doc.save('challan_details.pdf');
    //         } else {
    //             console.error('PDF content is not available. Fetch it first.');
    //         }
    // }


});


    function displayVehicleDetails(vehicleDetails) {
        var vehicleDetailsHtml = '<div class="accordion" id="accordionExample">';

        $.each(vehicleDetails, function(i, challanDetails) {
            var accordionId = 'accordion' + i;
            var collapseId = 'collapse' + i;

            vehicleDetailsHtml += '<div class="accordion-item">';
            vehicleDetailsHtml += '<h2 class="accordion-header" id="heading' + i + '">';
            vehicleDetailsHtml += '<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" aria-expanded="true" aria-controls="' + collapseId + '">';
            vehicleDetailsHtml += 'Challan N0. ' + (i + 1);
            vehicleDetailsHtml += '</button>';
            vehicleDetailsHtml += '</h2>';
            vehicleDetailsHtml += '<div id="' + collapseId + '" class="accordion-collapse collapse" aria-labelledby="heading' + i + '" data-bs-parent="#accordionExample">';
            vehicleDetailsHtml += '<div class="accordion-body">';
            vehicleDetailsHtml += generateTableHtml(challanDetails);
            vehicleDetailsHtml += '</div>';
            vehicleDetailsHtml += '</div>';
            vehicleDetailsHtml += '</div>';
        });

    vehicleDetailsHtml += '</div>';
    $('#challanDetails').html(vehicleDetailsHtml);
    }

    function generateTableHtml(data) {
        var tableHtml = '<table  class="table table-striped">';
        $.each(data, function(index, detail) {
            var fieldName = index;
            var fieldValue = detail;
            tableHtml += '<tr><td width="30%">' + fieldName + '</td><td>' + fieldValue + '</td></tr>';
        });
        tableHtml += '</table>';
        return tableHtml;
    }

    // function downloadPDF() {
    //     // Select the accordion containing the data
    //     const accordion = document.getElementById('accordionExample');

    //     // Create a CSV content string
    //     let csvContent = "data:text/csv;charset=utf-8,";

    //     // Iterate through accordion items
    //     const accordionItems = Array.from(accordion.querySelectorAll('.accordion-item'));
    //     accordionItems.forEach(item => {
    //         // Extract header and data rows from each accordion item
    //         const header = item.querySelector('.accordion-button').textContent.trim();
    //         const dataRows = Array.from(item.querySelectorAll('tbody tr')).map(row => {
    //             const rowData = Array.from(row.querySelectorAll('td')).map(cell => cell.textContent.trim());
    //             return rowData.join(',');
    //         });

    //         // Add header and data rows to CSV content
    //         csvContent += header + '\r\n';
    //         csvContent += dataRows.join('\r\n') + '\r\n';
    //     });

    //     // Create a data URI and trigger the download
    //     const encodedUri = encodeURI(csvContent);
    //     const link = document.createElement('a');
    //     link.setAttribute('href', encodedUri);
    //     link.setAttribute('download', 'challan_details.csv');
    //     document.body.appendChild(link);
    //     link.click();
    //     document.body.removeChild(link);
    // }



</script>

@endsection