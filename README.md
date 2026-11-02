# School Supplies Product Management and Pricing System

A simple PHP web application for managing school supply inventory and processing customer orders.

## Features

- Add, edit, and delete products
- Organize items by category
- Filter products by category and product status
- Mark items as either New or Regular
- Add products to a shopping cart
- Confirm checkout and view a receipt summary
- Track stock levels automatically during checkout

## Project Files

- `index.php` - Main storefront and management interface
- `product.php` - Product class and session initialization

## Requirements

- PHP 7.0 or later
- Apache web server (such as XAMPP or WAMP)
- A browser to access the app

## Setup

1. Place the project folder inside your local web server directory, usually:
   - `C:\xampp\htdocs\`
2. Start Apache in XAMPP.
3. Open the project in your browser using:
   - `http://localhost/SchoolSuppliesMPMPCarillo,Padin,Mallari/`

## How to Use

1. Add products using the "Add New Item" form.
2. Use the category and status filters to browse the catalog.
3. Set a quantity and click "Add to Cart" for any available item.
4. Review the cart and click "Confirm Cart & Checkout".
5. A receipt will be shown with the total order cost.

## Notes

- Product data is stored in PHP session storage while the app is running.
- Default product categories are initialized automatically when the app starts.
- Stock is reduced when a purchase is confirmed.

## License

This project is intended for educational use.
