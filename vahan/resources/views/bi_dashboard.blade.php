
@extends('layout')

@section('content')

<div id="main" class="main">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pagetitle">
                <h1>POWER BI Dashboard</h1>
            </div>
            <div style="display: flex;
            justify-content: center;
            align-items: center;
            height: 400px;">
            <h1>Under Maintenance....</h1>
            </div>
        </div>
    </div>
    @if(session('data.userRole') == 'super_admin')
    <!-- /.row -->
        <iframe src="https://37744:june%402023@vahan-dashboard:8443/Reports/powerbi/TATA_AIG/Tata_AIG_Vaahan_Hit?rs:embed=true" width="100%" height="400"></iframe>
    @endif
</div>
@endsection
