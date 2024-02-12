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

    table.dataTable.no-footer, .table-striped>tbody>tr:nth-of-type(odd)>*, .table>:not(caption)>*>*, table.dataTable thead th, table.dataTable thead td{border-bottom: none; padding: 2px 5px;}
</style>
<!-- Main container -->
<div id="main" class="main">
    <div class="row">
        <div class="pagetitle">
            <h1>Challan Details</h1>
        </div>
    </div>

    <div id="validate" style="color:red;"></div>

            <div class="row g-3">
                <div class="col-auto">
                    <input type="text" id="challanNo" placeholder="Vehicle Number" class="form-control capitalized-text text-uppercase" required>
                </div>
                <div class="col-auto">
                    <input type="text" id="chassisNo"  pattern="^(?!\d{5}$).*$" placeholder="Chassis Number" class="form-control capitalized-text text-uppercase" required>
                </div>
                <div class="col-auto">
                    <button id="submitBtn" class="btn btn-primary">Submit</button>
                </div>
            </div>

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

    {{-- <div class="row no-data g-3">
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
    </div> --}}
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
        <div class="">
            <div class="">
                <div class="d-flex align-items-center mb-2">
                    <h2 class="card-title me-auto" style="margin-top: 15px;">Challan Number</h2>

                    <a href="#"  id="downloadcsv" style="display:none;color:#a50101; border:1px solid; border-radius:20px; padding:5px 15px;"><i class="bi bi-file-pdf-fill"></i> Download </a>
                    {{-- generatePDF --}}
                    {{-- downloadCsv --}}
                </div>
            
                <div id="challanDetails"></div>
            </div>
        </div>
    </div>
    <div class="re_details_error">
        <div id="error"></div>
    </div>
</div>

<!-- //Main container -->

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
            var vehicle_No = $('#challanNo').val();
            var chassis_No = $('#chassisNo').val();
            var isValidChallanNumber = validateVehicleNumber(vehicle_No);
            var isValidChassisNo = validateChassisLastFiveDigit(chassis_No);

            if(vehicle_No !== null && vehicle_No !== '' || chassis_No !== null && chassis_No !== ''){
            //console.log(isValidChallanNumber);
            if(isValidChallanNumber !== false || vehicle_No !=  '' ||  vehicle_No !=  null)
            {
                if(isValidChassisNo !== false || chassis_No !=  '' || chassis_No !=  null)
                {
                    var loader = document.getElementById('loader');
                    $(".challan_details").css('display','none');
                    loader.style.display = 'block';
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');
                        $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                        });
                                    
                    // Make the Ajax request
                    $.ajax({
                        url: "{{ route('challan.challanWithChassisPostData') }}",// Path to controller through routes/web.php
                        type: 'POST',
                        data: {type:'challan_chassis', vehicle_No: vehicle_No, chassis_No: chassis_No},
                        dataType: 'json',
                        success: function(response) {
                            // Hide loader
                            loader.style.display = 'none';
                            // console.log(response);
                        //console.log(response);

                        if (response.status_code == 200 || response.status_code == 100 ) {
                            $(".challan_details").css('display', 'block');
                            $('.no-data').css('display', 'none');
                            $('#validate').html('');
                            $('#chassisNo').val('');
                            $('#challanNo').val('');
                            var challanDetails = response.data;
                            let result = Array.isArray(response.data);
                            if(result == true){
                                $('#downloadcsv').css('display', 'block');
                                displayVehicleDetails(challanDetails);
                                pdfresponse = challanDetails;
                                
                            console.log(pdfresponse);
                                $('#collapse0').addClass('show');
                            }else{
                                $('#validate').html(response.data);
                                $(".challan_details").css('display', 'none');
                            }
                        }
                        else{
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
                    $('#validate').html('<p> Please enter valid Chassis Number </p>');
                    $('#noDataFound').css('display', 'block');
                }
            }
            else{
                $('#validate').html('<p> Please enter valid Vehicle Number </p>');
                $('#noDataFound').css('display', 'block');
            }
        }
        });

        
        $('#downloadcsv').click(function() {
            downloadpdf(); // Call downloadpdf() when the button is clicked
        });


        function downloadpdf() {

            if (pdfresponse) {
                // Create a new jsPDF instance
                var doc = new jsPDF();

                // Set initial position
                var x = 20;
                var y = 10;

                // Determine headers dynamically based on all unique keys in the response
                var allKeys = pdfresponse.reduce((keys, obj) => keys.concat(Object.keys(obj)), []);
                var uniqueHeaders = [...new Set(allKeys)];

                // Set font size and style
                doc.setFontSize(9);
                doc.setFontStyle('normal');


                // Loop through each record
                pdfresponse.forEach((record, rowIndex) => {
                    // Check if there is enough space on the page for the current record
                    if (y + uniqueHeaders.length * 7 > doc.internal.pageSize.height - 7) {
                        // Add a new page
                        doc.addPage();
                        // Reset y position for the new page
                        y = 7;
                    }

                    // Calculate column widths based on the page width
                    var pageWidth = doc.internal.pageSize.width;
                    var columnWidth = pageWidth / 2 - 10; // Two columns with padding


                    uniqueHeaders.forEach((header) => {
                        console.log("header : " + header);
                        console.log("record : " + record[header]);

                        // Print property name on the left
                        doc.text(header, x, y);
                        doc.setFontStyle('normal');

                        // Print corresponding value on the right
                        var cellValue = record[header] || ''; // Use empty string if property is not 

                        if (typeof cellValue === 'object') {
                    // If the value is an object, convert it to a string
                    record.Offences.forEach((offence) => {
                        // Applying word wrap for "Offence Name"
                        var linesOffenceName = doc.splitTextToSize(offence.offence_name, columnWidth);
                        var linesPenalty = doc.splitTextToSize(offence.penalty.toString(), columnWidth);

                        // Check if there is enough space on the current line
                        if (y + Math.max(linesOffenceName.length, linesPenalty.length) * 7 > doc.internal.pageSize.height - 7) {
                            // Move to the next line
                            y += Math.max(linesOffenceName.length, linesPenalty.length) * 7;
                        }

                        linesOffenceName.forEach((line, index) => {
                            doc.text(line, 70, y + index * 7);
                        });

                        linesPenalty.forEach((line, index) => {
                            doc.text(line, x + 40, y + index * 7);
                        });

                        y += Math.max(linesOffenceName.length, linesPenalty.length) * 7;
                    });
                } else {
                    // Applying word wrap for other values
                    var lines = doc.splitTextToSize(cellValue.toString(), columnWidth);
                    doc.text(lines, x + 50, y);
                    y += lines.length * 7;
                }
                        // doc.line(10, y, pageWidth - 10, y);
                        // y += 1; // Adjust the space after the line
                    });

                        y += 7;
                });

                    doc.save('challan_details.pdf');
            } else {
                console.error('PDF content is not available. Fetch it first.');
            }
        }

    });


    function validateChassisLastFiveDigit(chassisNumber) {
            // Check if the chassis number has at least 5 characters
            if (chassisNumber.length < 5) {
                return false;
            }

            // Extract the last five characters
            var lastFiveDigits = chassisNumber.slice(-5);

            // Validate that all characters are digits
            return /^\d{5}$/.test(lastFiveDigits);
        }


        function displayVehicleDetails(vehicleDetails) {
            var vehicleDetailsHtml = '<div class="accordion" id="accordionExample">';

            $.each(vehicleDetails, function (i, challanDetails) {
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
            var tableHtml = '<table class="table table-striped">';
            $.each(data, function (index, detail) {
                var fieldName = index;
                var fieldValue = detail;

                // Handle the 'Offences' field which is an array
                if (fieldName === 'Offences' && Array.isArray(fieldValue)) {
                    tableHtml += '<tr><td width="30%">' + fieldName + '</td><td>';
                    fieldValue.forEach(function (offence) {
                        tableHtml += generateTableHtml(offence); // Recursively generate table for each offence
                    });
                    tableHtml += '</td></tr>';
                } else {
                    tableHtml += '<tr><td width="30%">' + fieldName + '</td><td>' + fieldValue + '</td></tr>';
                }
            });
            tableHtml += '</table>';
            return tableHtml;
        }


    // function downloadCsv() {
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