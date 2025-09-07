# Telegram Clone - Laravel MySQL WebSockets

A modern Telegram-like messaging application built with Laravel, MySQL, and WebSockets for real-time communication.

## 🚀 Features

- **Real-time Messaging**: WebSocket-powered instant messaging
- **User Authentication**: Secure user registration and login
- **Modern UI**: Clean and responsive user interface
- **Database**: MySQL for reliable data storage
- **Laravel Framework**: Built with Laravel for robust backend functionality

## 🛠️ Tech Stack

- **Backend**: Laravel 11.x
- **Database**: MySQL
- **Frontend**: Blade templates with modern CSS/JS
- **Real-time**: WebSockets for live messaging
- **Authentication**: Laravel's built-in authentication system

## 📋 Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL 5.7 or higher
- Node.js and NPM (for frontend assets)

## 🚀 Installation

1. Clone the repository:
```bash
git clone https://github.com/azizbek-web-dev/Telegram-Clone-Laravel-MySQL-WebSockets-.git
cd Telegram-Clone-Laravel-MySQL-WebSockets-
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Copy environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=telegram_clone
DB_USERNAME=root
DB_PASSWORD=
```

7. Run migrations:
```bash
php artisan migrate
```

8. Compile frontend assets:
```bash
npm run build
```

9. Start the development server:
```bash
php artisan serve
```

10. Open your browser and visit `http://localhost:8000`

## 🎨 Frontend Features

- **Modern Authentication**: Clean login and registration forms
- **Real-time Chat Interface**: Telegram-like messaging interface
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Dark Mode Support**: Automatic dark mode detection
- **Minimalistic UI**: Clean, professional design
- **API Integration**: Full integration with backend API
- **File Upload Support**: Profile images and media messages
- **Search Functionality**: User and chat search
- **Modal Dialogs**: Smooth interactions for creating chats

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Contact

For any questions or suggestions, please open an issue on GitHub.