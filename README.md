# Amsterdam ID ApartHotel Sauna Booking System

A web application for managing sauna bookings at Amsterdam ID ApartHotel.

## Features
- Guest booking interface with 30-minute time slots
- Employee management dashboard with real-time status tracking
- Excel export functionality for booking data
- Booking confirmation system with details and instructions

## Technologies
- PHP Backend (MVC architecture)
- Vue.js 3 Frontend with Options API
- MySQL Database
- Docker deployment

## Setup Instructions
1. Clone this repository
2. Run `docker-compose up`
3. Access the application at http://localhost

## User Interfaces

### Guest Interface
- Simple booking form (no login required)
- Date and time selection with 30-minute slots
- Room number and guest information entry
- Booking confirmation with instructions

### Employee Interface
- Staff login system
- Real-time sauna status monitoring
- Advanced booking management (create, start, complete, cancel)
- Booking history with Excel export
- Extended booking options (24-hour access, variable duration)

## Technical Details
- JWT authentication for employee access
- RESTful API endpoints
- Mobile-responsive design
- Docker-based development and deployment