# Farm Visit Portal (FVP)

A web-based system for tracking, monitoring, and managing farm visit permits to improve biosecurity and automate manual processes.

## Features

### Core Functionality
- **Digital Farm Visit Permits**: Create, manage, and track farm visit permits
- **User Management**: Role-based access control for Admin and User roles
- **Permit Status Tracking**: Monitor permit statuses in real-time
- **Rescheduling System**: Flexible permit rescheduling capabilities
- **Notifications**: Alert locations about incoming permits
- **Printing Support**: Generate printable farm visit permits

### User Roles
- **System Admin**: Full access to manage users, data, forms, and permits
- **User**: Manage cancellation and received status of permits

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Set up database configuration in `.env`
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Build production assets:
   ```bash
   npm run build
   ```

## Configuration

### Database Setup
Update your `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fvportal
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Timezone
The application is configured to use **Asia/Manila** timezone.

## Usage

1. **Admin Access**: Create and manage user accounts, configure system settings
2. **Permit Creation**: Generate new farm visit permits with required information
3. **Status Tracking**: Monitor permit progress through various stages
4. **Notifications**: Receive alerts for permit updates
5. **Printing**: Generate official permit documents