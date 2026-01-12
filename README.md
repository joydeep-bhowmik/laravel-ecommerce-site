

# Laravel E‑Commerce Site

A **full‑featured e‑commerce platform** built with **Laravel** and **Blade**. This project provides a solid foundation for an online store with user authentication, product catalog, shopping cart, checkout, orders, and basic admin management.

It’s designed to be simple to set up and extend for real‑world online store needs.

## Previews

![Screenshot 1](previews/Screenshot%202026-01-12%20162223.png)
![Screenshot 2](previews/Screenshot%202026-01-12%20162507.png)
![Screenshot 3](previews/Screenshot%202026-01-12%20164811.png)
![Screenshot 4](previews/Screenshot%202026-01-12%20164953.png)

---

## 🚀 Features

### 🛍️ Customer Shopping

* User **registration, login & profile**
* Browse products by **categories**
* **Product details** pages
* Add/remove items in **shopping cart**
* Persistent cart between sessions
* **Checkout** flow

### 📦 Order Management

* Place orders with shipping details
* View **order history**
* Order status tracking

### 🛠 Admin Panel (Basic)

* Manage **products**
* Manage **categories**
* View all orders
* Manage users (optional)

> Note: You can extend this with roles/permissions and advanced dashboards.

---

## 🧱 Tech Stack

**Backend**

* Laravel (PHP)
* MySQL (or any supported relational database)
* Eloquent ORM

**Frontend**

* Blade templates
* Tailwind CSS
* Vite for asset bundling

---

## 📥 Requirements

Before running the app, ensure you have:

* PHP 8.1+
* Composer
* Node.js & npm
* MySQL (or database of choice)
* Laravel CLI

---

## 🛠 Installation & Setup

### 1. Clone the Repo

```bash
git clone https://github.com/joydeep-bhowmik/laravel-ecommerce-site.git
cd laravel-ecommerce-site
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Config

Copy example env and configure:

```bash
cp .env.example .env
```

Update the following (example):

```env
APP_NAME=Laravel E-Commerce
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_DATABASE=ecommerce
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

Generate the app key:

```bash
php artisan key:generate
```

### 4. Database Setup

Run migrations (and optionally seeders if included):

```bash
php artisan migrate
# php artisan db:seed
```

### 5. Build Frontend Assets

```bash
npm run dev
```

Or for production builds:

```bash
npm run build
```

### 6. Run the Application

```bash
php artisan serve
```

Visit: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---




