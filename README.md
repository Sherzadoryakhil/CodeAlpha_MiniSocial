# Connectly

A mini social media platform built with **Laravel 12** for the CodeAlpha Internship (Task 2).
Users can register, create a profile, post text and images, comment, like, and follow each other.
The interface is inspired by a WhatsApp-style green palette and follows basic HCI principles
(clear feedback, consistent layout, accessible forms, mobile-friendly).

> Screenshots: see the `docs/screenshots/` folder (add yours using `docs/SCREENSHOTS.md`).

## Features

- **Authentication:** register, log in, log out (Laravel Breeze, restyled with Bootstrap 5).
- **Profiles:** name, unique username, bio (max 160), profile photo, public profile page with posts.
- **Posts:** create, view, edit, delete your own posts, with optional image (max 2 MB). Feed shows all users' posts, newest first, 10 per page.
- **Comments:** add comments (max 500), edit/delete your own. Feed shows the latest 2, post page shows all.
- **Likes:** like/unlike a post, one like per user per post, live count.
- **Follow system:** follow/unfollow users, follower and following lists, counts on profiles. You cannot follow yourself.
- **Polish:** character counters, loading state on submit, dismissible alerts, page titles, friendly 403/404/419/429/500 pages, skip link and ARIA labels.

Out of scope by design: search/explore, email verification, password reset.

## Tech stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2, Laravel 12 |
| Database | MySQL 8 |
| Frontend | Blade, HTML, CSS, JavaScript, Bootstrap 5 (no React or Vue) |
| Auth | Laravel Breeze (Blade stack) |
| Build | Vite, Node.js |
| Tests | PHPUnit feature tests (in-memory SQLite) |

## Requirements

PHP 8.2+, Composer 2, Node.js 20+ and npm, MySQL 8 (for example via XAMPP), Git.

## Installation

```bash
git clone https://github.com/<your-username>/connectly.git
cd connectly

composer install
npm install

cp .env.example .env        # on Windows PowerShell: copy .env.example .env
php artisan key:generate
```

Create an empty MySQL database (for example `connectly`) and set it in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=connectly
DB_USERNAME=root
DB_PASSWORD=
```

Then:

```bash
php artisan migrate --seed     # creates tables and demo data
php artisan storage:link       # makes uploaded images public
npm run build                  # or: npm run dev while developing
php artisan serve
```

Open http://127.0.0.1:8000.

**Demo account (from the seeder):** `demo@connectly.test` / `password`

## Running the tests

```bash
php artisan test
```

Tests use an in-memory SQLite database, so your MySQL data is never touched.
They cover authentication, profiles, posts, comments, likes, follows, security and UI polish.

## Security measures

- CSRF protection on every form (all write routes are in the `web` group; tested).
- Passwords hashed (bcrypt); login lockout after 5 failed attempts; registration throttled (10 per minute).
- Validation in Form Requests; ownership enforced with Policies (users can edit/delete only their own content).
- Mass-assignment protection: `user_id`, `post_id`, `profile_photo_path` cannot be set from a form.
- Output escaped by Blade (XSS); queries use Eloquent/parameter binding (SQL injection).
- Uploads: images only (jpg, jpeg, png, webp), max 2 MB, content checked, random file names; SVG and disguised files are rejected.
- Database integrity: unique like and follow rows, cascading foreign keys, MySQL triggers blocking self-follows.
- Security headers: X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy.

## Deploying to production (notes)

- Set `APP_ENV=production` and **`APP_DEBUG=false`**, and never commit `.env`.
- Serve over HTTPS and consider adding an HSTS header at the web server.
- Run `php artisan config:cache route:cache view:cache` and `npm run build`.
- A Content-Security-Policy header is not included because the views use inline scripts and styles.

## Project structure (main parts)

```
app/Http/Controllers   Post, Comment, Like, Follow, Profile controllers
app/Http/Requests      Validation and authorization
app/Policies           Post and Comment ownership rules
app/Models             User, Post, Comment
database/migrations    users, posts, comments, likes, follows (+ triggers)
resources/views        Blade pages and components
tests/Feature          Feature tests
docs/                  Testing guide, screenshots and submission checklists
```

## Author

<Sherzad Oryakhil> - CodeAlpha Internship, Task 2.

## License

For educational and portfolio purposes.
