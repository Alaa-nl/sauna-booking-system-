# Sauna Booking System

A full-stack web application for managing sauna bookings in a hotel environment. The system allows guests to book sauna time slots and staff members to manage bookings and sauna status.

## Features

- Guest booking form for easy reservations
- Employee dashboard for managing bookings and sauna status
- Admin dashboard for user management
- JWT authentication for secure access
- Responsive design for all device sizes
- QR code generation for bookings

## Login Credentials

### Admin Access
- Username: **admin**
- Password: **admin123**

### Employee Access
- Username: **employee**
- Password: **employee123**

## Getting Started

### Prerequisites
- Docker and Docker Compose

### Installation & Running

1. Clone the repository
   ```
   git clone https://github.com/yourusername/sauna-booking-system.git
   cd sauna-booking-system
   ```

2. Start the application using Docker Compose
   ```
   docker-compose up
   ```

3. The application will be available at:
   - Frontend: http://localhost
   - API: http://localhost/api
   - PHPMyAdmin: http://localhost:8080

## System Architecture

### Backend
- PHP REST API with JWT authentication
- MariaDB database
- MVC architecture with Controller, Service, and Repository layers

### Frontend
- Vue.js 3 with Composition API
- Vue Router for navigation
- Pinia for state management
- Custom CSS with responsive design

## API Endpoints

### Authentication
- POST `/api/users/login` - Login with username and password
- PUT `/api/users/change-password` - Change user password

### Bookings
- GET `/api/bookings` - Get all bookings (paginated)
- GET `/api/bookings/{id}` - Get a specific booking
- POST `/api/bookings` - Create a new booking
- PUT `/api/bookings/{id}` - Update a booking
- DELETE `/api/bookings/{id}` - Delete a booking
- GET `/api/bookings/{id}/qrcode` - Get QR code for a booking

### Users (Admin only)
- GET `/api/users` - Get all users (paginated)
- POST `/api/users` - Create a new user
- PUT `/api/users/{id}` - Update a user
- DELETE `/api/users/{id}` - Delete a user
- PUT `/api/users/{id}/reset-password` - Reset a user's password

### Sauna Status
- GET `/api/sauna/status` - Get current sauna status
- PUT `/api/sauna/status` - Update sauna status

## License
This project is licensed under the MIT License.