<?php
require_once 'config.php';

// Function to get current UTC time from NTP server
function getCurrentUTCTimeFromNTP($host = 'pool.ntp.org', $port = 123) {
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
    $unixTime = $timestamp - 2208988800;

    // Format UTC time in ISO 8601 format with milliseconds
    $current_time_utc = (new DateTime('@' . $unixTime))->format('Y-m-d\TH:i:s.v\Z');

    return $current_time_utc;
}

// Initialize variables
$msisdn = isset($_POST['msisdn']) ? $_POST['msisdn'] : '';
$error = '';
$subscriptionUrl = '';
$request_id = '';
$current_time_utc = '';
$hashed_signature = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($msisdn)) {
        $error = 'Phone number is required';
    } else {
        try {
            // Get current UTC time
            $current_time_utc = getCurrentUTCTimeFromNTP();
            
            // Generate request ID (15 digits)
            $request_id = str_pad(mt_rand(1, 999999999999999), 15, '0', STR_PAD_LEFT);
            
            // Create signature
            $signature_data = API_KEY . '|' . $current_time_utc . '|' . API_SECRET;
            $hashed_signature = hash('sha512', $signature_data);
            
            // Build the API endpoint URL
            $subscriptionUrl = SUBSCRIBE_API . '?' . http_build_query([
                'apiKey' => API_KEY,
                'requestId' => $request_id,
                'requestTime' => $current_time_utc,
                'signature' => $hashed_signature,
                'redirectUrl' => REDIRECT_URL,
                'msisdn' => $msisdn
            ]);
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subscription - Digimart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .subscription-card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
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
        pre code {
            font-size: 0.875rem;
            color: #333;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
            display: block;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .api-response-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .collapse-button:hover {
            background-color: #e9ecef;
        }
        .collapse-button:focus {
            box-shadow: none;
            outline: none;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card subscription-card">
                    <div class="card-header py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Add New Subscription</h3>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
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
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">
                                        <i class="bi bi-phone"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="msisdn" 
                                           name="msisdn" 
                                           value="<?php echo htmlspecialchars($msisdn); ?>" 
                                           placeholder="Enter phone number (e.g., 01773301138)"
                                           required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg btn-subscribe">
                                    <i class="bi bi-person-plus-fill me-2"></i> Create Subscription
                                </button>
                            </div>
                        </form>
                        
                        <?php if ($subscriptionUrl): ?>
                        <div class="mt-4">
                            <div class="alert alert-success p-3" role="alert">
                                <h4 class="alert-heading mb-3">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    Subscription Link Generated!
                                </h4>
                                <p class="mb-3">Click the button below to proceed with the subscription:</p>
                                <div class="d-grid mb-4">
                                    <a href="<?php echo htmlspecialchars($subscriptionUrl); ?>" 
                                       class="btn btn-success btn-lg btn-subscribe"
                                       target="_blank">
                                        <i class="bi bi-box-arrow-up-right me-2"></i>
                                        Complete Subscription
                                    </a>
                                </div>
                                <hr>
                                <div class="mt-3">
                                    <h5>
                                        <i class="bi bi-info-circle me-2"></i>
                                        Request Details:
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered details-table">
                                            
                                            <tr>
                                                <th class="bg-light">Request ID</th>
                                                <td><code><?php echo htmlspecialchars($request_id); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Request Time</th>
                                                <td><code><?php echo htmlspecialchars($current_time_utc); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Signature</th>
                                                <td><code><?php echo htmlspecialchars($hashed_signature); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Redirect URL</th>
                                                <td><code><?php echo htmlspecialchars(REDIRECT_URL); ?></code></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Phone Number</th>
                                                <td><code><?php echo htmlspecialchars($msisdn); ?></code></td>
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