<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digimart Subscription Management</title>
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
        .action-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            height: 100%;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .action-title {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .action-description {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card subscription-card mb-4">
                    <div class="card-header py-3">
                        <h3 class="text-center mb-0">Digimart Subscription Management</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Add Subscription -->
                            <div class="col-md-6 col-lg-3">
                                <a href="subscribe.php" class="text-decoration-none">
                                    <div class="card action-card h-100">
                                        <div class="card-body text-center p-4">
                                            <i class="bi bi-person-plus-fill action-icon text-primary"></i>
                                            <h4 class="action-title">Add Subscription</h4>
                                            <p class="action-description mb-0">Create new subscription for users</p>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Check Status -->
                            <div class="col-md-6 col-lg-3">
                                <a href="status.php" class="text-decoration-none">
                                    <div class="card action-card h-100">
                                        <div class="card-body text-center p-4">
                                            <i class="bi bi-search action-icon text-success"></i>
                                            <h4 class="action-title">Check Status</h4>
                                            <p class="action-description mb-0">View subscription status</p>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Subscription List -->
                            <div class="col-md-6 col-lg-3">
                                <a href="list.php" class="text-decoration-none">
                                    <div class="card action-card h-100">
                                        <div class="card-body text-center p-4">
                                            <i class="bi bi-list-ul action-icon text-info"></i>
                                            <h4 class="action-title">Subscription List</h4>
                                            <p class="action-description mb-0">View all active subscriptions</p>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Unsubscribe -->
                            <div class="col-md-6 col-lg-3">
                                <a href="unsubscribe.php" class="text-decoration-none">
                                    <div class="card action-card h-100">
                                        <div class="card-body text-center p-4">
                                            <i class="bi bi-person-x-fill action-icon text-danger"></i>
                                            <h4 class="action-title">Unsubscribe</h4>
                                            <p class="action-description mb-0">Cancel active subscriptions</p>
                                        </div>
                                    </div>
                                </a>
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