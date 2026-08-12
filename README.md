# CinePass — Full-Stack-Ready Movie Booking Web Application

CinePass is a modern movie booking application structure with a polished frontend experience (Tailwind + custom CSS + Chart.js) and a PHP/MySQL backend foundation already present in this repository.

## What This Refactor Adds

- New dedicated frontend template at `/templates/index.html`
- New static asset pipeline:
  - `/static/css/styles.css`
  - `/static/js/app.js`
  - `/static/js/chart-config.js`
- Responsive movie discovery and booking flow:
  - dynamic movie cards
  - interactive seat map (A–E rows, 8 seats each)
  - seat availability states (available/selected/occupied)
  - real-time booking summary and pricing with 18% tax/fee
  - checkout enable/disable state validation
- Admin analytics preview section with KPI cards and a dark-mode Chart.js line chart

## Architecture Breakdown

### Frontend Layer

- **Template:** `templates/index.html`
- **Utility Styling:** Tailwind CSS via CDN
- **Custom Styling:** `static/css/styles.css`
- **UI Logic & State:** `static/js/app.js`
- **Analytics Chart Logic:** `static/js/chart-config.js`

### Existing Backend Layer (Repository)

The repository still includes the existing PHP stack for full-stack expansion:

- `config/db.php` — PDO DB setup
- `includes/app.php` — app/session helper utilities
- multiple PHP pages for auth, movies, seats, bookings, ticketing, and admin

This keeps CinePass ready to integrate the new frontend screens with current backend endpoints.

## UI Features Implemented

1. **Glassmorphism Navbar** with branding, search, location selector, and login/profile CTA
2. **Hero Section** with featured movie spotlight and smooth-scroll “Book Ticket” trigger
3. **Now Showing Grid** generated from JavaScript dataset
4. **Interactive Seat Booking Section**
   - curved cinema screen with neon glow
   - dynamic seat rendering
   - occupied seat locking
5. **Dynamic Booking Summary**
   - selected movie + showtime
   - selected seat list
   - base + 18% convenience/GST + total
6. **Admin Analytics Preview**
   - total tickets sold
   - monthly revenue
   - active users
   - weekly booking trends line chart
7. **Footer** with project branding and profile links

## How to Run

### Frontend-only preview

From repository root, run:

```bash
php -S localhost:8000
```

Open:

- `http://localhost:8000/templates/index.html`

### Existing PHP app routes (optional)

- `http://localhost:8000/index.php`

## Data Model in Frontend Script

Movies are defined in `static/js/app.js` with:

- `id`
- `title`
- `genre`
- `price`
- `image`

Additional metadata like `language` is included for richer UI filtering.

## Next Integration Steps (Backend Wiring)

- persist bookings to database tables through PHP endpoints
- replace static occupied seats with DB-driven seat locks per showtime
- connect KPI and trend chart values to admin analytics queries
- add payment gateway flow to replace local booking confirmation alert
