<div align="center">
  <h1>🎓 EduTrack & Archive</h1>
  <p><b>Enterprise-Level Academic Management & Archiving System</b></p>
  
  [![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
  [![Firebase](https://img.shields.io/badge/Firebase-FFCA28?style=for-the-badge&logo=firebase&logoColor=white)](https://firebase.google.com)
</div>

## 📌 About The Project

**EduTrack & Archive** is a comprehensive backend system designed to manage academic departments, students, researchers, events, and research archiving. The system features a robust **Role-Based Access Control (RBAC)** architecture, **RESTful APIs**, and is integrated with **Firebase** for advanced authentication and real-time operations.

This project is built iteratively following a structured learning phase roadmap, ensuring clean architecture, professional documentation, and scalable backend practices using Laravel 12.

## ✨ Core Features
- **Authentication & Authorization**: Multi-guard authentication mapped to specific user roles (Super Admin, Supervisor, Researcher, Organizer).
- **Academic Archiving**: Upload, manage, and retrieve academic research (PDFs/Docs).
- **Event Management**: Create academic events and manage seat reservations.
- **RESTful APIs**: Clean API endpoints powered by Laravel API Resources and Sanctum.
- **Firebase Integration**: Secondary DB and Auth layer for mobile applications.

## 🗄️ Database Schema & Architecture
Our backend relies on a solid relational database design (MySQL):
- `Users` (Super Admins, Supervisors, Researchers)
- `Departments`
- `Researches` (Files, Metadata)
- `Events` (Academic Events & Seminars)

## 🚀 Getting Started (Development Setup)

### Prerequisites
- PHP 8.2+
- Composer
- MySQL (XAMPP/WAMP or Docker)
- Git

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/edutrack-system.git
   cd edutrack-system
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Setup environment variables:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure your Database in `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=edutrack
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Run migrations & start server:
   ```bash
   php artisan migrate
   php artisan serve
   ```

## 📚 Project Roadmap (Learning Phases)
- [x] **Phase 0**: Setup & Environment 
- [ ] **Phase 1**: Laravel Fundamentals (MVC, Routing, Auth)
- [ ] **Phase 2**: Core Backend (Models, Relations, RBAC)
- [ ] **Phase 3**: Advanced Backend (APIs, Storage, Sanctum)
- [ ] **Phase 4**: Firebase Integration
- [ ] **Phase 5**: GitHub Documentation

## 📄 License
This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
