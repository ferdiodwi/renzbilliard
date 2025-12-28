# 🎱 RenzBilliard - Billiard Management System

> Modern web-based management system for billiard venues with integrated POS, session tracking, and real-time alerts.

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-blue.svg)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

---

## ✨ Features

### 🎮 Core Features

- **📊 Dashboard** - Real-time statistics and business insights
- **🎯 Table Management** - Monitor all billiard tables status and sessions
- **⏱️ Session Tracking** - Automatic time tracking with smart alerts
- **💰 POS System** - Integrated F&B point of sale with cart management
- **📱 Fully Responsive** - Optimized for desktop, tablet, and mobile devices
- **🔔 Smart Notifications** - Real-time alerts for session expiry and transactions
- **📈 Transaction History** - Complete payment and order records
- **👥 User Management** - Role-based access control (Admin/Cashier)

### 🚀 Advanced Features

- **Client-side Alert Calculation** - Zero-delay notifications with minimal server load
- **Adaptive Polling** - Smart refresh intervals for optimal performance
- **Mobile-First POS** - Floating cart and overlay design for mobile devices
- **Session Alerts** - Warning (5min before) and urgent (expired) sound notifications
- **Dark Mode** - System-wide theme support
- **Notification Center** - Centralized notification panel with badge counter

---

## 🛠️ Tech Stack

### Backend
- **Laravel 10.x** - PHP Framework
- **MySQL** - Database
- **RESTful API** - JSON API endpoints

### Frontend
- **Vue.js 3** - Progressive JavaScript framework
- **Vite** - Next generation frontend tooling
- **TailwindCSS** - Utility-first CSS framework
- **Pinia** - Vue state management
- **Vue Router** - SPA routing

### UI/UX
- **Heroicons** - Beautiful SVG icons
- **TailAdmin** - Admin dashboard template (customized)

---

## 📋 System Requirements

- **PHP** >= 8.1
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **npm** >= 9.x
- **MySQL** >= 5.7 or MariaDB >= 10.3

---

## 🚀 Installation

### 1. Clone Repository

```bash
git clone https://github.com/YOUR_USERNAME/renz-billiard.git
cd renz-billiard
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renz_billiard
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Database Migration & Seeding

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE renz_billiard;"

# Run migrations
php artisan migrate

# Seed initial data (optional)
php artisan db:seed
```

### 6. Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start Development Server

```bash
# Terminal 1: Laravel backend
php artisan serve

# Terminal 2: Vite dev server
npm run dev
```

Visit: `http://localhost:8000`

---

## 👤 Default Credentials

After seeding, you can login with:

**Admin:**
- Email: `admin@renzbilliard.com`
- Password: `password`

**Cashier:**
- Email: `cashier@renzbilliard.com`
- Password: `password`

> ⚠️ **Remember to change default passwords in production!**

---

## 📱 Mobile Testing

For testing on mobile devices (same WiFi network):

1. Update `vite.config.js`:
```javascript
server: {
  host: '0.0.0.0',
  port: 5173,
}
```

2. Find your local IP:
```bash
# Linux/Mac
hostname -I

# Windows
ipconfig
```

3. Access from mobile:
```
http://YOUR_LOCAL_IP:8000
```

---

## 🎯 Key Functionalities

### Session Management
- Start billiard session with customer name and duration
- Real-time countdown display on each table card
- Automatic alerts at 5 minutes remaining
- Urgent alerts when session expires
- Extend session duration
- Stop and proceed to payment

### POS System
- Browse F&B products by category
- Search products
- Add to cart with quantity control
- Mobile-optimized floating cart
- Attach orders to active sessions
- Process payments

### Alert System
- **Session Warnings** - Single beep at 5 minutes remaining
- **Session Expired** - Repeating beep for 30 seconds
- **Transaction Alerts** - Coin sound for new payments
- **Notification Bell** - Visual notification center in header
- **Acknowledged Sessions** - Smart tracking to prevent alert spam

### Performance Optimizations
- Client-side session alert calculation (no server polling for alerts)
- Adaptive refresh intervals:
  - Session data: 10 seconds
  - Transaction check: 60 seconds
  - Table polling: 15 seconds (only on Tables page)
- ~85-90% reduction in server requests compared to naive polling

---

## 🌐 Deployment

### Option 1: Railway.app (Recommended for Testing)

1. Push to GitHub
2. Connect Railway to your repo
3. Add MySQL database
4. Set environment variables
5. Deploy! (automatic)

Free tier: $5 credit/month (~500 hours)

### Option 2: VPS (Production)

**Recommended:** Contabo VPS ($4.50/mo)

```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

### Option 3: Shared Hosting

Minimum requirements:
- PHP 8.1+
- MySQL 5.7+
- SSH access
- Composer support

---

## 📊 Database Schema

### Main Tables

- `billiard_tables` - Table information and status
- `billing_sessions` - Active and historical sessions
- `rates` - Pricing per hour for different table types
- `products` - F&B menu items
- `orders` - F&B orders linked to sessions
- `order_items` - Items in each order
- `transactions` - Payment records
- `users` - Admin and cashier accounts

---

## 🔧 Configuration

### Alert Settings

Users can configure alert preferences in Settings page:
- Toggle session alerts on/off
- Toggle transaction alerts on/off
- Sound notifications control

### Session Duration

Minimum session duration: 2 minutes (configurable in `SessionController.php`)

Default intervals:
- Warning alert: 5 minutes before end
- Expired alert: When time runs out

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Developer

Made with ❤️ by [Your Name]

---

## 📞 Support

For issues and questions:
- **Email:** your.email@example.com
- **GitHub Issues:** [Create an issue](https://github.com/YOUR_USERNAME/renz-billiard/issues)

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Vue.js](https://vuejs.org) - The Progressive JavaScript Framework
- [TailwindCSS](https://tailwindcss.com) - A utility-first CSS framework
- [TailAdmin](https://tailadmin.com) - Admin Dashboard Template

---

**⭐ Star this repo if you find it useful!**
