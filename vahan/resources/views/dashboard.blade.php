
@extends('layout')

@section('content')
<div id="main" class="main">
    @if(session('data.userRole') == 'user')
    <div class="pagetitle">
        <h1>dashboard</h1>
    </div>
    <div class="row servicesbox-set">
        <div class="col-lg-3 col-md-6">
            <div class="serviceBox">
                <div class="service-icon">
                    <h3 class="description">Available Credits</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" viewBox="608.843 240 40.355 40.361"><g data-name="Group 52"><path d="M622.091 278.504c-.137-.076-.3-.125-.408-.231-3.868-3.86-7.73-7.725-11.596-11.587-1.145-1.144-1.559-2.487-.99-4.023.192-.523.54-1.034.935-1.431a9251.983 9251.983 0 0 1 20.028-20.041c1.595-1.592 3.806-1.586 5.402.006 4.185 4.174 8.365 8.354 12.539 12.539 1.625 1.63 1.591 3.83-.068 5.489a47665.257 47665.257 0 0 1-19.033 19.03c-.11.11-.251.188-.378.281l.066.146h8.51c.765 0 1.144.272 1.153.822.01.564-.387.857-1.163.857l-16.156-.001c-.144 0-.29.003-.432-.021-.447-.077-.728-.42-.713-.852.015-.434.314-.748.776-.79.234-.021.472-.012.708-.014.231-.002.462 0 .693 0l.127-.179Zm16.66-31.647c-1.51-1.51-2.939-2.94-4.369-4.368-1.08-1.08-2.17-1.079-3.255.005l-19.812 19.816c-.13.13-.26.262-.372.406-.53.678-.568 1.667-.04 2.344.344.44.763.82 1.23 1.314.124-.175.2-.319.31-.428 6.266-6.272 12.536-12.542 18.807-18.81.138-.138.283-.277.445-.384a.8.8 0 0 1 1.012.1.8.8 0 0 1 .109 1.042 2.89 2.89 0 0 1-.362.414c-6.277 6.28-12.556 12.559-18.838 18.834-.109.108-.265.168-.407.255.164.182.221.252.285.316 3.427 3.428 6.852 6.857 10.282 10.281.936.934 2.098.91 3.058-.049 6.651-6.65 13.301-13.3 19.95-19.951.985-.986.98-2.12-.01-3.113-2.2-2.203-4.402-4.403-6.603-6.605-.089-.088-.182-.171-.302-.285-.137.152-.237.274-.348.385-6.27 6.27-12.538 12.54-18.81 18.808a2.836 2.836 0 0 1-.446.382.814.814 0 0 1-.982-.1.798.798 0 0 1-.152-1.006 2.54 2.54 0 0 1 .38-.449c6.287-6.289 12.575-12.577 18.865-18.862.102-.102.228-.179.376-.292Z" fill="#fabd00" fill-rule="evenodd" data-name="Path 66"/><path d="M639.134 260.356c-.24-.135-.474-.212-.633-.366-.765-.738-1.515-1.49-2.257-2.25-.417-.426-.423-.898-.006-1.322 1.141-1.158 2.291-2.309 3.451-3.449.412-.404.876-.4 1.29.002.763.74 1.513 1.493 2.255 2.253.42.431.424.894.005 1.32a267.276 267.276 0 0 1-3.453 3.447c-.166.162-.417.237-.652.365Zm1.256-5.554-2.308 2.29 1.066 1.082 2.293-2.316c-.35-.35-.727-.73-1.051-1.056Z" fill="#fabd00" fill-rule="evenodd" data-name="Path 67"/><path d="M621.52 271.337c.07-.112.168-.362.342-.538 2.125-2.142 4.26-4.275 6.397-6.405.435-.433.928-.46 1.289-.1.365.363.34.847-.099 1.287-2.13 2.137-4.265 4.27-6.4 6.403-.29.29-.623.45-1.028.272-.329-.143-.502-.409-.501-.92Z" fill="#fabd00" fill-rule="evenodd" data-name="Path 68"/><path d="M624.704 274.82c-.263-.205-.572-.337-.685-.566-.107-.218-.032-.54.009-.809.017-.11.147-.21.239-.303.814-.816 1.619-1.642 2.454-2.436.192-.182.489-.337.746-.352.61-.036.995.667.692 1.202a1.63 1.63 0 0 1-.266.337c-.813.818-1.622 1.639-2.45 2.441-.186.182-.44.294-.74.486Z" fill="#fabd00" fill-rule="evenodd" data-name="Path 69"/><path d="M635.27 245.07c.033.5-.61 1.208-1.126 1.241a.824.824 0 0 1-.876-.771c-.03-.503.602-1.202 1.125-1.243a.833.833 0 0 1 .877.773Z" fill="#fabd00" fill-rule="evenodd" data-name="Path 70"/><path d="M629.558 269.858c-.571-.017-1.018-.661-.733-1.15.189-.323.502-.62.829-.803.46-.257 1.039.128 1.099.658.061.539-.652 1.311-1.195 1.295Z" fill="#fabd00" fill-rule="evenodd" data-name="Path 71"/><path d="M618.714 268.837c-.008.505-.707 1.19-1.2 1.176a.845.845 0 0 1-.808-.851c.008-.513.697-1.185 1.201-1.172.45.011.814.394.807.847Z" fill="#fabd00" fill-rule="evenodd" data-name="Path 72"/></g></svg>
                </div>
                <div>
                    <div data-target='{{$maxCount}}' class="title count">{{$maxCount}}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="serviceBox">
                <div class="service-icon">
                    <div class="description">Utilized Credits</div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" viewBox="999.175 231 40.746 40.792"><g data-name="Group 61"><path d="m1029.664 253.83 3.207-3.294c.545-.56 1.085-1.126 1.635-1.681 1.563-1.579 4.306-1.122 5.131.937.46 1.149.401 2.36-.47 3.397-1.892 2.252-3.773 4.512-5.662 6.765-.553.66-1.068 1.361-1.687 1.952-1.364 1.3-3.032 1.904-4.911 1.907-3.365.007-6.73.044-10.095-.02-1.234-.022-2.05.562-2.796 1.39-.06.069-.066.218-.048.32.162.9-.052 1.677-.712 2.325-1.219 1.198-2.432 2.401-3.65 3.6-.502.492-.898.485-1.4-.021-2.885-2.905-5.768-5.81-8.65-8.717-.51-.514-.508-.912.006-1.43 1.168-1.173 2.342-2.341 3.505-3.52.562-.569 1.218-.865 2.028-.827a.48.48 0 0 0 .361-.205c2.669-4.654 8.425-5.729 12.491-4.187.228.086.487.13.732.131 2.676.01 5.352.006 8.028.006 1.408 0 1.92.2 2.957 1.172Zm-16.704 10.105c.006-.002.035-.007.054-.024.07-.06.141-.12.203-.189 1.01-1.118 2.262-1.54 3.766-1.516 3.272.054 6.545.021 9.818.015 1.808-.004 3.33-.635 4.477-2.082.551-.695 1.136-1.363 1.706-2.044 1.649-1.971 3.301-3.94 4.946-5.914.608-.73.548-1.689-.121-2.28-.651-.576-1.601-.515-2.25.15a1608.504 1608.504 0 0 0-5.01 5.163c-.098.101-.185.266-.186.402-.005 2.063-1.335 3.415-3.4 3.422-2.014.007-4.028.002-6.042 0-.158-.001-.32-.011-.472-.052a.772.772 0 0 1-.562-.812.768.768 0 0 1 .642-.708c.156-.027.317-.027.475-.027 1.617-.002 3.233 0 4.85-.002.503-.001 1.009.019 1.508-.025.765-.068 1.324-.67 1.4-1.444a1.568 1.568 0 0 0-1.087-1.62c-.26-.078-.548-.092-.823-.092-2.783-.007-5.565.002-8.347-.013a3.244 3.244 0 0 1-1-.187c-3.228-1.09-6.236-.757-8.95 1.392-.784.62-1.377 1.411-1.81 2.259l6.215 6.228Zm-3.944 5.84c1.038-1.028 2.145-2.108 3.23-3.21.3-.304.256-.757-.06-1.074-2.237-2.24-4.476-4.479-6.721-6.71-.384-.383-.792-.37-1.187.014-.455.443-.898.9-1.348 1.348-.607.605-1.216 1.207-1.863 1.849l7.949 7.783Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 73"/><path d="M1021.115 247.877c-3.086 0-6.172-.039-9.257.016-1.57.027-2.678-1.13-2.66-2.64.049-3.88.015-7.762.018-11.642 0-1.645.97-2.61 2.621-2.61h18.635c1.652 0 2.621.965 2.622 2.61.002 3.893.002 7.787 0 11.68 0 1.62-.973 2.585-2.602 2.585-3.126.002-6.251 0-9.377 0Zm-10.284-8.893c-.008.145-.02.273-.02.402-.002 1.96-.002 3.92 0 5.88 0 .767.25 1.017 1.026 1.017h18.632c.775 0 1.031-.253 1.032-1.014v-5.88c0-.128-.012-.257-.019-.405h-20.651Zm20.657-3.178h-20.657v1.528h20.657v-1.528Zm.013-1.642v-.322c.009-1.063-.175-1.247-1.242-1.247h-18.323c-.133 0-.266-.007-.397.006-.387.039-.683.307-.715.68-.025.286-.005.575-.005.883h20.682Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 74"/><path d="M1018.143 258.089a.791.791 0 0 1-.797.807.817.817 0 0 1-.788-.786.796.796 0 0 1 .806-.8.778.778 0 0 1 .78.779Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 75"/><path d="M1027.53 244.69c-.515 0-1.031.007-1.547-.002-.498-.009-.83-.316-.844-.761-.016-.45.332-.815.833-.822 1.032-.014 2.064-.014 3.095-.001.512.006.848.35.84.811-.007.464-.339.766-.868.774-.503.007-1.005.002-1.508.001Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 76"/><path d="M1022.742 244.687a.781.781 0 0 1-.787-.772.81.81 0 0 1 .798-.811.81.81 0 0 1 .787.783.78.78 0 0 1-.798.8Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 77"/></g></svg>
                </div>
                <div>
                    <div data-target="{{$utliziedCount}}" class="title count">{{$utliziedCount}}</div>
                </div>
            </div>
        </div>
       
        <div class="col-lg-3 col-md-6">
            <div class="serviceBox">
                <div class="service-icon">
                    <div class="description">Success Hit</div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" viewBox="1382.781 231 47.016 40.881"><g data-name="Group 62"><path d="M1384.826 239.206v.42c0 5.016-.003 10.03.001 15.046.002 1.76 1.154 2.903 2.916 2.903 4.695.002 9.391 0 14.087.001.902 0 1.387.355 1.392 1.015.005.67-.47 1.024-1.378 1.024-4.68.001-9.36.004-14.04 0-3.002-.003-5.01-2.008-5.015-5.022-.009-5.223-.004-10.446-.002-15.669 0-1.117-.022-2.235.017-3.351.088-2.508 2.078-4.47 4.592-4.562a11.8 11.8 0 0 1 .431-.007c10.957 0 21.914.004 32.87-.004 2.133-.002 3.7.907 4.574 2.85.28.622.425 1.354.433 2.037.043 3.418.023 6.836.018 10.254 0 .703-.349 1.14-.917 1.195-.67.063-1.134-.398-1.14-1.164-.01-1.325-.003-2.65-.003-3.977v-2.99h-38.836Zm.008-2.1h38.828v-1.03c-.006-1.944-1.09-3.032-3.021-3.032h-23.858c-3.067 0-6.133-.006-9.199.002-1.476.004-2.64 1.08-2.744 2.532-.035.492-.006.988-.006 1.527Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 78"/><path d="M1418.569 249.406a11.218 11.218 0 0 1 11.227 11.246 11.221 11.221 0 0 1-11.245 11.23 11.222 11.222 0 0 1-11.228-11.248 11.22 11.22 0 0 1 11.246-11.228Zm9.19 11.236c-.001-5.077-4.122-9.198-9.2-9.197-5.078 0-9.198 4.122-9.198 9.199 0 5.076 4.124 9.2 9.2 9.199 5.076 0 9.198-4.125 9.198-9.2Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 79"/><path d="M1392.013 254.51c-1.357 0-2.714.006-4.071-.003-.521-.004-.88-.272-1.016-.717-.137-.448-.016-.837.358-1.1.182-.127.44-.207.664-.208 2.682-.014 5.365-.014 8.047-.006.661.002 1.096.427 1.093 1.015-.002.582-.45 1.011-1.1 1.016-1.325.01-2.65.003-3.975.003Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 80"/><path d="M1389.963 247.346c.621 0 1.242-.008 1.863.002.726.011 1.172.419 1.164 1.044-.009.602-.449 1.006-1.148 1.012-1.274.011-2.548.012-3.822 0-.69-.007-1.145-.428-1.151-1.02-.005-.593.45-1.02 1.135-1.035.653-.014 1.306-.003 1.96-.003Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 81"/><path d="M1418.522 262.303c1.1-1.1 2.113-2.117 3.128-3.131.636-.637 1.232-.736 1.703-.285.484.462.392 1.088-.257 1.739-1.196 1.197-2.393 2.395-3.592 3.59-.69.69-1.193.691-1.876.01-.916-.914-1.85-1.811-2.731-2.757-.225-.24-.417-.659-.374-.96.042-.296.352-.628.634-.8.373-.23.778-.06 1.087.242.639.626 1.27 1.26 1.9 1.895.122.124.223.269.378.457Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 82"/></g></svg>
                </div>
                <div>
                    <div data-target="{{$successCount}}" class="title count">{{$successCount}}</div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="serviceBox">
                <div class="service-icon">
                    <div class="description">Failed Hit</div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" viewBox="1778.499 240.482 47.016 40.881"><g data-name="Group 63"><path d="M1780.545 248.688v.42c0 5.016-.003 10.031.001 15.046.002 1.76 1.154 2.903 2.915 2.904 4.696.002 9.392 0 14.088 0 .902 0 1.387.355 1.392 1.016.005.669-.47 1.024-1.378 1.024-4.68 0-9.36.003-14.04-.001-3.002-.003-5.01-2.007-5.016-5.022-.008-5.223-.003-10.446-.002-15.668 0-1.118-.022-2.236.018-3.352.088-2.507 2.078-4.469 4.592-4.562.143-.005.287-.007.43-.007 10.958 0 21.914.004 32.871-.004 2.133-.001 3.7.908 4.574 2.85.279.622.425 1.354.433 2.038.043 3.417.022 6.835.018 10.253 0 .703-.35 1.14-.918 1.195-.669.063-1.134-.397-1.14-1.163-.009-1.326-.002-2.652-.002-3.977v-2.99h-38.836Zm.008-2.1h38.828v-1.03c-.007-1.944-1.09-3.032-3.021-3.032H1792.5c-3.066 0-6.132-.006-9.198.003-1.477.004-2.64 1.079-2.744 2.531-.035.492-.006.988-.006 1.528Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 83"/><path d="M1814.288 258.888a11.218 11.218 0 0 1 11.227 11.247 11.221 11.221 0 0 1-11.246 11.229 11.222 11.222 0 0 1-11.228-11.248 11.22 11.22 0 0 1 11.247-11.228Zm9.19 11.237c-.001-5.078-4.123-9.198-9.2-9.198-5.078 0-9.199 4.122-9.198 9.2 0 5.075 4.124 9.198 9.2 9.198 5.075 0 9.198-4.124 9.197-9.2Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 84"/><path d="M1787.732 263.992c-1.357 0-2.714.006-4.071-.003-.522-.004-.881-.272-1.017-.716-.137-.45-.016-.838.359-1.1.182-.128.44-.207.664-.209 2.682-.014 5.364-.014 8.046-.005.662.002 1.097.426 1.094 1.014-.003.582-.45 1.012-1.1 1.017-1.325.01-2.65.002-3.975.002Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 85"/><path d="M1785.682 256.828c.621 0 1.242-.008 1.863.002.726.012 1.172.42 1.163 1.044-.008.602-.448 1.007-1.147 1.013-1.274.01-2.548.011-3.822 0-.69-.007-1.146-.428-1.151-1.02-.006-.594.45-1.02 1.135-1.036.653-.014 1.306-.003 1.959-.003Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 86"/><path d="M1814.1 271.384c-.083.08-.157.146-.227.216-1.203 1.198-2.408 2.395-3.608 3.596-.244.243-.521.38-.867.31-.33-.066-.567-.262-.665-.59-.107-.353-.023-.668.244-.932.815-.808 1.629-1.616 2.44-2.427.474-.472.941-.95 1.423-1.438-.07-.075-.136-.149-.205-.219-1.198-1.203-2.395-2.408-3.596-3.608-.236-.236-.372-.502-.323-.837a.878.878 0 0 1 1.397-.568c.088.066.165.146.243.224 1.172 1.177 2.344 2.355 3.514 3.535.07.07.123.16.197.256.114-.105.187-.17.257-.238 1.203-1.198 2.408-2.395 3.607-3.597.259-.26.55-.401.916-.314.68.162.899.98.392 1.502-.55.567-1.12 1.113-1.676 1.673-.725.729-1.444 1.462-2.189 2.217.053.056.119.128.187.196 1.205 1.21 2.408 2.422 3.616 3.629.244.244.373.523.306.868a.878.878 0 0 1-1.38.535c-.087-.064-.166-.144-.243-.222l-3.534-3.553c-.07-.07-.144-.137-.226-.214Z" fill="#ffbe2f" fill-rule="evenodd" data-name="Path 87"/></g></svg>
                </div>
                <div>
                    <div data-target="{{$failcount}}" class="title count">{{$failcount}}</div>
                </div>
                
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped" id="user_table">
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Module</th>
                    {{-- <th>Api_url</th> --}}
                    <th>Status</th>
                    <th>Activity On</th>
                </tr>
            </thead>
        </table>
    </div>


    @endif

    @if(session('data.userRole') == 'admin')

    {{-- print_r(session) --}}

    @if(in_array(session('data.Client_id'), [19]) && in_array(session('data.clientName'), ['Edas']))
    {{-- || in_array(session('data.clientName'), ['Edas']) --}}
    <?php 
    // $sessionData = session('data');
    // print_r($sessionData);
    ?>

{{-- servicesbox-set --}}
<div class="pagetitle">
    <h1>dashboard</h1>
</div>
    <div class="row ">
        <div class="col-lg-12">

        <div class="table-responsive">
            <table class="table  align-middle th-font-size table-bordered table-bordered-white table-hover text-center">
                <thead>
                <tr>
                   <th class="th-transparent"></th>
                   <th >Portal</th>
                   <th class="th-yelow-bg">API</th>
                </tr>
                </thead>
                <tbody >
                <tr>
                    <td>Total Lifetime</td>
                    <td>{{$utliziedCount}}</td>
                    <td >{{$apicount}}</td>
                </tr>
                <tr>
                    <td>Monthly</td>
                    <td>{{$monthportalhits}}</td>
                    <td >{{$monthapihits}}</td>
                </tr>
                <tr>
                    <td>Previous Day</td>
                    <td>{{$yesterdayutilizedCount}}</td>
                    <td >{{$yesterdayapihits}}</td>
                </tr>
                <tr>
                    <td style="padding-left: 120px"><i class="bi bi-chevron-double-right" style="font-size: 11px;font-weight: bold;"></i> Success</td>
                    <td>{{$yesterdaysuccessCount}}</td>
                    <td >{{$yesterdayapisuccesscount}}</td>
                </tr>
                <tr>
                    <td style="padding-left: 120px"><i class="bi bi-chevron-double-right" style="font-size: 11px;font-weight: bold;"></i> Failure</td>
                    <td>{{$yesterdayfailcount}}</td>
                    <td >{{$yesterdayapifailcount}}</td>
                </tr>
                
                </tbody>
            </table>
        </div>


        </div>
    </div>
    @else
        {{-- <div class="row servicesbox-set">
            <div class="col-lg-3 col-md-6">
                <div class="serviceBox">
                    <div class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="19" viewBox="0 0 24 19" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M1 3.43093V15.5856C1 16.9282 2.08837 18.0165 3.43093 18.0165H20.4475C21.7901 18.0165 22.8784 16.9282 22.8784 15.5856V3.43093C22.8784 2.08837 21.7901 1 20.4475 1H3.43093C2.08837 1 1 2.08837 1 3.43093Z" stroke="#1D4487" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.5856 7.07715H19.232" stroke="#1D4487" stroke-linecap="round"/>
                        <path d="M15.5856 10.7236H19.232" stroke="#1D4487" stroke-linecap="round"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.7238 7.07693C10.7238 8.41953 9.63548 9.50786 8.29287 9.50786C6.9503 9.50786 5.86194 8.41953 5.86194 7.07693C5.86194 5.73436 6.9503 4.646 8.29287 4.646C8.93756 4.646 9.55586 4.90211 10.0118 5.358C10.4677 5.81389 10.7238 6.43221 10.7238 7.07693Z" stroke="#1D4487" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3.43091 14.3698C6.81355 10.5569 10.7869 11.7554 13.1546 14.3698" stroke="#1D4487" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="description">Available Credits</h3>
                        <div data-target='{{$maxCount}}' class="title count">{{$maxCount}}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="serviceBox">
                    <div class="service-icon">
                        <div class="description">Utilized Credits</div>
                        <i class="fa fa-sitemap"></i>
                    </div>
                    <div>
                        <div data-target="{{$utliziedCount}}" class="title count">{{$utliziedCount}}</div>
                    </div>
                </div>
            </div>
        
            <div class="col-lg-3 col-md-6">
                <div class="serviceBox">
                    <div class="service-icon">
                        <div class="description">Success Hit</div>
                        <i class="fa fa-hand-pointer-o"></i>
                    </div>
                    <div>
                        <div data-target="{{$successCount}}" class="title count">{{$successCount}}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="serviceBox">
                    <div class="service-icon">
                        <div class="description">Failed Hit</div>
                        <i class="fa fa-user"></i>
                    </div>
                    <div>
                        <div data-target="{{$failcount}}" class="title count">{{$failcount}}</div>
                    </div>
                    
                </div>
            </div>
        </div> --}}
    @endif
    <div class="table-responsive">
        <table class="table table-striped" id="admin_table">
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Name</th>
                    <th>Total hits</th>
                    <th>Success hits</th>
                    <th>Failed hits</th>
                    {{-- <th>Status</th> --}}
                </tr>
            </thead>
        </table>
    </div>

    @endif

    @if(session('data.userRole') == 'super_admin')
    <!-- /.row -->
    <div class="pagetitle">
        <h1>dashboard</h1>
    </div>
    <div class="row servicesbox-set">
        <div class="col-lg-3 col-md-6">
            <div class="serviceBox">
                <div class="service-icon">
                    <div class="description">Organization</div>
                    <i class="fa fa-users"></i>
                </div>
                <div>
                    <div data-target="{{$companyCount}}" class="title count">{{$companyCount}}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="serviceBox">
                <div class="service-icon">
                    <div class="description">Total Hit</div>
                    <i class="fa fa-hand-pointer-o"></i>
                </div>
                <div>
                    <div data-target="{{$sum}}" class="title count">{{$sum}}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="serviceBox">
                <div class="service-icon">
                    <div class="description">Success Hits</div>
                    <i class="fa fa-sitemap"></i>
                </div>
                <div>
                    <h3 data-target="{{$successCounts[0]['count']}}" class="title count">{{$successCounts[0]['count']}}</h3>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="serviceBox">
                <div class="service-icon">
                    <div class="description">Failed Hits</div>
                    <i class="fa fa-user"></i>
                </div>
                <div>
                    <div data-target="{{$failCounts[0]['count']}}" class="title count">{{$failCounts[0]['count']}}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped" id="superadmin_table">
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Organization</th>
                    <th>Available Credits</th>
                    <th>Utilized Credit</th>
                    <th>Success Hit</th>
                    <th>Failed Hit</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    </div>

    @endif
    
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
    $(document).ready(function(){

        // Get the CSRF token value from the meta tag
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        // Add the "buttons" option for downloading
        $('#superadmin_table').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('dashboard.list') }}",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken // Include the CSRF token in the headers
                }
            },
            columns: [
                { 
                    data: 'id',
                    render: function (data, type, row, meta) {
                        // Calculate the serial number using the row index
                        var srNo = meta.row + 1;

                        return srNo;
                    },
                    orderable: false,
                    searchable: false
                },
                { data: 'client_name', name: 'client_name' },
                { data: 'max_count', name: 'max_count' },
                {
                    data: 'utilized_count',
                    name: 'utilized_count',
                    render: function (data, type, row) {
                        var clientName = row.client_name;
                        var count = 0;

                        for (var i = 0; i < data.length; i++) {
                            if (data[i].client_name === clientName) {
                                count = data[i].utilized_count;
                                break;
                            }
                        }

                        return count;
                    }
                },

                { 
                    data: 'successCounts', 
                    name: 'successCounts',
                    render: function (data, type, row) {
                        var clientName = row.client_name;
                        var count = 0;

                        // Iterate over the successCounts array to find the matching count value
                        for (var i = 0; i < data.length; i++) {
                            if (data[i].client_name === clientName) {
                                count = data[i].count;
                                break;
                            }
                        }

                        return count;
                    }
                },
                { 
                    data: 'failcounts', 
                    name: 'failcounts',
                    render: function (data, type, row) {
                        var clientName = row.client_name;
                        var count = 0;

                        // Iterate over the failCounts array to find the matching count value
                        for (var i = 0; i < data.length; i++) {
                            if (data[i].client_name === clientName) {
                                count = data[i].count;
                                break;
                            }
                        }

                        return count;
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function (data, type, row) {
                        if (data == 0 || data == 1) {
                            return '<b class="text-success">Active</b>';
                        } else{
                            return '<b class="text-danger">Inactive</b>';
                        }
                    }
                },
                        //{ data: 'response_status_code', name: 'response_status_code' }
                ],
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print' // Add the buttons you want to enable
                ]
        });

        $('#admin_table').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('userdashboard.list') }}",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken // Include the CSRF token in the headers
                }
            },
            columns: [
                { 
                    data: 'id',
                    render: function (data, type, row, meta) {
                        // Calculate the serial number using the row index
                        var srNo = meta.row + 1;

                        return srNo;
                    },
                    orderable: false,
                    searchable: false
                },
                { data: 'name', name: 'name' },
                { data: 'total_count', name: 'total_count' },
                { data: 'success_count', name: 'success_count' },
                { data: 'fail_count', name: 'fail_count' },
                ],
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print' // Add the buttons you want to enable
                ]
        });

        $('#user_table').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('userdashboard.list') }}",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken // Include the CSRF token in the headers
                }
            },
            columns: [
                { 
                    data: 'id',
                    render: function (data, type, row, meta) {
                        // Calculate the serial number using the row index
                        var srNo = meta.row + 1;

                        return srNo;
                    },
                    orderable: false,
                    searchable: false
                },
                { data: 'api_name', name: 'api_name' },
                { 
                    data: 'response_status_code', 
                    name: 'response_status_code',
                    render: function (data, type, row) {
                        if (data == 200 || data == 201 || data == 1) {
                            return '<b class="text-success">Success</b>';
                        } else{
                            return '<b class="text-danger">Failed</b>';
                        }
                    }
                },
                { data: 'created_at', name: 'created_at' }
                ],
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print' // Add the buttons you want to enable
                ]
        });

    });
</script>
<!-- counter  -->
<script>

	</script>
<!-- /#page-wrapper -->
    <!-- Your home page content goes here -->
    @endsection
