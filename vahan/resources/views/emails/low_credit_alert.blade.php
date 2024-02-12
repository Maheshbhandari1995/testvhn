<!DOCTYPE html>
<html>
<head>
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .logo {
            text-align: center;
        }
        .logo img {
            max-width: 200px;
        }
        .message {
            margin-top: 20px;
            background-color: #ffffff;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }} Logo">
        </div>
        <div class="message">
            <p>Dear Customer,</p>
            <p>Your account has a low credit limit. Please take necessary actions.</p>
            <h4>Client Details:</h4>
            <p>Organization Name: {{strtoupper($clientData->name)}}</p>
            <p>Available Credit: {{strtoupper($clientData->max_count)}}</p>
            {{-- <pre>{{ print_r($clientData, true) }}</pre> --}}
        </div>
    </div>
</body>
</html>