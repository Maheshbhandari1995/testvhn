<?php 
	$url = request()->root();
		$parsedUrl = parse_url($url);
		$baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
		$watermark = $baseUrl."/assets/img/edas-logo-light.png";
?>
<!DOCTYPE html>
<html>
<head>
    <title>PDF Report</title>
	{{-- style="background : url('{{asset('assets/img/edas-logo-light.png')}}') no-repeat;"   background: url('https://assets-global.website-files.com/62a091d2bcd34528614901bd/62a87fb61ae5a71bfa43f0b9_AGB%20Black%20Logo-p-500.webp') no-repeat background-color: hsl(0deg 0% 100%); filter:blur(4px); position:center; width: 200px; height: 100px; top: 50%; left: 50%;--}}
</head>
	<body style="background-image : url('https://edas-webapi.edas.tech/amygb2.0/assets/img/amygb_blur_img.png'); background-repeat: no-repeat; background-position: center; background-size:100%; max-height: 40px; ">
		<div id="main" class="main">
			<div class="searched-details">
				<img class="header-img" src="https://assets-global.website-files.com/62a091d2bcd34528614901bd/62a87fb61ae5a71bfa43f0b9_AGB%20Black%20Logo-p-500.webp" width="150" height="51" alt="Amygb">
			<h3 style="text-align:center; border-bottom:1px solid #f2f2f2; padding:10px;">RC Details</h3>
				<table style="width:100%;border-spacing: 0px; font-family: Arial, Helvetica, sans-serif; font-size:14px;">
					<tbody>
						@foreach($data as $key => $item)
						<tr>
							<th style="width:35%; text-align: left; font-weight:100; border-bottom:1px solid #ebe1e1; padding:5px; text-transform:uppercase">{{ $key }}</th> <td style="width:65%; border-bottom:1px solid #ebe1e1; padding:5px;">{{ $item }}</td>
							<!-- Add other table data as needed -->
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>