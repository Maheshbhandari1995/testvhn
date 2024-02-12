
<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>AmyGB.ai</title>
		<link rel="icon" type="image/x-icon" href="assets/img/favicon.png">

        <!-- Bootstrap Core CSS -->
        <link  rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}"/>
        <link  rel="stylesheet" href="{{asset('assets/css/bootstrap-icons.min.css')}}"/>
        <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"> -->

        <!-- MetisMenu CSS -->
        <link href="{{asset('assets/js/metisMenu/metisMenu.min.css')}}" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="{{asset('assets/css/sb-admin-2.css')}}" rel="stylesheet">
        <!-- Custom Fonts -->
        <link href="{{asset('assets/fonts/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css">
        {{-- <link rel="stylesheet" href="{{asset('assets/fonts/font-awesome/css/jquery.dataTables.min.css')}}"> --}}
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
            <![endif]-->
            <link rel="stylesheet" type="text/css" href="{{asset('assets/css/jquery.dataTables.min.css')}}">
            <link rel="stylesheet" type="text/css" href="{{asset('assets/css/buttons.dataTables.min.css')}}">
            <link rel="stylesheet" href="{{asset('assets/css/jquery-ui.css')}}">
            <link href="{{asset('assets/css/work-sans-fonts.css')}}" rel="stylesheet" type="text/css">
            <link href="{{asset('assets/css/style.css')}}" rel="stylesheet" type="text/css">
            <link href="{{asset('assets/css/login.css')}}" rel="stylesheet" type="text/css">

            
            <script src="{{asset('assets/js/jquery-3.6.0.min.js')}}"></script>
        {{-- <script src="{{asset('assets/js/jquery.min.js')}}" type="text/javascript"></script> --}}
        <script src="{{asset('assets/js/sweetalert2.all.min.js')}}"></script>
        
        <script src="{{asset('assets/js/jquery-ui.js')}}"></script>
        <!-- DataTables CSS -->
    
        <script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('assets/js/dataTables.buttons.min.js')}}"></script>
        <script src="{{asset('assets/js/buttons.html5.min.js')}}"></script>
        <script src="{{asset('assets/js/buttons.print.min.js')}}"></script>
        <script src="{{asset('assets/js/customJS.js')}}"></script>
    
    </head>

    <body  data-bs-spy="scroll" data-bs-target=".header">



        <div id="wrapper">
            @if(session()->has('data'))
            <?php
               // echo "<pre>"; print_r(session('data'));


            ?>
            <?php
                 $url = parse_url($_SERVER['REQUEST_URI']);
                 $URLfilename = explode('/', $url['path']);
                 $fileName = end($URLfilename);
                //echo "<pre>"; print_r($_SESSION);
            ?>
            <!-- Navigation -->


            <div class="content">

            <!--success / error-->
                @if(Session::get('success'))
                    <?php $message = Session::get('success') ?>
                    <?php echo '<script>swal.fire({text:"'. $message .'",icon:"success",timer:3000,showConfirmButton:false});</script>' ?>
                @endif
                
                @if(Session::get('error'))
                    <?php $message = Session::get('error') ?>
                    <?php echo '<script>swal.fire({text:"'. $message .'",icon:"error",timer:3000,showConfirmButton:false});</script>' ?>
                @endif
            </div>
                <header id="header" class="header fixed-top d-flex align-items-center">

                    <div class="d-flex align-items-center justify-content-between">
                    <!-- <a href="{{asset('login')}}" class="logo d-flex align-items-center">
                        <img src="{{asset('assets/img/edas-logo-light.png')}}" alt="Edas Logo">
                        {{-- <span class="d-none d-lg-block">Vahan</span> --}}
                    </a> -->
                    <!-- <i class="bi bi-list toggle-sidebar-btn"></i> -->
                    <a href="#" class="toggle-sidebar-btn">
                            <svg width="25" height="20" viewBox="0 0 25 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="menu">
                                <g id="&#240;&#159;&#166;&#134; icon &#34;menu scale&#34;">
                                <path id="Vector" d="M1 1H11.2857" stroke="black" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"/>
                                <path id="Vector_2" d="M1 10H17.7143" stroke="black" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"/>
                                <path id="Vector_3" d="M1 19H24.1429" stroke="black" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"/>
                                </g>
                                </g>
                            </svg>
                        </a>
                    </div><!-- End Logo -->

                    <!-- <div class="search-bar">
                    <form class="search-form d-flex align-items-center" method="POST" action="#">
                        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
                        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
                    </form>
                    </div> -->
                    
                    <!-- End Search Bar -->

                    <nav class="header-nav ms-auto">
                    <ul class="d-flex align-items-center">
                        {{-- <li>
                            <a class="dropdown-item " href="">
                                <div class="box-header">
                                    <div>
                                        <!-- <i class="bi bi-credit-card-2-back-fill"></i> -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="26" viewBox="1400.569 87 32.918 32.919"><g data-name="Group 37"><path d="M1417.018 119.919c-4.488 0-8.977.002-13.465-.002-1.406 0-2.54-.85-2.879-2.156-.069-.266-.1-.55-.1-.825-.006-5.013-.007-10.026-.003-15.04.002-1.71 1.241-2.96 2.955-2.976 1.254-.013 2.507-.008 3.76.002a.75.75 0 0 0 .59-.24c3.563-3.573 7.13-7.14 10.7-10.706 1.14-1.14 2.689-1.295 3.898-.394.17.127.326.277.477.428 3.167 3.165 6.328 6.337 9.503 9.494.902.897 1.245 1.918.884 3.154a.573.573 0 0 0-.01.215c.05.476.15.952.15 1.428.012 4.874.009 9.748.006 14.622 0 1.412-.834 2.54-2.145 2.886-.306.08-.634.105-.952.105-4.457.007-8.913.005-13.369.005Zm.007-1.93h13.368c.797 0 1.162-.362 1.162-1.153v-14.814c0-.816-.357-1.174-1.173-1.174h-26.705c-.819 0-1.177.357-1.177 1.17V116.8c0 .837.351 1.189 1.19 1.189h13.335Zm-6.632-19.091c.13.01.193.018.256.018 3.148.001 6.296.004 9.444-.008.146 0 .324-.098.432-.205 1.552-1.537 3.094-3.085 4.64-4.627.181-.18.379-.345.577-.525-.103-.113-.165-.185-.232-.252a5274.477 5274.477 0 0 0-3.952-3.953c-.56-.56-1.07-.555-1.635.01-3.082 3.08-6.164 6.162-9.245 9.244-.082.082-.16.166-.285.298Zm12.74.017c.871 0 1.714.006 2.557-.009.095-.001.201-.107.28-.185.716-.709 1.424-1.425 2.142-2.13.112-.11.267-.178.379-.25l-1.39-1.392-3.967 3.966Zm7.887 0-1.209-1.206-1.211 1.205h2.42Z" fill="#212429" fill-rule="evenodd" data-name="Path 58"/><path d="M1407.687 109.205c-.513 0-1.027.005-1.54-.002-1.068-.013-1.717-.668-1.72-1.729-.001-.792-.003-1.584 0-2.376.006-.991.68-1.67 1.676-1.679 1.049-.008 2.098-.007 3.146 0 1.005.007 1.674.672 1.682 1.668.007.824.008 1.648 0 2.472-.01.954-.652 1.612-1.607 1.64-.545.016-1.091.003-1.637.003v.003Zm-1.312-1.954h2.607v-1.88h-2.607v1.88Z" fill="#212429" fill-rule="evenodd" data-name="Path 59"/><path d="M1408.843 113.167v1.885h-4.464v-1.885h4.464Z" fill="#212429" fill-rule="evenodd" data-name="Path 60"/><path d="M1415.794 113.171v1.878h-4.468v-1.878h4.468Z" fill="#212429" fill-rule="evenodd" data-name="Path 61"/><path d="M1422.739 113.172v1.877h-4.468v-1.877h4.468Z" fill="#212429" fill-rule="evenodd" data-name="Path 62"/><path d="M1425.206 115.05v-1.88h4.468v1.88h-4.468Z" fill="#212429" fill-rule="evenodd" data-name="Path 63"/></g></svg>
                                    </div>
                                    <div>
                                        <h5>Credit</h5>
                                        <p>Rs.9000</p>
                                    </div>
                                </div>
                         
                                
                            </a>
                        </li> --}}
                        @if(session('data.userRole') == 'user' || session('data.userRole') == 'admin')
                        <li>
                            <a class="dropdown-item">
                                <div class="box-header">
                                    <div class="pe-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="26" viewBox="1400.569 87 32.918 32.919"><g data-name="Group 37"><path d="M1417.018 119.919c-4.488 0-8.977.002-13.465-.002-1.406 0-2.54-.85-2.879-2.156-.069-.266-.1-.55-.1-.825-.006-5.013-.007-10.026-.003-15.04.002-1.71 1.241-2.96 2.955-2.976 1.254-.013 2.507-.008 3.76.002a.75.75 0 0 0 .59-.24c3.563-3.573 7.13-7.14 10.7-10.706 1.14-1.14 2.689-1.295 3.898-.394.17.127.326.277.477.428 3.167 3.165 6.328 6.337 9.503 9.494.902.897 1.245 1.918.884 3.154a.573.573 0 0 0-.01.215c.05.476.15.952.15 1.428.012 4.874.009 9.748.006 14.622 0 1.412-.834 2.54-2.145 2.886-.306.08-.634.105-.952.105-4.457.007-8.913.005-13.369.005Zm.007-1.93h13.368c.797 0 1.162-.362 1.162-1.153v-14.814c0-.816-.357-1.174-1.173-1.174h-26.705c-.819 0-1.177.357-1.177 1.17V116.8c0 .837.351 1.189 1.19 1.189h13.335Zm-6.632-19.091c.13.01.193.018.256.018 3.148.001 6.296.004 9.444-.008.146 0 .324-.098.432-.205 1.552-1.537 3.094-3.085 4.64-4.627.181-.18.379-.345.577-.525-.103-.113-.165-.185-.232-.252a5274.477 5274.477 0 0 0-3.952-3.953c-.56-.56-1.07-.555-1.635.01-3.082 3.08-6.164 6.162-9.245 9.244-.082.082-.16.166-.285.298Zm12.74.017c.871 0 1.714.006 2.557-.009.095-.001.201-.107.28-.185.716-.709 1.424-1.425 2.142-2.13.112-.11.267-.178.379-.25l-1.39-1.392-3.967 3.966Zm7.887 0-1.209-1.206-1.211 1.205h2.42Z" fill="#212429" fill-rule="evenodd" data-name="Path 58"/><path d="M1407.687 109.205c-.513 0-1.027.005-1.54-.002-1.068-.013-1.717-.668-1.72-1.729-.001-.792-.003-1.584 0-2.376.006-.991.68-1.67 1.676-1.679 1.049-.008 2.098-.007 3.146 0 1.005.007 1.674.672 1.682 1.668.007.824.008 1.648 0 2.472-.01.954-.652 1.612-1.607 1.64-.545.016-1.091.003-1.637.003v.003Zm-1.312-1.954h2.607v-1.88h-2.607v1.88Z" fill="#212429" fill-rule="evenodd" data-name="Path 59"/><path d="M1408.843 113.167v1.885h-4.464v-1.885h4.464Z" fill="#212429" fill-rule="evenodd" data-name="Path 60"/><path d="M1415.794 113.171v1.878h-4.468v-1.878h4.468Z" fill="#212429" fill-rule="evenodd" data-name="Path 61"/><path d="M1422.739 113.172v1.877h-4.468v-1.877h4.468Z" fill="#212429" fill-rule="evenodd" data-name="Path 62"/><path d="M1425.206 115.05v-1.88h4.468v1.88h-4.468Z" fill="#212429" fill-rule="evenodd" data-name="Path 63"/></g></svg>
                                    </div>
                                    <div>
                                        <h5>Credit</h5>
                                        <p>
                                            <span id="availableBalance"> 0.00</span>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>

                        <li class="dropdown notification-btn notify-defult">
                            <a class="" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" viewBox="-1333.124 516.182 31.646 34.405"><path d="M-1317.274 550.586c-2.197.01-4.163-1.47-4.672-3.62-.137-.576-.39-.664-.897-.697-2.681-.175-5.347-.474-7.95-1.187-2.264-.62-3.036-2.855-1.613-4.735 1.724-2.277 3.341-4.633 4.109-7.408.436-1.577.525-3.25.76-4.882.558-3.875 2.624-6.629 6.17-8.26.43-.198.635-.415.727-.9.302-1.59 1.769-2.73 3.38-2.715 1.605.015 3.016 1.186 3.322 2.792.067.352.156.58.532.746 3.83 1.698 6.16 4.648 6.348 8.817.2 4.4 2.046 7.987 4.545 11.392.328.447.68.91.864 1.423.567 1.567-.32 3.296-1.98 3.662-2.115.467-4.263.778-6.402 1.126-.638.104-1.292.131-1.94.143-.414.008-.555.159-.65.566-.514 2.206-2.435 3.728-4.653 3.737Zm-.04-5.393c2.768-.178 5.543-.285 8.302-.561 1.66-.166 3.305-.554 4.94-.921 1.14-.257 1.532-1.383.895-2.364-.206-.319-.456-.609-.68-.916-2.527-3.473-4.382-7.164-4.564-11.61-.162-3.975-2.363-6.643-6.076-8.04-.536-.2-.84-.463-.771-1.069a1.8 1.8 0 0 0-.062-.664c-.254-.926-1.216-1.58-2.146-1.48-1.044.113-1.883.93-1.842 1.959.032.789-.291 1.164-.999 1.328a2.04 2.04 0 0 0-.376.141c-3.626 1.65-5.448 4.5-5.561 8.451-.07 2.426-.726 4.695-1.9 6.783-.96 1.71-2.092 3.327-3.18 4.964-.737 1.108-.377 2.2.892 2.548.345.095.69.187 1.04.266 3.979.892 8.024 1.04 12.087 1.185Zm-3.298 1.247c.354 1.669 1.773 2.818 3.368 2.772 1.636-.046 3.012-1.211 3.248-2.772h-6.616Z" fill="#212429" fill-rule="evenodd" data-name="Path 360"/></svg>
                            <span class="notification-number"></span>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow noty-dropdown">

                                <div class="list-group">
                                    
                                    <a href="#" class="list-group-item list-group-item-action d-flex gap-1 py-3" aria-current="true">
                                    <div class="d-flex gap-1 w-100 justify-content-between">
                                        <div>
                                     
                                        <p class="mb-0 opacity-75">Subject</p>
                                        </div>
                                    </div>
                                    </a>
                                </div>
                            </ul>
                        </li>
                       
                        @endif
                        <li class="nav-item dropdown manage-profile">

                        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                            <!-- <img src="{{asset('assets/img/profile-img.jpg')}}" alt="Profile" class="rounded-circle"> -->
                            <!-- <i class="bi bi-person user-icon"></i> -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" viewBox="-1818.089 773.88 40.885 40.898"><path d="M-1777.203 794.34c-.019 11.325-9.178 20.451-20.513 20.437-11.285-.013-20.406-9.226-20.373-20.578.034-11.243 9.236-20.342 20.55-20.32 11.187.023 20.354 9.247 20.336 20.461Zm-24.198 1c-2.952-2.525-3.681-3.552-4.059-5.738-.438-2.536.21-4.782 1.908-6.694 2.983-3.358 8.138-3.549 11.387-.443 1.58 1.51 2.427 3.379 2.455 5.56.041 3.216-1.483 5.59-4.244 7.288 5.863 2.026 9.22 6.034 10.174 12.066 4.253-4.073 6.95-12.007 4.09-19.721-2.926-7.887-10.457-12.814-19.073-12.47-7.975.32-15.179 6.173-17.353 14.052-2.048 7.42.921 14.8 4.611 18.096.96-5.992 4.298-9.986 10.104-11.996Zm3.733 18.15c4.55-.073 8.607-1.472 12.116-4.367.248-.205.48-.614.47-.92-.212-6.579-5.69-11.951-12.272-12.073-6.849-.126-12.448 5.077-12.849 11.978-.019.33.195.788.454 1.002 3.504 2.902 7.562 4.302 12.081 4.38Zm.015-18.644c3.674.008 6.656-2.96 6.653-6.625-.002-3.664-2.98-6.652-6.639-6.663-3.645-.01-6.65 2.994-6.653 6.649a6.624 6.624 0 0 0 6.639 6.639Z" fill="#212429" fill-rule="evenodd" data-name="Path 359"/></svg>
                            <span class="d-none d-md-block dropdown-toggle ps-2">{{Str::ucfirst(session('data.Name'))}}</span>
                        </a><!-- End Profile Iamge Icon -->

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                            <li class="dropdown-header">
                                <h6>{{session('data.userRole')}}</i></h6>
                            </li>
                            {{-- <li>
                                <i class="bi bi-award" title="Email ID"> Email Id : </i> <span>sdf{{session('data.email')}}</span>
                            </li>
                            <li>
                                <i class="bi bi-award" title="Last login"> Last Login At:</i> <span>sdf{{session('data.email')}}</span>
                            </li> --}}
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{asset('password/reset')}}">
                                    <!-- <i class="bi bi-gear"></i> -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16.749" height="16.743" viewBox="1692.12 182 16.749 16.743"><g data-name="Group 239"><path d="M1700.505 198.741c-2.067 0-4.134.002-6.201 0-1.367-.002-2.178-.816-2.182-2.185-.002-.971-.005-1.943.002-2.914.009-1.226.843-2.073 2.064-2.074 4.208-.006 8.417-.006 12.626 0 1.199.002 2.039.85 2.049 2.051.008.996.005 1.993.001 2.989-.006 1.297-.83 2.13-2.12 2.132-2.08.005-4.16.001-6.239.001Zm-4.403-4.293c0-.347-.005-.606 0-.864.007-.286-.083-.514-.402-.508-.297.004-.388.221-.382.492.006.268.001.535.001.874l-.759-.467c-.218-.135-.44-.154-.591.076-.157.24-.072.45.168.598.25.154.5.311.804.502-.299.182-.55.335-.802.486-.232.14-.36.333-.21.589.15.254.379.242.615.1.238-.145.48-.284.776-.458v.923c0 .265.124.444.395.44.272-.003.388-.187.386-.45v-.914c.296.18.526.32.755.461.23.142.462.173.62-.08.153-.247.042-.448-.193-.593-.25-.154-.498-.312-.793-.498.305-.188.566-.348.826-.51.222-.14.313-.338.173-.57-.14-.233-.356-.247-.583-.111-.244.145-.487.292-.804.482Zm4.001-.008c-.286-.175-.501-.298-.708-.433-.236-.154-.479-.216-.65.056-.166.264-.03.468.218.615.24.143.476.295.763.473l-.655.397c-.401.242-.52.46-.352.69.239.329.495.157.748.003.194-.118.393-.226.636-.364 0 .346.002.619 0 .892-.004.26.102.454.373.462.293.01.411-.186.409-.465-.002-.278 0-.557 0-.9.272.167.473.289.673.41.346.209.595.203.712-.037.15-.309-.03-.486-.28-.633-.23-.134-.452-.282-.724-.452.288-.174.52-.316.753-.454.25-.148.43-.342.243-.635-.174-.273-.415-.203-.651-.054-.222.14-.453.265-.728.424 0-.368.002-.654 0-.94-.002-.25-.129-.415-.377-.42-.271-.006-.4.167-.402.433-.002.283-.001.566-.001.932Zm5.565.01c0-.363-.002-.635 0-.907.003-.278-.111-.475-.405-.468-.272.007-.38.198-.378.46.003.279 0 .558 0 .907l-.761-.468c-.22-.135-.442-.152-.59.08-.156.24-.068.449.173.597.25.153.498.311.8.5-.301.183-.552.337-.804.488-.234.14-.357.336-.205.59.152.253.38.238.615.095.238-.144.48-.283.772-.455v.665c0 .515.115.731.4.7.456-.051.377-.414.383-.718.003-.193 0-.387 0-.649.298.182.528.321.758.463.23.142.463.169.618-.085.152-.248.038-.448-.198-.592-.25-.153-.497-.312-.79-.497.307-.19.57-.35.83-.512.221-.14.31-.34.167-.57-.143-.232-.359-.242-.584-.106-.243.145-.486.292-.801.482Z" fill="#656668" fill-rule="evenodd" data-name="Path 247"/><path d="M1694.157 190.742c0-.708-.074-1.399.017-2.068.164-1.215 1.22-2.148 2.436-2.27.085-.01.17-.02.31-.034-.133-1.414.197-2.66 1.335-3.58a3.44 3.44 0 0 1 2.429-.783c1.092.063 1.977.543 2.654 1.4.681.862.81 1.873.733 2.943.246.043.465.069.68.122a2.746 2.746 0 0 1 2.108 2.58c.019.556.003 1.113.003 1.69h-12.705Zm9.09-4.39c.148-1.285-.12-2.391-1.257-3.11-.98-.62-2.014-.62-2.993 0-1.136.719-1.408 1.825-1.257 3.11h5.508Z" fill="#656668" fill-rule="evenodd" data-name="Path 248"/></g></svg>
                                    <span>Reset Password</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{asset('signout')}}">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Sign Out</span>
                                </a>
                            </li>

                        </ul><!-- End Profile Dropdown Items -->
                        </li><!-- End Profile Nav -->

                    </ul>
                    </nav><!-- End Icons Navigation -->

                </header>
                <aside id="sidebar" class="sidebar">

                    <ul class="sidebar-nav" id="sidebar-nav">
                        <a href="{{asset('login')}}" class="logo d-flex justify-content-center">
                            <img src="{{asset('assets/img/edas-logo-light.png')}}" alt="Edas Logo">
                            <!-- <span class="d-none d-lg-block">Vahan</span> -->
                        </a>
                  <?php

                    //  $sessionData = session('data');
                    //  print_r($sessionData);//die;
                    ?>
                    @if(session('data.userRole') == 'user')
                        <li class="nav-item">
                            <a class="nav-link <?php if($fileName == 'dashboard'){ echo "";} else { echo "collapsed";}?>" href="{{asset('dashboard')}}">
                                <i class="bi bi-receipt"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                       <?php
                       $apiArr = session('data.api_list');
                       //echo "<pre>";print_r($apiArr);
                        
                        if(!empty($apiArr))
                        {
                            $viewFilenames = array_values(array_column($apiArr, 'view_filename'));
                             ?>
                            <li class="nav-item">
                                <a class="nav-link <?php if(in_array($fileName, $viewFilenames)){ echo '';} else { echo 'collapsed';} ?>" data-bs-target="#Module-nav" data-bs-toggle="collapse" href="#">
                                    <i class="bi bi-award"></i><span>Module</span><i class="bi bi-chevron-down ms-auto"></i>
                                </a>
                                <ul id="Module-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                <?php foreach($apiArr as $k => $val) { ?>
                                    <li>
                                        <a href="{{asset($val['view_filename'])}}">
                                            <i class="bi bi-circle"></i><span>{{strtoupper($val['api_alias'])}}</span>
                                        </a>
                                    </li>
                                <?php } ?>
                            
                                </ul>
                            </li>
                        <?php  }  ?>
                     {{-- @if(!in_array(session('data.Client_id'), []))
                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#Report-nav" data-bs-toggle="collapse" href="#">
                                <i class="bi bi-award"></i><span>Bulk Upload</span><i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <ul id="Report-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                <li class="nav-item">
                                    <a class="nav-link collapsed" href="{{asset('rc_bulk_upload')}}">
                                        <i class="bi bi-upload"></i>
                                    <span>RC Bulk</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link collapsed" href="{{asset('rcbulkreport')}}">
                                    <i class="bi bi-receipt"></i>
                                    <span>RC Bulk List</span>
                                    </a>
                                </li> --}}

                                {{-- <li class="nav-item">
                                    <a class="nav-link collapsed" href="{{asset('rc_bulk_upload_logic')}}">
                                        <i class="bi bi-upload"></i>
                                    <span>RC Logic </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link collapsed" href="{{asset('rcbulkreport_logic')}}">
                                    <i class="bi bi-receipt"></i>
                                    <span>RC Logic Uploaded List</span>
                                    </a>
                                </li> --}}
                            {{-- </ul>
                        </li>
                        @endif --}}
                    <!-- End RC Nav -->
                    @endif 
                    @if(session('data.userRole') == 'super_admin')

                        {{-- <li class="nav-item">
                            <a class="nav-link <?php if($fileName == 'dashboard'){ echo "";} else { echo "collapsed";}?>" href="{{asset('dashboard')}}">
                                <i class="bi bi-layout-sidebar"></i>
                                <span>Dashboard</span>
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#Dashboard-nav" data-bs-toggle="collapse" href="#">
                                <i class="bi bi-layout-sidebar"></i><span>Dashboard</span><i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <ul id="Dashboard-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                <li>
                                    <a href="{{asset('dashboard')}}">
                                    <i class="bi bi-circle"></i><span>Dashboard</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('/bi_dashboard') }}">
                                        <i class="bi bi-circle"></i>
                                        <span>BI Dashboard</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#Organization-nav" data-bs-toggle="collapse" href="#">
                                <i class="bi bi-receipt"></i><span>Organization</span><i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <ul id="Organization-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                <li>
                                    <a href="{{asset('company')}}">
                                        <i class="bi bi-circle"></i><span>Organization List</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('/company/create') }}">
                                    <i class="bi bi-circle"></i><span>Add New</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#User-nav" data-bs-toggle="collapse" href="#">
                                <i class="bi bi-award"></i><span>User</span><i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <ul id="User-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                <li>
                                    <a href="{{asset('users')}}">
                                    <i class="bi bi-circle"></i><span>User List</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('users.create') }}">
                                    <i class="bi bi-circle"></i><span>Add New</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#Module-nav" data-bs-toggle="collapse" href="#">
                                <i class="bi bi-person-vcard"></i><span>Module</span><i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <ul id="Module-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                <li>
                                    <a href="{{asset('module')}}">
                                    <i class="bi bi-circle"></i><span>Module List</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('module.create') }}">
                                    <i class="bi bi-circle"></i><span>Add New</span>
                                    </a>
                                </li>
                            </ul>
                        </li> --}}
                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#Report-nav" data-bs-toggle="collapse" href="#">
                                <i class="bi bi-file-earmark-text"></i><span>Report</span><i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <ul id="Report-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                <li>
                                    <a href="{{asset('report')}}">
                                    <i class="bi bi-circle"></i><span>Login Activity</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{asset('dashboardreport')}}">
                                    <i class="bi bi-circle"></i><span>Module Activity</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{asset('rcbulkreport')}}">
                                    <i class="bi bi-circle"></i>
                                    <span>RC-Bulk List</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#BillReport-nav" data-bs-toggle="collapse" href="#">
                                <i class="bi bi-file-earmark-text"></i><span>Billing Report</span><i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <ul id="BillReport-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                
                                <li>
                                    <a href="{{asset('summarybillingreport')}}">
                                    <i class="bi bi-circle"></i><span>Summary Report</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{asset('billingreport')}}">
                                    <i class="bi bi-circle"></i><span>Module Report</span>
                                    </a>
                                </li>
                                
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#vendorBillReport-nav" data-bs-toggle="collapse" href="#">
                                <i class="bi bi-file-earmark-text"></i><span>Vendor Billing Report</span><i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <ul id="vendorBillReport-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                
                                <li>
                                    <a href="{{asset('vendorbillingreport')}}">
                                    <i class="bi bi-circle"></i><span>Vendor billing Report</span>
                                    </a>
                                </li>
                                
                            </ul>
                        </li>
                    @endif

                    @if(session('data.userRole') == 'mis')
                    <li class="nav-item">
                        <a class="nav-link collapsed" data-bs-target="#BillReport-nav" data-bs-toggle="collapse" href="#">
                            <i class="bi bi-file-earmark-text"></i><span>Mis Report</span><i class="bi bi-chevron-down ms-auto"></i>
                        </a>
                        <ul id="BillReport-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                            <li>
                                <a href="{{asset('apisummaryreport')}}">
                                <i class="bi bi-circle"></i><span>Api Summary Report</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{asset('summarybillingreport')}}">
                                <i class="bi bi-circle"></i><span>Summary Report</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{asset('vendorbillingreport')}}">
                                <i class="bi bi-circle"></i><span>Vendor billing Report</span>
                                </a>
                            </li>
                            
                        </ul>
                    </li>
                    @endif

                    @if(session('data.userRole') == 'admin')

                        <li class="nav-item">
                            <a class="nav-link <?php if($fileName == 'dashboard'){ echo "";} else { echo "collapsed";}?>" href="{{asset('dashboard')}}">
                            <i class="bi bi-layout-sidebar"></i>
                            <span>Dashboard</span>
                            </a>
                        </li>
						@if(!in_array(session('data.Client_id'), [20]))
                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#User-nav" data-bs-toggle="collapse" href="#">
                                <i class="bi bi-award"></i><span>User</span><i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <ul id="User-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                <li>
                                    <a href="{{asset('users')}}">
                                    <i class="bi bi-circle"></i><span>User List</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('users.create') }}">
                                    <i class="bi bi-circle"></i><span>Add New</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
						@endif
                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-target="#Report-nav" data-bs-toggle="collapse" href="#">
                                <i class="bi bi-file-earmark-text"></i><span>Report</span><i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <ul id="Report-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                                <li>
                                    <a href="{{asset('report')}}">
                                    <i class="bi bi-circle"></i><span>Login Activity Log</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{asset('dashboardreport')}}">
                                    <i class="bi bi-circle"></i><span>Module Activity log</span>
                                    </a>
                                </li>
                                @if(in_array(session('data.Client_id'), [19]))
                                <li>
                                    <a href="{{asset('apireport')}}">
                                    <i class="bi bi-circle"></i><span>Api Report</span>
                                    </a>
                                </li>
                                @endif
                                <li>
                                    <a href="{{asset('rcbulkreport')}}">
                                    <i class="bi bi-circle"></i>
                                    <span>RC-Bulk List</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    
                </ul>
                </aside>
                {{-- <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0"> --}}
                    <!-- <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="">Administrator</a>
                    </div> -->
                    <!-- /.navbar-header -->

                    <!-- <ul class="nav navbar-top-links navbar-right">
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-user">
                                <li><a href="#"><i class="fa fa-user fa-fw"></i> User Profile</a>
                                </li>
                                <li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
                                </li>
                                <li class="divider"></li>
                                <li><a href="logout.blade.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                                </li>
                            </ul>
                        </li>
                    </ul> -->
                    <!-- /.navbar-top-links -->

                    <!-- <div class="navbar-default sidebar" role="navigation">
                        <div class="sidebar-nav navbar-collapse">
                            <ul class="nav" id="side-menu">
                               <li>
                                    <a href="index.blade.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                                </li>
                                <li>
                                    <a href="rc_details.blade.php"><i class="fa fa-users fa-fw"></i> RC Details</a>
                                </li>
                                <li>
                                    <a href="challan_details.blade.php"><i class="fa fa-users fa-fw"></i> Challan Details</a>
                                </li>
                            </ul>
                        </div>
                    </div> -->
                    <!-- /.navbar-static-side -->
                {{-- </nav> --}}
            @endif
            <!-- The End of the Header -->
            <div class="content">
                @yield('content')
            </div>

            </div>
    <!-- /#wrapper -->

    <!-- jQuery -->


    <!-- Bootstrap Core JavaScript -->
        <script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
        <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
    <!-- Metis Menu Plugin JavaScript -->
    <script src="{{asset('assets/js/metisMenu/metisMenu.min.js')}}"></script>
    <!-- Custom Theme JavaScript -->
    <script src="{{asset('assets/js/sb-admin-2.js')}}"></script>
    <script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('assets/js/main.js')}}"></script>
    <!-- <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script> -->

    <script>
        $(document).ready(function(){

            var sessionData = @json(session()->all());

            getAvailableBalance();
            
            setInterval(function() {
                getAvailableBalance();
            }, 10000);
            

            getNotifications();
            

                $('.list-group').on('click', 'a.list-group-item', function(e) {
                    e.preventDefault();
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');
                    var notification_id = $(this).data('notification-id');
                    $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                    });
                    // Perform the Ajax call using the notificationId variable
                    $.ajax({
                        url: "{{ route('notification.change')}}",
                        type: 'POST',
                        data: {
                            notification_id: notification_id
                        },
                        success: function(response) {
                            console.log(response);
                        },
                        error: function(xhr) {
                            // Handle the error response
                        }
                    });
                });

        });

        function getAvailableBalance()
        {   
            var sessionData = @json(session()->all());
            
            var userrole = sessionData.data.Role; 
            if(userrole != 'super_admin'){
                $.ajax({
                    url: "{{ route('header.balance')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(availableBalance) {
                        $("#availableBalance").html(availableBalance);
                    }
                });
            }
        }


        function getNotifications() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var sessionData = @json(session()->all());
            
    var userid = sessionData.data.userID; 
    var clientid = sessionData.data.Client_id;

    if (userid != null && clientid != null) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        // Make the Ajax request
        $.ajax({
            url: "{{ route('notification.data') }}",
            type: 'POST',
            data: { userid: userid, clientid: clientid },
            dataType: 'json',
            success: function(response) {
                //console.log(response);
                if (response != null && response.length > 0) { // Check if the response is not empty
                    $('.list-group').empty();

                    $.each(response, function(index, item) {
                        var notificationItem = $('<a>', {
                            class: 'list-group-item list-group-item-action d-flex gap-1 py-3',
                            href: '#',
                            'aria-current': 'true',
                            'data-notification-id': item.id
                        }).appendTo('.list-group');

                        $('.notification-btn').addClass('notify').removeClass('notify-defult');

                        var notificationContent = $('<div>', {
                            class: 'd-flex gap-1 w-100 justify-content-between'
                        }).appendTo(notificationItem);

                        var notificationText = $('<div>').appendTo(notificationContent);

                        $('<p>', {
                            class: 'mb-0 opacity-75 fw-bold',
                            text: item.subject
                        }).appendTo(notificationText);

                        $('<p>', {
                            class: 'mb-0 opacity-75',
                            text: item.created_at
                        }).appendTo(notificationText);
                    });
                } else {
                    $('.list-group').empty(); // Clear the list if the response is empty
                    $('.notification-btn').addClass('notify-defult').removeClass('notify');
                }
            },
            error: function(xhr, status, error) {
                // Handle the error
                console.log('AJAX Error:', error);
            }
        });
    } else {
        $('.list-group').empty(); // Clear the list if either userid or clientid is null
        $('.notification-btn').addClass('notify-defult').removeClass('notify');
    }
}

        // function getNotifications(){
        //     var csrfToken = $('meta[name="csrf-token"]').attr('content');
        //     var sessionData = @json(session()->all());
            
        //     var userid = sessionData.data.userID; 
        //     var clientid = sessionData.data.Client_id;

        //     if(userid != null && clientid != null ){
        //         //console.log(sessionData);
        //         $.ajaxSetup({
        //         headers: {
        //             'X-CSRF-TOKEN': csrfToken
        //         }
        //         });
        //         // Make the Ajax request
        //         $.ajax({
        //             url: "{{ route('notification.data') }}",
        //             type: 'POST',
        //             data: { userid : userid ,clientid : clientid },
        //             dataType: 'json',
        //             success: function(response) {
        //                 console.log(response);
        //             if(response != null || response != undefined){

        //                 $('.list-group').empty();

        //                 $.each(response, function(index, item) {

        //                     var notificationItem = $('<a>', {
        //                         class: 'list-group-item list-group-item-action d-flex gap-1 py-3',
        //                         href: '#',
        //                         'aria-current': 'true',
        //                         'data-notification-id': item.id
        //                     }).appendTo('.list-group');

        //                     $('.notification-btn').addClass('notify').removeClass('notify-defult');

        //                     var notificationContent = $('<div>', {
        //                         class: 'd-flex gap-1 w-100 justify-content-between'
        //                     }).appendTo(notificationItem);

        //                     var notificationText = $('<div>').appendTo(notificationContent);

        //                     $('<p>', {
        //                         class: 'mb-0 opacity-75 fw-bold',
        //                         text: item.subject
        //                     }).appendTo(notificationText);

        //                     $('<p>', {
        //                         class: 'mb-0 opacity-75',
        //                         text: item.created_at
        //                     }).appendTo(notificationText);
                          
        //                 });
        //             }
        //             else if(response == null || response == undefined){
        //                 $('.notification-btn').addClass('notify-defult').removeClass('notify');
        //             }
        //             else{
        //                 $('.notification-btn').addClass('notify-defult').removeClass('notify');
        //             }
        //             },
        //             error: function(xhr, status, error) {
        //                 // Handle the error
        //                 console.log('AJAX Error:', error);
        //             }
        //             });
        //         }
        // }

        setInterval(getNotifications, 5000);
    </script>

</body>

</html>