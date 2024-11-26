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

$msisdn = isset($_POST['msisdn']) ? $_POST['msisdn'] : '01773301138';
$showLink = false;
$requestUrl = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // API credentials
        $apiKey = "bc4a1799650257f3d70262d17afb6d3e";
        $apiSecret = "b800886a-667d-4f58-91cf-06b3e399aff8";
        
        // Generate request ID using microseconds for uniqueness
        $milliseconds = round(microtime(true) * 1000);
        $random = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $requestId = substr($milliseconds . $random, -15);
        
        // Get current time from NTP server
        $requestTime = getCurrentUTCTimeFromNTP();
        
        // Set redirect URL
        $redirectUrl = "https://dev.digimart.store/";

        // Create signature with SHA-512
        $signatureData = "{$apiKey}|{$requestTime}|{$apiSecret}";
        $signature = hash("sha512", $signatureData);

        // Construct API endpoint with parameters
        $baseUrl = "https://user.digimart.store/sdk/subscription/authorize";
        $queryParams = [
            "apiKey" => urlencode($apiKey),
            "requestId" => urlencode($requestId),
            "requestTime" => urlencode($requestTime),
            "signature" => urlencode($signature),
            "redirectUrl" => urlencode($redirectUrl)
        ];

        if (!empty($msisdn)) {
            $queryParams["msisdn"] = urlencode($msisdn);
        }

        $requestUrl = $baseUrl . '?' . http_build_query($queryParams);
        $showLink = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digimart Subscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .subscription-card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
            border: none;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .btn-subscribe {
            padding: 12px 24px;
            font-size: 1.1rem;
        }
        .details-table td code {
            word-break: break-all;
            white-space: normal;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card subscription-card">
                    <div class="card-header py-3">
                        <h3 class="text-center mb-0">Digimart Subscription</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                        <div class="alert alert-danger mb-4" role="alert">
                            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>

                        <form method="post" class="mb-4">
                            <div class="mb-3">
                                <label for="msisdn" class="form-label">Phone Number</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="msisdn" 
                                       name="msisdn" 
                                       value="<?php echo htmlspecialchars($msisdn); ?>" 
                                       placeholder="Enter your phone number">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg btn-subscribe">
                                    Generate Subscription Link
                                </button>
                            </div>
                        </form>
                        
                        <?php if ($showLink && $requestUrl): ?>
                        <div class="mt-4">
                            <div class="alert alert-success p-3" role="alert">
                                <h4 class="alert-heading mb-3">Subscription Link Generated!</h4>
                                <p class="mb-3">Click the button below to proceed with your subscription:</p>
                                <div class="d-grid mb-4">
                                    <a href="<?php echo htmlspecialchars($requestUrl); ?>" 
                                       class="btn btn-success btn-lg btn-subscribe"
                                       target="_blank">
                                        Click to Subscribe
                                    </a>
                                </div>
                                <hr>
                                <div class="mt-3">
                                    <h5>Request Details:</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered details-table">
                                            <tr>
                                                <th class="bg-light">API Key</th>
                                                <td><code><?php echo htmlspecialchars($apiKey); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Request ID</th>
                                                <td><code><?php echo htmlspecialchars($requestId); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Request Time</th>
                                                <td><code><?php echo htmlspecialchars($requestTime); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Signature</th>
                                                <td><code><?php echo htmlspecialchars($signature); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Redirect URL</th>
                                                <td><code><?php echo htmlspecialchars($redirectUrl); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">MSISDN</th>
                                                <td><code><?php echo htmlspecialchars($msisdn); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Complete URL</th>
                                                <td>
                                                    <div class="text-break">
                                                        <code><?php echo htmlspecialchars($requestUrl); ?></code>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
