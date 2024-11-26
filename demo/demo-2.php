<?php

// Include a function to get the current UTC time in ISO 8601 format
function getCurrentUTCTimeFromNTP($host = 'pool.ntp.org', $port = 123)
{
    // Create a socket
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$socket) {
        throw new Exception("Unable to create socket: " . socket_strerror(socket_last_error()));
    }

    // Prepare NTP request packet (48 bytes)
    $message = chr(0x1B) . str_repeat(chr(0x00), 47);

    // Send request to NTP server
    $serverAddress = gethostbyname($host);
    if (!socket_sendto($socket, $message, strlen($message), 0, $serverAddress, $port)) {
        throw new Exception("Failed to send data to NTP server.");
    }

    // Receive response
    $buffer = '';
    if (socket_recvfrom($socket, $buffer, 48, 0, $serverAddress, $port) === false) {
        throw new Exception("Failed to receive data from NTP server.");
    }

    socket_close($socket);

    // Interpret response
    $data = unpack('N12', $buffer);
    $timestamp = sprintf('%u', $data[9]); // NTP time-stamp seconds

    // Convert NTP time to Unix epoch time
    // NTP epoch (1900-01-01 00:00:00) is 2208988800 seconds before Unix epoch (1970-01-01 00:00:00)
    $unixTime = $timestamp - 2208988800;

    // Format UTC time in ISO 8601 format with milliseconds
    $current_time_utc = (new DateTime('@' . $unixTime))->format('Y-m-d\TH:i:s.v\Z');

    return $current_time_utc;
}


$requestId = '';
for ($i = 0; $i < 15; $i++) {
    $requestId .= mt_rand(0, 9);
}

// Provided keys and values
$api_key = 'bc4a1799650257f3d70262d17afb6d3e';
$api_secret = 'b800886a-667d-4f58-91cf-06b3e399aff8';
$request_id = $requestId;
$redirectUrl = "https://dev.digimart.store/";
$msisdn = ''; // Optional - Subscriber's mobile number
$msisdn2 = '01740812854';

// Generate the current UTC time
$current_time_utc = getCurrentUTCTimeFromNTP();

// Create the signature
$signature_data = "{$api_key}|{$current_time_utc}|{$api_secret}";
$hashed_signature = hash('sha512', $signature_data);

// Construct the final API endpoint
$api_endpoint = "https://user.digimart.store/sdk/subscription/authorize";
$api_endpoint .= "?apiKey=" . urlencode($api_key);
$api_endpoint .= "&requestId=" . urlencode($request_id);
$api_endpoint .= "&requestTime=" . urlencode($current_time_utc);
$api_endpoint .= "&signature=" . urlencode($hashed_signature);
$api_endpoint .= "&redirectUrl=" . urlencode($redirectUrl);
if (!empty($msisdn2)) {
    $api_endpoint .= "&msisdn=" . urlencode($msisdn2);
}

// Second endpoint without MSISDN
$api_endpoint2 = "https://user.digimart.store/sdk/subscription/authorize";
$api_endpoint2 .= "?apiKey=" . urlencode($api_key);
$api_endpoint2 .= "&requestId=" . urlencode($request_id);
$api_endpoint2 .= "&requestTime=" . urlencode($current_time_utc);
$api_endpoint2 .= "&signature=" . urlencode($hashed_signature);
$api_endpoint2 .= "&redirectUrl=" . urlencode($redirectUrl);

// Handle form submission for MSISDN
$api_endpoint3 = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $msisdn = $_POST['msisdn']; // Get the MSISDN from the form
    // Create the third API endpoint with MSISDN
    $api_endpoint3 = "https://user.digimart.store/sdk/subscription/authorize";
    $api_endpoint3 .= "?apiKey=" . urlencode($api_key);
    $api_endpoint3 .= "&requestId=" . urlencode($request_id);
    $api_endpoint3 .= "&requestTime=" . urlencode($current_time_utc);
    $api_endpoint3 .= "&signature=" . urlencode($hashed_signature);
    $api_endpoint3 .= "&redirectUrl=" . urlencode($redirectUrl);
    $api_endpoint3 .= "&msisdn=" . urlencode($msisdn);
}

$api_endpoint4 = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $msisdn = $_POST['msisdn']; // Get the MSISDN from the form
    // Create the third API endpoint with MSISDN
    $api_endpoint4 = "https://user.digimart.store/sdk/subscription/authorize";
    $api_endpoint4 .= "?apiKey=" . urlencode($api_key);
    $api_endpoint4 .= "&requestId=" . urlencode($request_id);
    $api_endpoint4 .= "&requestTime=" . urlencode($current_time_utc);
    $api_endpoint4 .= "&signature=" . urlencode($hashed_signature);
    $api_endpoint4 .= "&redirectUrl=" . urlencode($redirectUrl);
    $api_endpoint4 .= "&msisdn=" . urlencode($msisdn);
    $api_endpoint4 .= "&amount=5";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiver Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        p {
            text-align: center;
        }
        form {
            text-align: center;
            margin: 20px 0;
        }
        input[type="text"] {
            padding: 10px;
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }
        button {
            padding: 10px 15px;
            background-color: #007BFF;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Fiver Code - Initial One</h1>
    <p><a href="<?php echo htmlspecialchars($api_endpoint); ?>" target="_blank"><?php echo htmlspecialchars($api_endpoint); ?></a></p>

    <h1>End Point Without MSIDN</h1>
    <p><a href="<?php echo htmlspecialchars($api_endpoint2); ?>" target="_blank"><?php echo htmlspecialchars($api_endpoint2); ?></a></p>

    <h1>Add Phone Number</h1>
    <form method="POST" action="">
        <input type="text" name="msisdn" placeholder="Enter Phone Number" required>
        <button type="submit">Get Endpoint</button>
    </form>

    <?php if (!empty($api_endpoint3)): ?>
        <h1>End Point With MSIDN</h1>
        <p><a href="<?php echo htmlspecialchars($api_endpoint3); ?>" target="_blank"><?php echo htmlspecialchars($api_endpoint3); ?></a></p>
    <?php endif; ?>

    <?php if (!empty($api_endpoint4)): ?>
        <h1>End Point With MSIDN and Amount</h1>
        <p><a href="<?php echo htmlspecialchars($api_endpoint4); ?>" target="_blank"><?php echo htmlspecialchars($api_endpoint4); ?></a></p>
    <?php endif; ?>
</body>
</html>

