# Ticketing System

A modern ticketing system built with Laravel that allows organizations to manage support requests efficiently.

## Features

### User Management

-   **Role-based Access Control**: Different permissions for admin and regular users
-   **Custom Admin Registration**: Secure route for registering admin users (`/register-admin`)
-   **Password Management**: Self-service password change with real-time validation

### Ticket Management

-   **Ticket Creation**: Users can create tickets with descriptions and attachments
-   **Status Tracking**: Track tickets through their lifecycle (Open → In Progress → Closed)
-   **Assignment System**: Admins can assign tickets to themselves or other admins
-   **Activity Logging**: All ticket actions are logged with timestamp and user information

### Admin Features

-   **Ticket Overview**: View all tickets or filter by status and assignment
-   **Quick Actions**: Change ticket status or assign with one-click actions
-   **Comprehensive Dashboard**: Visual analytics and insights

### Dashboard Analytics

-   **Status Distribution**: Pie chart showing tickets by status (Open, In Progress, Closed)
-   **Ticket Trends**: Line chart showing creation and closure of tickets over time
-   **Admin Workload**: Horizontal bar chart showing ticket distribution among admins
-   **Ticket Aging**: Visualization of active tickets by age groups

## Technical Implementation

### Backend

-   Built with Laravel framework
-   MySQL database with Eloquent ORM
-   RESTful API endpoints for ticket operations

### Frontend

-   Bootstrap-based responsive design
-   Interactive charts with ApexCharts
-   Real-time form validation with JavaScript

### Security Features

-   Authentication using Laravel's built-in auth system
-   CSRF protection for all forms
-   Validation of all user inputs
-   Role-based access control for routes and features

## Installation

1. Clone the repository

    ```
    git clone <repository-url>
    ```

2. Install dependencies

    ```
    composer install
    npm install
    ```

3. Configure environment

    ```
    cp .env.example .env
    php artisan key:generate
    ```

4. Set up database

    ```
    php artisan migrate
    ```

5. Create the first admin user

    ```
    php artisan tinker
    $admin = new \App\Models\User();
    $admin->name = 'Admin User';
    $admin->email = 'admin@example.com';
    $admin->password = Hash::make('password');
    $admin->role_id = 1; // Admin role
    $admin->save();
    exit
    ```

6. Start the application
    ```
    php artisan serve
    ```

## Key Routes

-   `/login` - User login
-   `/register` - Regular user registration
-   `/register-admin` - Admin user registration (secured)
-   `/tickets` - Ticket listing
-   `/tickets/create` - Create new ticket
-   `/change-password` - Password change form
-   `/home` - Dashboard with analytics

## Dashboard

The system provides a comprehensive dashboard with multiple visualizations:

1. **Summary Cards**

    - Total tickets count
    - Open tickets count
    - In Progress tickets count
    - Closed tickets count

2. **Status Distribution Chart**

    - Visual breakdown of tickets by current status

3. **Ticket Trends Chart**

    - 14-day view of ticket creation and closure patterns
    - Helps identify support volume patterns

4. **Admin Assignment Chart** (Admin view only)

    - Shows which admin users have the most assigned tickets
    - Helps balance workload among the team

5. **Ticket Age Distribution** (Admin view only)
    - Highlights tickets by age groups
    - Identifies potentially stale tickets requiring attention

## Usage

### User Workflows

#### Regular Users

-   Register an account
-   Create and view their own tickets
-   Update open tickets
-   Change their password

#### Admin Users

-   Register via the admin registration route
-   View all tickets in the system
-   Assign tickets to themselves or other admins
-   Change ticket statuses
-   Access the analytics dashboard

## License

[MIT License](LICENSE)

## Technical Architecture

### Database Schema

The system uses the following key tables:

-   `users` - User accounts with role associations
-   `roles` - User roles (Admin, User)
-   `tickets` - Support tickets with metadata
-   `ticket_activities` - Audit log of all ticket changes
-   `attachments` - Files attached to tickets

### Key Models

-   `User` - Represents system users with role relationships
-   `Role` - Defines user roles and permissions
-   `Ticket` - Core ticket entity with relationships
-   `TicketActivity` - Records all actions on tickets
-   `Attachment` - Handles file uploads

### Authentication

-   Uses Laravel's built-in authentication system
-   Extended with custom middleware for role-based permissions
-   Custom password change functionality with validation

### Controllers

-   `TicketController` - Core CRUD operations for tickets
-   `HomeController` - Dashboard and analytics
-   `Auth/PasswordChangeController` - Password management

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Acknowledgements

-   Laravel framework
-   Bootstrap UI framework
-   ApexCharts for data visualization
-   All open-source contributors
