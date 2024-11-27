<?php
require_once 'config.php';

// Initialize variables
$error = '';
$subscriberInfo = null;
$subscriberId = isset($_POST['subscriberId']) ? trim($_POST['subscriberId']) : '';
$debugInfo = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($subscriberId)) {
        $error = 'Subscriber ID is required';
    } else {
        try {
            // Prepare the request data
            $data = [
                'applicationId' => APPLICATION_ID,
                'password' => APPLICATION_PASSWORD,
                'subscriberId' => 'tel:'.$subscriberId
            ];

            // Save request data for debugging
            $debugInfo['request'] = $data;

            // Initialize cURL session
            $ch = curl_init('https://api.digimart.store/subscription/subscriberChargingInfo');
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            // Execute cURL request
            $response = curl_exec($ch);
            
            // Get HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $debugInfo['http_code'] = $httpCode;
            
            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new Exception('Curl error: ' . curl_error($ch));
            }
            
            // Close cURL session
            curl_close($ch);
            
            // Save raw response for debugging
            $debugInfo['raw_response'] = $response;
            
            // Decode JSON response
            $result = json_decode($response, true);
            
            if ($result) {
                if ($result['statusCode'] === 'S1000') {
                    $subscriberInfo = $result['subscriberInfo'][0] ?? null;
                } else {
                    throw new Exception("API Error: " . ($result['statusDetail'] ?? 'Unknown error'));
                }
            } else {
                throw new Exception('Failed to decode API response: ' . json_last_error_msg());
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Function to format date
function formatDate($dateStr) {
    return date('F j, Y g:i A', strtotime($dateStr));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Status - Digimart</title>
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
        .btn-check-status {
            padding: 12px 24px;
            font-size: 1.1rem;
        }
        .details-table td {
            padding: 0.75rem;
        }
        .details-table th {
            width: 200px;
            background-color: #f8f9fa;
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
        .api-debug {
            font-family: monospace;
            font-size: 0.8rem;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-top: 1rem;
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
                            <h3 class="mb-0">Check Subscription Status</h3>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                        <div class="alert alert-danger mb-4" role="alert">
                            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                            
                            <!-- Debug Information -->
                            <div class="api-debug mt-3">
                                <strong>Debug Information:</strong><br>
                                <strong>Request:</strong><br>
                                <?php echo htmlspecialchars(json_encode($debugInfo['request'], JSON_PRETTY_PRINT)); ?><br>
                                <strong>HTTP Code:</strong> <?php echo htmlspecialchars($debugInfo['http_code']); ?><br>
                                <strong>Raw Response:</strong><br>
                                <?php echo htmlspecialchars($debugInfo['raw_response']); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form method="post" class="mb-4">
                            <div class="mb-3">
                                <label for="subscriberId" class="form-label">Subscriber ID</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="subscriberId" 
                                           name="subscriberId" 
                                           value="<?php echo htmlspecialchars($subscriberId); ?>" 
                                           placeholder="Enter subscriber ID"
                                           required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Check Status</button>
                            </div>
                        </form>
                        
                        <?php if ($subscriberInfo): ?>
                        <div class="mt-4">
                            <div class="alert alert-<?php echo $subscriberInfo['subscriptionStatus'] === 'REGISTERED' ? 'success' : 'warning'; ?> mb-4">
                                <h4 class="alert-heading mb-3">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    Subscription Status
                                </h4>
                                <table class="table table-bordered details-table mb-0">
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge bg-<?php echo $subscriberInfo['subscriptionStatus'] === 'REGISTERED' ? 'success' : 'warning'; ?>">
                                                <?php echo htmlspecialchars($subscriberInfo['subscriptionStatus']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Subscriber ID</th>
                                        <td><code><?php echo htmlspecialchars($subscriberInfo['subscriberId']); ?></code></td>
                                    </tr>
                                    <tr>
                                        <th>Request ID</th>
                                        <td><code><?php echo htmlspecialchars($subscriberInfo['subscriberRequestId']); ?></code></td>
                                    </tr>
                                    <tr>
                                        <th>Last Charged Amount</th>
                                        <td><?php echo htmlspecialchars($subscriberInfo['lastChargedAmount']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Last Charged Date</th>
                                        <td><?php echo formatDate($subscriberInfo['lastChargedDate']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status Code</th>
                                        <td><code><?php echo htmlspecialchars($subscriberInfo['statusCode']); ?></code></td>
                                    </tr>
                                    <tr>
                                        <th>Status Detail</th>
                                        <td><?php echo htmlspecialchars($subscriberInfo['statusDetail']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($result)): ?>
                        <!-- API Response Section -->
                        <div class="mt-4">
                            <button class="btn btn-outline-secondary w-100" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#apiResponse">
                                <i class="bi bi-code-slash"></i> Show API Response
                            </button>
                            <div class="collapse mt-3" id="apiResponse">
                                <pre><code><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)); ?></code></pre>
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