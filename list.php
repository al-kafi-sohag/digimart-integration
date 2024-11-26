<?php
require_once 'config.php';

// Initialize variables
$error = '';
$subscribers = [];
$statusCode = '';
$requestId = '';
$nextPageNumber = -1;
$moreDataAvailable = false;
$debugInfo = [];

// Current page number from GET parameter, default to 1
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

try {
    // Prepare the request data
    $data = [
        'applicationId' => APPLICATION_ID,
        'password' => APPLICATION_PASSWORD,
        'version' => '2.0',
        'status' => 'REGISTERED',
        'requestPage' => $currentPage
    ];

    // Save request data for debugging
    $debugInfo['request'] = $data;

    // Initialize cURL session
    $ch = curl_init(SUBSCRIPTION_LIST_API);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
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
        $subscribers = $result['subscribers'] ?? [];
        $statusCode = $result['statusCode'] ?? '';
        $requestId = $result['requestId'] ?? '';
        $nextPageNumber = $result['nextPageNumber'] ?? -1;
        $moreDataAvailable = $result['moreDataAvailable'] ?? false;
        
        // Check API status code
        if ($statusCode !== '0' && $statusCode !== '') {
            throw new Exception("API Error: Status Code $statusCode");
        }
    } else {
        throw new Exception('Failed to decode API response: ' . json_last_error_msg());
    }
} catch (Exception $e) {
    $error = $e->getMessage();
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
    <title>Subscription List - Digimart</title>
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
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.075);
        }
        .subscriber-count {
            font-size: 0.9rem;
            color: #6c757d;
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
            <div class="col-md-10">
                <div class="card subscription-card">
                    <div class="card-header py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Subscription List</h3>
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
                        
                        <?php if (!$error && empty($subscribers)): ?>
                        <div class="alert alert-info mb-4">
                            No subscribers found.
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($subscribers)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>MSISDN</th>
                                        <th>Status</th>
                                        <th>Subscription Date</th>
                                        <th>Last Renewal</th>
                                        <th>Next Renewal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subscribers as $subscriber): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subscriber['msisdn']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $subscriber['status'] === 'REGISTERED' ? 'success' : 'warning'; ?>">
                                                <?php echo htmlspecialchars($subscriber['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($subscriber['subscriptionDate']); ?></td>
                                        <td><?php echo formatDate($subscriber['lastRenewalDate']); ?></td>
                                        <td><?php echo formatDate($subscriber['nextRenewalDate']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($moreDataAvailable): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <a href="?page=<?php echo $nextPageNumber; ?>" class="btn btn-primary">
                                Load More <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- API Response Section -->
                        <div class="mt-4">
                            <button class="btn btn-outline-secondary w-100 collapse-button" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#apiResponse">
                                <i class="bi bi-code-slash"></i> Show API Response
                            </button>
                            <div class="collapse mt-3" id="apiResponse">
                                <pre><code><?php echo htmlspecialchars(json_encode($result ?? [], JSON_PRETTY_PRINT)); ?></code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>