<?php
require_once 'config.php';

// Initialize variables
$error = '';
$success = '';
$subscriberId = isset($_POST['subscriberId']) ? trim($_POST['subscriberId']) : 'ZTljMjM2YmI4M2NjMDYwNDRlMjAzZmI3NDlhYTRlYTEzNTE3ZDIxNzJmYmUwMDg3MGU1Y2NhYzIzYjI4Mzg4YTpncmFtZWVucGhvbmU';
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
                'subscriberId' => 'tel:' . $subscriberId,
                'action' => '0'
            ];

            // Save request data for debugging
            $debugInfo['request'] = $data;

            // Initialize cURL session
            $ch = curl_init('https://api.digimart.store/subs/unregistration');
            
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
                if ($httpCode === 200) {
                    $success = 'Successfully unsubscribed from the service.';
                } else {
                    throw new Exception("API Error: " . ($result['message'] ?? 'Unknown error'));
                }
            } else {
                throw new Exception('Failed to decode API response: ' . json_last_error_msg());
            }
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
    <title>Unsubscribe - Digimart</title>
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
        .btn-unsubscribe {
            padding: 12px 24px;
            font-size: 1.1rem;
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
                            <h3 class="mb-0">Unsubscribe from Service</h3>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-house-door"></i> Home
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                        <div class="alert alert-danger mb-4" role="alert">
                            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success mb-4" role="alert">
                            <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="subscriberId" class="form-label">Subscriber ID</label>
                                <input type="text" class="form-control form-control-lg" id="subscriberId" 
                                       name="subscriberId" value="<?php echo htmlspecialchars($subscriberId); ?>" 
                                       placeholder="Enter your Subscriber ID" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger btn-unsubscribe">
                                    <i class="bi bi-x-circle me-2"></i>Unsubscribe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>