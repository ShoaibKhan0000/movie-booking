# CineTicket (Movie Booking)

A PHP + MySQL movie booking app with user authentication, movie listings, showtime selection, seat booking, ticket generation, and a lightweight admin panel.

## Tech Stack

- PHP (server-rendered pages)
- MySQL (via PDO)
- Bootstrap 5 + Font Awesome (CDN)

## Project Structure

- `/config/db.php` – DB connection and PDO setup
- `/includes/app.php` – shared helpers (session, redirects, validation, flash messages)
- `/includes/header.php`, `/includes/footer.php` – shared layout
- `/assets/css/app.css` – global UI styling
- `/assets/js/app.js` – global UI micro-interactions
- `/admin/index.php` – admin movie management

## Setup

1. Clone repository and place it under your local PHP server root.
2. Create a MySQL database and required tables (`users`, `movies`, `shows`, `bookings`).
3. Configure environment variables (or use defaults in local dev):
   - Copy values from `.env.example` into your runtime environment.
4. Ensure poster uploads directory exists:
   - `assets/images/` (created automatically by admin upload when needed).

## Run

- Serve with Apache/XAMPP/WAMP, or PHP built-in server:
  ```bash
  php -S localhost:8000
  ```
- Open `http://localhost:8000/index.php`

## Notes on Recent Modernization

- Modernized shared styling and responsive polish via `assets/css/app.css`.
- Added consistent micro-interactions for form submissions via `assets/js/app.js`.
- Strengthened backend validation for IDs, seats, and booking flow.
- Added transaction-safe booking to reduce double-booking race conditions.
- Improved admin poster upload validation (MIME/type-safe naming).
- Kept compatibility for old route `movie_details.php` by redirecting to `movie-details.php`.
