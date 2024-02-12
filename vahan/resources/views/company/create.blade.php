
@extends('layout')
@section('content')

<main id="main">
<div class="pagetitle justify-content-between d-flex">
    <h1>Create organization</h1>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('company.index') }}"> <i class="bi bi-arrow-left"></i></a>
</div>
<div class="card pt-5">
  <div class="card-body">



<?php 
// echo "<pre>"; print_r(($requested_data) ? $requested_data : '' );

// echo "vendors : <pre>"; print_r(($vendors) ? $vendors : '' );

// echo "moduleMaster :  <pre>"; print_r(($moduleMaster[0]->api_name) ? $moduleMaster[0]->api_name : '' );
// die;

?>
  @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

   <!-- create.blade.php -->
<form method="POST" action="{{ route('company.store') }}" enctype="multipart/form-data">
    @csrf
<div class="row">
    <div class="col-lg-3 mb-4">
        <input type="text" class="form-control" id="name" name="name" value="<?php echo (isset($requested_data['name']) ? $requested_data['name'] : '') ?>" required>
        <label class="form-element-label" for="name">Company Name</label>
        @error('name')
            <span>{{ $message }}</span>
        @enderror
    </div>

    <div  class="col-lg-3 mb-4">
        <input type="email" class="form-control" id="email" name="email" value="<?php echo (isset($requested_data['email']) ? $requested_data['email'] : '') ?>" required>
        <label class="form-element-label" for="email">Email</label>
        @error('email')
            <span>{{ $message }}</span>
        @enderror
    </div>

    <div class="col-lg-3 mb-4">
        <select id="envtype" class="form-control" name="envtype" required>
            <option value="production" selected="<?php echo (isset($requested_data['envtype']) ? 'selected' : '') ?>">Production</option>
        </select>
        <label class="form-element-label" for="envtype">Environment Type</label>
        @error('envtype')
            <span>{{ $message }}</span>
        @enderror
    </div>


    <div  class="col-lg-3 mb-4 credit">
        <input type="text" class="form-control" id="max_count" name="max_count" value="<?php echo (isset($requested_data['envtype']) ? $requested_data['max_count'] : '') ?>" required>
        <label class="form-element-label" for="max_count">Add Credits</label>
        @error('email')
        <span>{{ $message }}</span>
        @enderror
    </div>
</div>
<hr>
<div id="modulesContainer">
@if(isset($requested_data['module']) && !empty($requested_data['module']))
    @foreach($requested_data['module'] as $key => $module)
        <div id="modulesCreate" class="row">
            <div class="col-11">
                <div class="row">
                    <div  class="col-lg-4 mb-4">
                        <select name="module[]" id="" class="form-control module-select" required>
                            <option value=""></option>
                            @foreach($moduleMaster as $moduleOption)
                            <?php //echo $module. " <>". $moduleOption->api_name; die;?>
                            <option value="{{ $moduleOption->api_name }}" {{ $requested_data['module'][$key] == $moduleOption->api_name ? 'selected' : '' }}>
                                {{ strtoupper($moduleOption->api_name) }}
                            </option>
                            @endforeach
                            
                        </select>
                        <label class="form-element-label" for="max_count">Modules</label>
                    </div>
                    <div  class="col-lg-4 mb-4">
                        <select name="primary_vendor[]" id="" class="form-control primary-vendor-select" required>
                            <option value="">Select Vendor</option>
                            @foreach($vendors[$module] as $vendor)
                            <option value="{{ $vendor->vender }}" {{ $requested_data['primary_vendor'][$key] == $vendor->vender ? 'selected' : '' }}>
                                {{ strtoupper($vendor->vender) }}
                            </option>
                            @endforeach
                        </select>
                        <label class="form-element-label" for="max_count">Primary Vendor</label>
                    </div>
                    <div  class="col-lg-4 mb-4">
                        <select name="secondary_vendor[]" id="" class="form-control secondary-vendor-select">
                            <option value="">Select Vendor</option>
                            @foreach($vendors[$module] as $vendor)
                            <option value="{{ $vendor->vender }}" {{ $requested_data['secondary_vendor'][$key] == $vendor->vender ? 'selected' : '' }}>
                                {{ strtoupper($vendor->vender) }}
                            </option>
                            @endforeach
                        </select>
                        <label class="form-element-label" for="max_count">Secondary Vendor</label>
                    </div>
                </div>
            </div>
            <div class="col text-center d-none">
                <button id="closeRow" class="closet-btn-set" type="button"><i class="bi bi-dash"></i></button>
            </div>
        </div>
    @endforeach
@endif
@if(!isset($requested_data['module']))
<div id="modulesCreate" class="row">
    <div class="col-11">
        <div class="row">
            <div  class="col-lg-4 mb-4">
                <select name="module[]" id="" class="form-control module-select" required>
                    <option value="">Select Module</option>
                </select>
                <label class="form-element-label" for="max_count">Modules</label>
            </div>
            <div  class="col-lg-4 mb-4">
                <select name="primary_vendor[]" id="" class="form-control primary-vendor-select" required>
                    <option value=""></option>
                </select>
                <label class="form-element-label" for="max_count">Primary Vendor</label>
            </div>
            <div  class="col-lg-4 mb-4">
                <select name="secondary_vendor[]" id="" class="form-control secondary-vendor-select">
                    <option value=""></option>
                </select>
                <label class="form-element-label" for="max_count">Secondary Vendor</label>
            </div>
        </div>
    </div>
</div>

@endif
</div>
<div class="">
    <button class="btn btn-outline-primary plus-btn" type="button"><i class="bi bi-plus-lg"></i> Add More</button>
</div>
<hr>

    <div class="col-lg-6 mb-4 expirydate">
        <input type="text" id="datepicker" class="form-control datepicker" name="date">
        <label class="form-element-label" for="date">Expiry Date</label>
    </div>

    <div  class="col-lg-6 mb-4">
        <select id="status" class="form-control" name="status" required>
            <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Active</option>
            <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>Inactive</option>
        </select>
        <label class="form-element-label" for="status">Status</label>
        @error('status')
            <span>{{ $message }}</span>
        @enderror
    </div>

    <div class="text-center" style="margin-top: 28px;">
        <button class="btn btn-primary" type="submit">Submit</button>
    </div>
    
</form>

  </div>
</div>

<script>
    $(document).ready(function() {

        $('.expirydate').css('display','none');
        $("#datepicker").attr("required", false);

        $('#max_count').keypress(function(event) {
            // var inputValue = event.key;
            
            // Check if the input value is "-"
            // if (inputValue === '-') {
            //     event.preventDefault(); // Prevent the "-" character from being entered
            // }

            var inputValue = $(this).val();

            // Remove any non-digit characters
            inputValue = inputValue.replace(/[^0-9]/g, '');

            // Update the input value
            $(this).val(inputValue);
        });


        $('#envtype').change(function(event) {
            var value = $('#envtype').val();
            if(value == 'preproduction'){
                $('.expirydate').css('display','block');
                $("#datepicker").attr("required", true);
            }
            else if(value == 'production')
            {
                $('.expirydate').css('display','none');
                $("#datepicker").attr("required", false);
                $("#datepicker").val(null);
                $("#datepicker").datepicker("setDate", null);
                $("#datepicker").removeAttr("value");

            }
            else{
                $('.expirydate').css('display','none');
                $("#datepicker").attr("required", false);
                $("#datepicker").val(null);
                $("#datepicker").datepicker("setDate", null);
                $("#datepicker").removeAttr("value");
            }
        });


        $( "#datepicker" ).datepicker({
            changeYear: true,
            changeMonth: true,
            minDate:0,
            dateFormat: "yy-m-dd",
            yearRange: "-100:+20",
        });

        getModule();

        // Validate Module and Primary Vendor selection before form submission
        $("form").submit(function (event) {
            var modules = $(".module-select");
            var primaryVendors = $(".primary-vendor-select");
            var nameInput = $("#name");
            var emailInput = $("#email");
            var allowCreditInput = $("#max_count");
            var isValid = true;

            // Check if Name field is empty
            if (nameInput.val().trim() === "") {
            nameInput.addClass("is-invalid");
            isValid = false;
            } else {
            nameInput.removeClass("is-invalid");
            }

            // Check if Email field is empty or invalid format
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailInput.val().trim() === "" || !emailPattern.test(emailInput.val())) {
            emailInput.addClass("is-invalid");
            isValid = false;
            } else {
            emailInput.removeClass("is-invalid");
            }

            // Check if Allow Credit field is empty or non-numeric
            if (allowCreditInput.val().trim() === "" || isNaN(allowCreditInput.val())) {
            allowCreditInput.addClass("is-invalid");
            isValid = false;
            } else {
            allowCreditInput.removeClass("is-invalid");
            }

            // Check if any module is selected without a corresponding primary vendor
            modules.each(function (index) {
            if ($(this).val() !== "" && primaryVendors.eq(index).val() === "") {
                isValid = false;
                $(this).addClass("is-invalid");
                primaryVendors.eq(index).addClass("is-invalid");
            }
            });

            if (!isValid) {
            event.preventDefault();
            }
        });

        // Reset validation on module and primary vendor selection change
        $(document).on("change", ".module-select, .primary-vendor-select", function () {
            var parentRow = $(this).closest(".row");
            var moduleSelect = parentRow.find(".module-select");
            var primaryVendorSelect = parentRow.find(".primary-vendor-select");

            moduleSelect.removeClass("is-invalid");
            primaryVendorSelect.removeClass("is-invalid");
        });
    });

    function getModule(){
        $.ajax({
            url: "{{ route('company.modules') }}",
            type: "GET",
            dataType: "json",
            success: function (response) {
                // console.log(response.module);
                var options = "<option value=''></option>";
                $.each(response.module, function (key, module) {
                    // console.log(module);
                    options += "<option value='" + module.api_name + "'>" + module.api_name.toUpperCase() + "</option>";
                });
                $(".module-select").html(options);
            }
        });
    }

    function fetchPrimaryVendors(module, primaryVendorSelect) {
        
        $.ajax({
            url: "{{ route('company.primary_vendors') }}",
            type: "GET",
            data: {
                module: module
            },
            dataType: "json",
            success: function (response) {
                var options = "<option value=''>Select Vendor</option>";
                //console.log(response.vendors);
                $.each(response.vendors, function (key, vendor) {
                    options += "<option value='" + vendor.vender + "'>" + vendor.vender.toUpperCase() + "</option>";
                });
                $(primaryVendorSelect).html(options);
            }
        });
    }

    function fetchSecondaryVendors(module, vendor, secondaryVendorSelect) {
       
        $.ajax({
            url: "{{ route('company.secondary_vendors') }}",
            type: "GET",
            data: {
                module: module,
                vendor:vendor
            },
            dataType: "json",
            success: function (response) {
                var options = "<option value=''>Select Vendor</option>";
                $.each(response.vendors, function (key, vendor) {
                    options += "<option value='" + vendor.vender + "'>" + vendor.vender.toUpperCase() + "</option>";
                });
                $(secondaryVendorSelect).html(options);
            }
        });
    }


    // Event listener for module selection change
    $(document).on('change', '.module-select', function () {
        var module = $(this).val();
        var parentRow = $(this).closest('.row');
        var primaryVendorSelect = parentRow.find('.primary-vendor-select');
        //var secondaryVendorSelect = parentRow.find('.secondary-vendor-select');

        // Clear previous options
        // primaryVendorSelect.empty().append("<option value=''>Select Vendor</option>");
        // secondaryVendorSelect.empty().append("<option value=''>Select Vendor</option>");

        // Fetch primary vendors for the selected module
        if (module !== "") {
            fetchPrimaryVendors(module, primaryVendorSelect);
        }
    });

    // Event listener for primary vendor selection change
    $(document).on('change', '.primary-vendor-select', function () {
        var moduleId = $(this).closest('.row').find('.module-select').val();
        var primaryVendorId = $(this).val();
        var secondaryVendorSelect = $(this).closest('.row').find('.secondary-vendor-select');

        // Clear previous options
        //secondaryVendorSelect.empty().append("<option value=''>Select Vendor</option>");

        // Fetch secondary vendors for the selected module and primary vendor
        if (moduleId !== "" && primaryVendorId !== "") {
            fetchSecondaryVendors(moduleId, primaryVendorId, secondaryVendorSelect);
        }
    });
    

//  Add more 

    let count = 0;
    $(".plus-btn").click( function(){
    console.log($(this));
        if(count < 9){
            var cloneElement = $("#modulesCreate").clone();
            // if(cloneElement.hasClass('first_element')){
            //   cloneElement.removeClass('first_element');
            // }
            $("#modulesContainer").append(cloneElement);
            cloneElement.find(".form-element-field").removeClass("-hasvalue").val("");
            // cloneElement.find(".form-element-field").remove("required");
        }
        count++;

        if(count >= 1){
        //   cloneElement.children(".col:last-child").children("#plus-btn-add").hide();
        cloneElement.children(".col:last-child").removeClass("d-none");
        }
        if(count == 9){
            $(this).attr("disabled", true);
        }
        
        var close = cloneElement.children(".col:last-child").children("#closeRow");
        $(close).on("click", function(){
        //   $(this).parent().parent().prev().find('#plus-btn-add').attr('disabled',false);
        if(count > 9){
            $(this).find('.plus-btn').attr('disabled',false);
        }
        $(this).parents("#modulesCreate").remove();
        count = count-1;
        });  
    });
</script>
</main>
@endsection