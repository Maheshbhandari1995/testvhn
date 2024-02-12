
<!DOCTYPE html>
<html>
<head>
    <title>PDF Challan Report</title>
    <style>
        /* .header-img {
            align-items: center;
        } */
    </style>
</head>
<?php 
$url = request()->root();
		$parsedUrl = parse_url($url);
		$baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];

        $headerImagePath = $baseUrl.'/assets/img/edas-logo-light.png'; ?>
            {{-- <img class="header-img" src="<?php //echo $headerImagePath?>" width="150" height="51" alt="Amygb"> --}}
            {{-- <img class="header-img" src="<?php //echo $headerImagePath?>" width="150" height="51" alt="Amygb"> --}}
	<body>
		<div id="main" class="main">
			<div class="searched-details">
            <img class="header-img" src="https://assets-global.website-files.com/62a091d2bcd34528614901bd/62a87fb61ae5a71bfa43f0b9_AGB%20Black%20Logo-p-500.webp" width="150" height="51" alt="Amygb">
			<h3 style="text-align:center; border-bottom:1px solid #f2f2f2; padding:10px;">Challan Details</h3>
				<table style="width:100%;border-spacing: 0px; font-family: Arial, Helvetica, sans-serif; font-size:14px;">
					<tbody>
                        @foreach($data as $item)
                            @foreach($item as $key => $items)
                                <tr>
                                    <th style="width:35%; text-align: left; font-weight:100; border-bottom:1px solid #ebe1e1; padding:5px; text-transform:uppercase">{{ htmlspecialchars($key) }}</th>
                                    <td style="width:65%; border-bottom:1px solid #ebe1e1; padding:5px;">{{ htmlspecialchars($items) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" style="height: 20px;"></td>
                            </tr>
                        @endforeach
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>