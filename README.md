# Facility Booking System

A complete facility booking system built with Laravel (backend) and Vue.js (frontend).

## Features

- **User Authentication**: Register, login, logout, and password recovery
- **Facility Browsing**: Browse facilities by category with filtering
- **Booking System**: Book facilities with date and time slot selection
- **Payment Integration**: Multiple payment methods support
- **Admin Panel**: Manage facilities, bookings, categories, and users
- **Responsive Design**: Mobile-friendly interface

## Tech Stack

### Backend (Laravel)
- Laravel 11
- MySQL Database
- Laravel Sanctum for API authentication
- RESTful API architecture

### Frontend (Vue.js)
- Vue 3 with Composition API
- Pinia for state management
- Vue Router for navigation
- Axios for HTTP requests
- Vite as build tool

## Project Structure

```
web-booking/
├── laravel/ (Backend)
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Models/
│   │   ├── Mail/
│   │   └── Http/Middleware/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/api.php
│
└── frontend/ (Vue.js)
    ├── src/
    │   ├── components/
    │   ├── views/
    │   ├── stores/
    │   ├── router/
    │   ├── utils/
    │   └── api/
    └── public/images/facilities/
```

## Installation

### Backend Setup (Laravel)

1. Navigate to the Laravel directory:
```bash
cd laravel
```

2. Install PHP dependencies:
```bash
composer install
```

3. Create a copy of the .env file:
```bash
copy .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in the `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=facility_booking
DB_USERNAME=root
DB_PASSWORD=
```

6. Run migrations:
```bash
php artisan migrate
```

7. Seed the database (optional):
```bash
php artisan db:seed --class=FacilitySeeder
```

8. Start the Laravel development server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

### Frontend Setup (Vue.js)

1. Navigate to the frontend directory:
```bash
cd frontend
```

2. Install Node.js dependencies:
```bash
npm install
```

3. Update the API base URL in `src/api/axios.js` if needed:
```javascript
const instance = axios.create({
  baseURL: 'http://localhost:8000/api',
  // ...
})
```

4. Start the development server:
```bash
npm run dev
```

The frontend will be available at `http://localhost:5173`

## API Endpoints

### Public Routes
- `POST /api/register` - Register a new user
- `POST /api/login` - User login
- `POST /api/forgot-password` - Request password reset
- `GET /api/categories` - Get all categories
- `GET /api/facilities` - Get all facilities
- `GET /api/facilities/{id}` - Get facility details

### Protected Routes (Require Authentication)
- `POST /api/logout` - User logout
- `GET /api/me` - Get current user
- `GET /api/time-slots` - Get time slots
- `GET /api/time-slots/available` - Get available time slots
- `GET /api/bookings` - Get user bookings
- `POST /api/bookings` - Create a booking
- `POST /api/bookings/{id}/cancel` - Cancel a booking

### Admin Routes (Require Admin Role)
- Facility management (CRUD)
- Category management (CRUD)
- Booking management
- Time slot management

## Database Schema

### Users Table
- id, name, email, password, phone, role, timestamps

### Categories Table
- id, name, description, icon, timestamps

### Facilities Table
- id, category_id, name, description, location, capacity, price_per_hour, image, is_available, timestamps

### Time Slots Table
- id, facility_id, start_time, end_time, timestamps

### Bookings Table
- id, user_id, facility_id, time_slot_id, booking_date, number_of_people, status, notes, timestamps

### Payments Table
- id, booking_id, amount, payment_method, payment_status, transaction_id, paid_at, timestamps

## Key Features Implementation

### Authentication
- Uses Laravel Sanctum for token-based authentication
- Tokens stored in localStorage on the frontend
- Axios interceptors for automatic token inclusion

### Booking Flow
1. User browses facilities
2. Selects a facility and date
3. Views available time slots
4. Fills booking form
5. Confirms booking
6. Optionally processes payment

### Admin Features
- Dashboard with statistics
- Manage facilities (add, edit, delete)
- Manage bookings (view, update status)
- Manage categories
- User management

## Development

### Adding New Features

1. **Backend (Laravel)**:
   - Create controller in `app/Http/Controllers/Api/`
   - Add routes in `routes/api.php`
   - Create/update models in `app/Models/`
   - Create migrations if needed

2. **Frontend (Vue.js)**:
   - Create components in `src/components/`
   - Add views in `src/views/`
   - Create/update stores in `src/stores/`
   - Add routes in `src/router/index.js`

### Testing

**Backend:**
```bash
php artisan test
```

**Frontend:**
```bash
npm run test
```

## Deployment

### Backend Deployment
1. Set up production environment
2. Configure `.env` for production
3. Run migrations on production database
4. Set up web server (Apache/Nginx)
5. Configure SSL certificate

### Frontend Deployment
1. Build for production:
```bash
npm run build
```
2. Deploy the `dist` folder to your hosting service
3. Configure routing (important for SPA)

## Security Considerations

- CSRF protection enabled
- SQL injection prevention with Eloquent ORM
- XSS protection with Vue's template system
- Password hashing with bcrypt
- API rate limiting
- Input validation on both frontend and backend

## Future Enhancements

- Email notifications for bookings
- Calendar integration
- Real-time availability updates
- Rating and review system
- Advanced search and filters
- Multiple image upload for facilities
- PDF receipt generation
- Analytics and reporting

## License

This project is open-source and available for educational purposes.

## Support

For issues or questions, please create an issue in the repository.
