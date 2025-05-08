# Crafts Platform

A simple web platform designed to connect talented artisans with beneficiaries through profile showcases, training courses, and an admin management panel. Features include multi-role login (Admin, Artisan, Beneficiary), an Artisan dashboard to manage profiles and courses, course management with material uploads, file attachments for each course, and an Admin dashboard to manage users and content. The platform supports an Arabic RTL layout while also allowing English content. This project is simple and adaptable for further development.

## Tech Stack

The project uses PHP for the backend, MySQL (`new_crafts_db`) for the database, and HTML, CSS, JS for the frontend with an RTL-friendly design.

## Project Structure

crafts-platform/
│
├── admin/             → Admin-related interfaces
├── assets/            → Static files (images, CSS, etc.)
├── auth/              → Authentication & main app logic
│   ├── add_course.php
│   ├── admin_dashboard.php
│   ├── artisan_dashboard.php
│   ├── config.php
│   ├── course_started.php
│   ├── delete_item.php
│   ├── download.php
│   ├── edit_course.php
│   ├── edit_item.php
│   ├── enroll_course.php
│   ├── favorites.php
│   ├── forgot_password.php
│   ├── login.php
│   ├── main.php
│   ├── my_courses.php
│   ├── payment.php
│   ├── profile.php
│   ├── register.php
│   ├── register_artisan.php
│   ├── view_enrolled.php
│   ├── view_item.php
│   └── database/
│       └── new_crafts_db (3).sql
├── components/        → Reusable HTML/PHP components
├── public/            → Public assets like entry point or landing
└── uploads/           → Uploaded files (images, PDFs, materials)

## Database

Import the `new_crafts_db (3).sql` file into phpMyAdmin under a database named `new_crafts_db`.

## How to Run

1. Ensure you have XAMPP installed and running on your local machine.
2. Place the project folder in `C:\xampp\htdocs\`
3. Start Apache and MySQL via XAMPP
4. Access the app at: `http://localhost/crafts-platform/auth/login.php`

## Notes

This project is for educational and social development purposes. Contributions are welcome. Feel free to ⭐ the repo if you find it helpful!
