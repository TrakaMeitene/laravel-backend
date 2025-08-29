# ⚡ Laravel Backend API

<div align="center">

[![GitHub stars](https://img.shields.io/github/stars/TrakaMeitene/laravel-backend?style=for-the-badge)](https://github.com/TrakaMeitene/laravel-backend/stargazers)

[![GitHub forks](https://img.shields.io/github/forks/TrakaMeitene/laravel-backend?style=for-the-badge)](https://github.com/TrakaMeitene/laravel-backend/network)

[![GitHub issues](https://img.shields.io/github/issues/TrakaMeitene/laravel-backend?style=for-the-badge)](https://github.com/TrakaMeitene/laravel-backend/issues)

[![GitHub license](https://img.shields.io/github/license/TrakaMeitene/laravel-backend?style=for-the-badge)](LICENSE)

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-brightgreen.svg)](https://www.php.net/)

**A robust and scalable backend API built with Laravel.**

</div>

## 📖 Overview

Pierakstspie API is a Laravel-based backend that powers the Pierakstspie.lv booking platform, providing secure authentication (Laravel Sanctum), user and role management, client and contact handling with custom fields, service and schedule management, and integration with a Next.js frontend, and extensions such as invoicing and financial reporting.

## 🛠️ Tech Stack

* **Backend:**
    * [![Laravel](https://img.shields.io/badge/laravel-%20-orange.svg)](https://laravel.com/)
    * PHP 
    * MySQL 


## 🚀 Quick Start

### Prerequisites

* PHP >= 8.1
* Composer
* A MySQL database server

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/TrakaMeitene/laravel-backend.git
   cd laravel-backend
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment:**
   ```bash
   cp .env.example .env
   # Configure database credentials and other environment variables in .env
   ```

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

5. **Database migrations:**
   ```bash
   php artisan migrate
   ```

6. **Start the development server (if applicable,  depends on app specifics):**
   ```bash
   php artisan serve
   ```

## 📁 Project Structure

```
laravel-backend/
├── app/
├── artisan
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── lang/
├── package-lock.json
├── package.json
├── phpunit.xml
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
└── vite.config.js
```

## ⚙️ Configuration

Configuration is primarily managed through the `.env` file (created by copying `.env.example`).  This file contains database credentials, API keys, and other environment-specific settings.  Laravel's built-in configuration system is also used, with settings stored within the `config` directory.

### Environment Variables (Example from .env.example)

The `.env.example` file provides examples of required environment variables. These should be populated in the `.env` file before running the application.  Specific variables and their purpose will depend on the features of this particular application.

## 🧪 Testing

The project utilizes PHPUnit for testing.  Test cases are located in the `tests` directory.  To run tests:

```bash
./vendor/bin/phpunit
```


## 📄 License

This project is NOT licensed 
---

<div align="center">

**Made with ❤️ by TrakaMeitene**

</div>

