# YR Team Remap ECU Warranty System

A comprehensive warranty management system for YR Team's ECU remapping services, built with PHP and hosted on Wasmer.io.

## Features

- **User Management**: Customer registration and login system
- **Warranty Tracking**: Check warranty status by ID or phone number
- **Admin Dashboard**: Complete administrative control panel
- **Booking System**: Service booking and payment processing
- **File Upload**: Proof of payment upload functionality
- **WhatsApp Integration**: Automated notifications via WhatsApp Bot
- **Security**: Password hashing, session management, and CSRF protection

## Database Schema

The system uses a MySQL database with the following main tables:
- `admins` - Administrative users
- `users` - Customer accounts
- `bookings` - Service bookings
- `payments` - Payment records
- `warranties` - Warranty information

## Deployment on Wasmer.io

This application is configured for deployment on Wasmer.io with the following setup:

### Prerequisites
- Wasmer CLI installed (`curl https://get.wasmer.io -sSfL | sh`)
- Account on Wasmer.io

### Deployment Steps

1. **Login to Wasmer**:
   ```bash
   wasmer login
   ```

2. **Deploy the application**:
   ```bash
   wasmer deploy
   ```

3. **Access your app**:
   After deployment, Wasmer will provide a URL to access your application.

### Configuration

- **Database**: Remote MySQL database hosted on wasmernet.com
- **PHP Version**: 8.1
- **Port**: 8080
- **Entry Point**: index.php

### Environment Variables

The application automatically detects the Wasmer environment and adjusts settings accordingly.

## Local Development

To run locally with XAMPP:

1. Import `database.sql` into your MySQL database
2. Update database credentials in `config.php` if needed
3. Start Apache and MySQL in XAMPP
4. Access at `http://localhost/yrteam`

## Security Features

- Password hashing with bcrypt
- Session management with timeouts
- CSRF protection
- Input sanitization
- File upload restrictions
- Security headers

## File Structure

```
yrteam/
├── config.php          # Database and app configuration
├── index.php           # Main landing page
├── functions.php       # Helper functions
├── wasmer.toml         # Wasmer deployment config
├── .htaccess           # Apache configuration
├── database.sql        # Database schema
├── admin/              # Admin panel files
├── user/               # User panel files
├── uploads/            # File uploads directory
└── logs/               # Application logs
```

## Support

For support or questions, contact the development team.

## License

This project is proprietary software for YR Team.
