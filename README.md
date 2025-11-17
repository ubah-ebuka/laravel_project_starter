# Laravel Starter Kit — SPA & Mobile API Boilerplate

A production-ready Laravel starter template that provides developers with a solid foundation for building modern applications using **Sanctum authentication**, **roles & permissions**, **email and phone verification**, and a clean, scalable folder structure.

This boilerplate removes repetitive setup work and helps you start building your product immediately with best practices already implemented. This boilerplate removes repetitive setup work and helps you start building your product immediately with best practices already implemented. Scalable user, role, and permission management is built-in, with Laravel cache used to prevent redundant permission queries.

---

## 🚀 Features

### 🔐 Authentication (Pre-configured)
- Sanctum authentication for **SPA**, **mobile**, and **API** usage  
- Login, logout, registration  
- Password reset (email OTP or signed URL)  
- CSRF-protected SPA sessions  
- API token authentication for mobile apps  

### 👤 User Module
- User profile endpoints  
- Email verification (signed URLs)  
- Phone verification (OTP)  
- Account status helpers (active, suspended, pending, etc.)
- Complete customer and admin endpoints ready for consumption

### 🛡️ Roles & Permissions Management By Admin
- Preconfigured roles, permissions, middleware, and seeders  
- Scalable and reusable permission handling with cached queries to prevent redundant lookups
- Helper functions such as:
  - `userHasPermission()`
  - `isUserActive()`

### 📁 Clean Architecture
- Domain-oriented structure  
- Organized into:
  - Controllers  
  - Services  
  - Repositories  
  - Resources  
  - Rules  
  - Helpers & Traits  
- Written for scalability and maintenance

### 📡 API Structure
- Dedicated route groups:
  - `/web-api` (SPA backend)
  - `/mobile-api` (Mobile app backend)
  - `/panel-api` (Admin dashboard)
- Uniform JSON response format (status, message, data)

### 📨 Notifications
- Email verification notifications  
- Phone OTP messages  
- Extendable notification channels  

### 🛠 Developer Experience
- Clean folder structure  
- Seeders for demo data  
- Config-driven setup  
- Pre-built Insomnia/Postman collections (optional)

---

## 📦 Tech Stack

- Laravel 12  
- Sanctum   
- MySQL
- PHPUnit / Pest 
- Docker

---

## 📁 Install dependencies
- composer install

## Copy environment file
- cp .env.example .env

## Generate app key
- php artisan key:generate

## Configure your database and run migrations
- php artisan migrate --seed