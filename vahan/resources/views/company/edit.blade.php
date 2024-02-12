@extends('layout')
@section('content')
 <?php
    // echo "modules<pre>"; print_r($modules[0]);
        // echo "<pre>"; print_r($vendors[$modules[0]->apiname]);die;
 ?>
<main id="main">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pagetitle justify-content-between d-flex">
                <h1>Update Organization</h1>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('company.index') }}"> <i class="bi bi-arrow-left"></i></a>
            </div>
        </div>
    </div>
<div class="card pt-4">
    <div class="card-body">
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
  <!-- update.blade.php -->
        <form method="POST" action="{{ url('company/' .$company->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-3 mb-4">
                    <input type="text" class="form-control" id="name" name="name" value="{{ $company->name }}" placeholder="Please enter Company Name" required>
                    <label class="form-element-label" for="name">Company Name</label>
                    @error('name')
                        <span>{{ $message }}</span>
                    @enderror
                </div>
            
                <div  class="col-lg-3 mb-4">
                    <input type="email" class="form-control" id="email" name="email" value="{{ $company->email }}" placeholder="Please enter valid email address" required>
                    <label class="form-element-label" for="email">Email</label>
                    @error('email')
                        <span>{{ $message }}</span>
                    @enderror
                </div>
            
                <div class="col-lg-3 mb-4">
                    <select id="envtype" class="form-control" name="envtype" required>
                        <option value="production" {{ $company->envtype }}>Production</option>
                        {{-- <option value="preproduction" {{ $company->envtype }}>Pre-production</option> --}}
                    </select>
                    <label class="form-element-label" for="envtype">Environment Type</label>
                    @error('envtype')
                        <span>{{ $message }}</span>
                    @enderror
                </div>
            
            
                <div  class="col-lg-1 mb-2 credit">
                    <input type="number" class="form-control" id="max_count" value="{{ $company->max_count }}" placeholder="" disabled required>
                    <label class="form-element-label" for="max_count">Credits</label>
                    @error('email')
                        <span>{{ $message }}</span>
                    @enderror
                </div>
                <div  class="col-lg-2 mb-2 credit">
                    <label class="form-element-label" for="max_count"></label>
                    <input type="number" class="form-control" id="add_more_credit" name="add_more_credit"  placeholder="" style="display: none;">
                    <a href="#" class="add-more-credits-btn"><i class="bi bi-plus-lg"></i> Add More Credits</a>
                    @error('email')
                        <span>{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <hr>
            <div id="modulesContainer">
                @foreach($modules as $key => $module)
                    <div id="modulesCreate"  class="row">
                        <div class="col-11">
                            <div class="row">
                                <div  class="col-lg-4 mb-4">
                                    <select name="module[]" class="form-control module-select" required>
                                        <option value="">Select Module</option>
                                        @foreach($moduleMaster as $moduleOption)
                                        <option value="{{ $moduleOption->api_name }}" {{ $module->apiname == $moduleOption->api_name ? 'selected' : '' }}>
                                            {{ strtoupper($moduleOption->api_name) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label class="form-element-label" for="module">Module</label>
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <select name="primary_vendor[]" class="form-control primary-vendor-select" required>
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors[$module->apiname] as $vendor)
                                        <option value="{{ $vendor->vender }}" {{ $module->vendorname == $vendor->vender ? 'selected' : '' }}>
                                            {{ strtoupper($vendor->vender) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label class="form-element-label" for="primary_vendor">Primary Vendor</label>
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <select name="secondary_vendor[]" class="form-control secondary-vendor-select">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors[$module->apiname] as $vendor)
                                        <option value="{{ $vendor->vender }}" {{ $module->sec_vendor == $vendor->vender ? 'selected' : '' }}>
                                            {{ strtoupper($vendor->vender) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label class="form-element-label" for="secondary_vendor">Secondary Vendor</label>
                                </div>
                            </div>
                        </div>
                        <div class="col text-center {{ $key == 0 ? "d-none" : ""}}">
                            <input type="hidden" value="{{$module->id}}" name="isExist[]" class="hidden isExist">
                            <button id="closeRow" class="closet-btn-set" type="button"><i class="bi bi-dash"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div>
                <button class="btn btn-outline-primary plus-btn" type="button">
                    <i class="bi bi-plus-lg"></i> Add More
                </button>
            </div>
            <hr>
            
                <div class="col-lg-6 mb-4 expirydate">
                    <input type="text" id="datepicker" class="form-control datepicker" name="date">
                    <label class="form-element-label" for="date">Expiry Date</label>
                </div>
            
                <div  class="col-lg-6 mb-4">
                    <select id="status" class="form-control" name="status" required>
                        <option value="1" {{ $company->status == 1 ? 'selected' : '' }}>Active</option>
                        <option value="2" {{ $company->status == 2 ? 'selected' : '' }}>Inactive</option>
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

        var value = $('#envtype').val();
            if(value == 'preproduction'){
                $('.expirydate').css('display','block');
                $("#datepicker").attr("required", true);
            }

        $('#max_count').keypress(function(event) {
            var inputValue = event.key;
            
            // Check if the input value is "-"
            if (inputValue === '-') {
                event.preventDefault(); // Prevent the "-" character from being entered
            }
        });

        $('#envtype').change(function(event) {
            var value = $('#envtype').val();
            if(value == 'preproduction'){
                $('.expirydate').css('display','block');
                $("#datepicker").attr("required", true);
                var set_val = "<?php echo $company->expirydate; ?>";
                $("#datepicker").val(set_val);

            }
            else if(value == 'production')
            {
                $('.expirydate').css('display','none');
                $("#datepicker").attr("required", false);
                $("#datepicker").val(null);
                $("#datepicker").removeAttr("value");
            }
            else{
                $('.expirydate').css('display','none');
                $("#datepicker").attr("required", false);
                $("#datepicker").val(null);
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
                var options = "<option value=''>Select Module</option>";
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

        // Fetch secondary vendors for the selected module and primary vendor
        if (moduleId !== "" && primaryVendorId !== "") {
            fetchSecondaryVendors(moduleId, primaryVendorId, secondaryVendorSelect);
        }
    });

    //Remove Element
    $(document).on('click', '.closet-btn-set', function () {
       // alert($(this).siblings(".hidden").hasClass('isExist'));
        if($(this).siblings(".hidden").hasClass('isExist') === true)
        {
            var moduleID = $(this).siblings(".isExist").val();
           // alert(moduleID);
            removeExistingModule(moduleID);
            $(this).parents("#modulesCreate").remove();
        }
        else{
            $(this).parents("#modulesCreate").remove();
        }
    });

    function removeExistingModule(id)
    {
        $.ajax({
            url: "{{ route('company.remove_modules') }}",
            type: "GET", 
            data: {
                id: id
            },
            dataType: "json",
            success: function (response) {
                
            }
        });
    }

    //  Add more 

    let count = {{count($modules)}};
    //alert(count);
    $(".plus-btn").click( function(){
    console.log($(this));
        if(count < 9){
            var cloneElement = $("#modulesCreate").clone();
            // if(cloneElement.hasClass('first_element')){
            //   cloneElement.removeClass('first_element');
            // }
            $("#modulesContainer").append(cloneElement);
            // getModule();
            cloneElement.find(".form-element-field").removeClass("-hasvalue").val("");
            
            cloneElement.find(".hidden").val("");
            cloneElement.find(".hidden").removeClass("isExist");

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
<script>
    $(".add-more-credits-btn").click(function(){
        $("#add_more_credit").show();
        $(this).hide();
    });
</script>
</main>
 
@endsection
