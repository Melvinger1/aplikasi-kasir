# Cash Register System

A web-based cash register application for minimarkets and fast-food restaurants built with PHP, MySQL, and JavaScript.

## Features

- Product Management: Add, view, and manage products with stock tracking
- Sales Transactions: Two interfaces available:
  - Dashboard Sales: Integrated sales section in main dashboard
  - POS System: Standalone POS interface for focused sales operations
- Payment Processing: Cash payment handling with proper validation
- Receipt Generation: Professional HTML receipt generation after successful payment
- User Authentication: Secure login for cash register operators
- Improved Code Structure: Organized and documented code for better maintainability
- Responsive Design: Works on different screen sizes

## Technology Stack

- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Database: MySQL
- Server: Apache (recommended)

## Installation

1. Clone or download this repository to your web server directory
2. Create a MySQL database (e.g., `cash_register_db`)
3. Import the database schema from `database/schema.sql`
4. Update database credentials in `includes/db_connect.php`:
   - `DB_HOST`: Your database host (usually localhost)
   - `DB_USER`: Your database username
   - `DB_PASS`: Your database password
   - `DB_NAME`: Your database name (cash_register_db)

5. Access the application via your web browser
6. Use the default login credentials:
   - Username: `admin`
   - Password: `password123`

## Usage

1. Log in with valid credentials
2. Manage products in the "Products" section
3. Process sales in the "Sales" section
4. View sales reports in the "Reports" section
5. Log out when finished

## Security Note

For production use:
- Change the default admin password
- Use strong, unique passwords
- Implement proper password hashing
- Consider enabling HTTPS
- Regularly backup your database

## Files Structure

- `index.php`: Main application interface with dashboard, products, sales, and reports sections
- `pos_system.html`: Standalone POS system interface for sales transactions
- `product_management.html`: Product management interface for viewing products
- `add_product_form.html`: Product addition interface
- `system_overview.html`: System documentation and testing interface
- `login.php`: Login page
- `logout.php`: Logout functionality
- `api/product_api.php`: Product management API endpoints
- `api/sales_api.php`: Sales and payment processing API endpoints
- `api/receipt_api.php`: Receipt generation API endpoints
- `includes/db_connect.php`: Database connection configuration
- `includes/product_functions.php`: Product-related database functions
- `includes/sales_functions.php`: Sales and payment processing functions
- `includes/receipt_functions.php`: Receipt generation functions
- `css/common.css`: Common stylesheet for all pages
- `css/style.css`: Original stylesheet
- `js/main.js`: Main JavaScript functionality for index.php
- `database/`: Database initialization scripts

## Database Schema

The application uses the following tables:
- `products`: Stores product information
- `customers`: Stores customer information (optional)
- `transactions`: Stores transaction records
- `transaction_items`: Stores items in each transaction

## API Endpoints

- `api/product_api.php`: Product management API
- `api/sales_api.php`: Sales and payment processing API
- `api/receipt_api.php`: Receipt generation API

## Demo Credentials

- Username: admin
- Password: password123