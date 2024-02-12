@if(session()->has('email'))
  return redirect('/');
@else

@extends('layout')

@section('content')

<main class="bg-fixed">
    <div class="login-container">

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

      <section class="section register d-flex flex-column">
        <div class="container px-0 ">
          <div class="row mx-0 manage-height align-items-center">
            
            <div class="col-lg-4 col-md-4 d-flex flex-column align-items-center px-0">

                <div class="row">
                    <div class="col-md-12">
                      <div class="d-flex justify-content-center">
                        <a href="{{asset('login')}}" class="logo d-flex align-items-center w-auto">
                          <img style="width:240px;" src="assets/img/edas-logo-mn.svg" alt="">
                          <!-- <span class="d-none d-lg-block">Vahan</span> -->
                        </a>
                      </div>
                      <div class="pt-4 pb-2">
                        <h5 class="card-title text-center pb-0 fs-4 text-white">Welcome!</h5>
                        <p class="text-center small text-yellow">Login to Continue</p>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="row justify-content-center">
                        <div class="col-md-5">
                          <form class="form loginform g-3 row" method="POST" action="{{asset('signin')}}">
                          @csrf
                            <div class="col-12">
                              <!-- <label for="yourUsername" class="form-label">Username</label> -->
                              <div class="input-group has-validation">
                                <span class="input-group-text" id="inputGroupPrepend"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" class="form-control" required="required" placeholder="Username">
                                <div class="invalid-feedback">Please enter your username.</div>
                              </div>
                            </div>

                            <div class="col-12">
                              <!-- <label for="yourPassword" class="form-label">Password</label> -->
                              <div class="input-group has-validation">
                                <span class="input-group-text" id="inputGroupPrepend"><i class="bi bi-lock"></i></span>
                                <input type="password" name="passwd" class="form-control" required="required" placeholder="Password">
                                <div class="invalid-feedback">Please enter your password!</div>
                              </div>
                            </div>

                            {{-- <div class="col-12">
                              <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="remember" value="true" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">Remember me</label>
                              </div>
                            </div> --}}
                  
                            <!-- <div class="alert alert-danger alert-dismissable">
                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                              
                            </div> -->
                  
                            <div class="col-12 text-center">
                              <button class="btn btn-primary loginField" type="submit">Login</button>
                            </div>
                            <!-- <div class="col-12">
                              <p class="small mb-0">Don't have account? <a href="pages-register.html">Create an account</a></p>
                            </div> -->
                          </form>
                        </div>
                      </div>
                  </div>
                </div>
           


              <!-- <div class="credits">
                Powered by <a href="https://edas.tech/" target="_blank">eDAS</a> 2023
              </div> -->
            </div>
            <div class="col-8">
                    <div id="carouselExampleIndicators" class="carousel slide  carousel-container d-none d-sm-block" data-bs-ride="carousel">
                      <div class="carousel-indicators">
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                      </div>
                        <div class="carousel-inner">
                          <div class="carousel-item active">
                            <!-- <img src="assets/img/image-slide-01.jpg" class="d-block w-100 h-100vh" alt="..."> -->
                            <div class="carousel-caption position-relative l-0 d-none d-md-flex flex-column">
                              <h5>Discover the New Era of RC Validation</h5>
                              <p>Enable faster decision-making by securely extracting data from RC and validating it to enrich asset portfolios with additional information.</p>
                            </div>
                          </div>
                          <div class="carousel-item">
                            <!-- <img src="assets/img/image-slide-02.jpg" class="d-block w-100 h-100vh" alt="..."> -->
                            <div class="carousel-caption position-relative l-0 d-none d-md-flex flex-column">
                              <h5>The Perfect Blend of Automation & Accuracy </h5>
                              <p>Achieve high accuracy in processing and extracting information from documents, automating manual tasks, saving time, and minimizing errors.</p>
                            </div>
                          </div>
                          <div class="carousel-item">
                            <!-- <img src="assets/img/image-slide-03.jpg" class="d-block w-100 h-100vh" alt="..."> -->
                            <div class="carousel-caption position-relative l-0 d-none d-md-flex flex-column">
                              <h5>Elevating Customer Experience with Each Step</h5>
                              <p>Boost customer experiences and loyalty by optimizing data extraction, streamlining processes, and delivering quicker responses to consumer queries.</p>
                            </div>
                          </div>
                        </div>
                      <!-- <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                      </button>
                      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                      </button> -->
                    </div>
                  </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->
<!-- <div id="page-" class="col-md-4 col-md-offset-4">
	<form class="form loginform" method="POST" action="authenticate.php">
		<div class="login-panel panel panel-default">
			<div class="panel-heading">Please Sign in</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="control-label">username</label>
					<input type="text" name="username" class="form-control" required="required">
				</div>
				<div class="form-group">
					<label class="control-label">password</label>
					<input type="password" name="passwd" class="form-control" required="required">
				</div>
				<div class="checkbox">
					<label>
						<input name="remember" type="checkbox" value="1">Remember Me
					</label>
				</div>
				
				<button type="submit" class="btn btn-success loginField">Login</button>
			</div>
		</div>
	</form>
</div> -->
@endsection
@endif