# Digimart Integration

This PHP-based integration project provides functionality for managing Digimart subscriptions and user management.

## Features

- Subscription management
- User status checking
- List management
- Subscribe/Unsubscribe functionality

## Project Structure

- `index.php` - Main entry point
- `config.php` - Configuration settings (API keys and endpoints)
- `status.php` - Status checking functionality
- `list.php` - List management
- `subscribe.php` - Subscription handling
- `unsubscribe.php` - Unsubscription handling

## Configuration Variables

The following variables need to be configured in `config.php`:

### API Configuration
- `API_KEY` - Your Digimart API key
- `API_SECRET` - Your Digimart API secret
- `REDIRECT_URL` - Your application's redirect URL
- `SUBSCRIBE_API` - Subscription authorization endpoint

### Subscription List API Configuration
- `APPLICATION_ID` - Your Digimart application ID
- `APPLICATION_PASSWORD` - Your application password
- `SUBSCRIPTION_LIST_API` - Endpoint for retrieving subscribers

## Setup

1. Clone the repository
2. Configure your API credentials in `config.php`
3. Ensure your web server meets PHP requirements
4. Set up the appropriate redirect URLs in your Digimart dashboard

## Security Note

Make sure to never commit your actual API credentials to version control. Use environment variables or a separate configuration file for production deployments.

## License

Proprietary - All rights reserved
