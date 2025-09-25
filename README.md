# ILDIS - Indonesian Legal Documentation Information System

<p align="center">
  <h3 align="center">JDIH (Jaringan Dokumentasi dan Informasi Hukum)</h3>
  <p align="center">
    Sistem informasi manajemen dokumen hukum Indonesia yang terintegrasi dengan standar JDIHN (Jaringan Dokumentasi dan Informasi Hukum Nasional)
  </p>
</p>

## Tentang ILDIS JDIH

ILDIS (Indonesian Legal Documentation Information System) adalah sistem manajemen dokumen hukum yang dikembangkan untuk mendukung penyimpanan, pengelolaan, dan distribusi dokumen hukum Indonesia secara digital. Sistem ini dibangun menggunakan Laravel 12.x dan Filament 4.x dengan arsitektur modern yang mengikuti standar Clean Architecture dan Domain-Driven Design.

### Fitur Utama

- **Manajemen Dokumen Hukum** - Penyimpanan dan pengelolaan dokumen peraturan perundang-undangan
- **Pencarian Canggih** - Full-text search dengan Meilisearch untuk pencarian dokumen yang akurat
- **Integrasi JDIHN** - Sinkronisasi dengan SATU DATA HUKUM INDONESIA sesuai standar nasional
- **Role-Based Access Control** - Sistem otorisasi berlapis (Super Admin, Admin, Koordinator, Pustakawan)
- **RESTful API** - API lengkap untuk integrasi dengan sistem eksternal
- **Admin Panel Modern** - Interface administrasi dengan Filament 4.x
- **Audit Trail** - Pencatatan aktivitas pengguna dengan Spatie ActivityLog
- **Media Library** - Manajemen file dokumen dengan Spatie MediaLibrary
- **Responsive Design** - Antarmuka yang responsif untuk semua perangkat

## Teknologi yang Digunakan

### Backend
- **Laravel 12.x** - PHP framework modern dengan fitur terdepan
- **PHP 8.3+** - Bahasa pemrograman dengan performa tinggi
- **MySQL 8.0+** - Database relasional untuk penyimpanan data
- **Filament 4.x** - Admin panel modern dengan interface intuitif

### Packages Utama
- **Spatie Permission** - Role dan permission management
- **Spatie MediaLibrary** - File dan media management
- **Spatie ActivityLog** - Audit trail dan logging aktivitas
- **Laravel Scout** - Full-text search engine
- **Meilisearch** - Search engine untuk pencarian cepat
- **Laravel Sanctum** - API authentication

### Frontend
- **Tailwind CSS** - CSS framework utility-first
- **Alpine.js** - JavaScript framework ringan
- **Blade Templates** - Template engine Laravel

## Persyaratan Sistem

- PHP >= 8.3
- MySQL >= 8.0
- Node.js >= 18.x
- Composer >= 2.6
- Meilisearch >= 1.5 (opsional untuk pencarian)

## Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd jdih
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Setup Environment
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jdih
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Setup Meilisearch (Opsional)
Jika menggunakan pencarian canggih:
```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=your_master_key
```

### 6. Jalankan Migration dan Seeder
```bash
# Jalankan migration
php artisan migrate

# Jalankan seeder untuk data awal
php artisan db:seed
```

### 7. Setup Storage dan Permissions
```bash
# Create storage link
php artisan storage:link

# Setup file permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 8. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 9. Jalankan Aplikasi
```bash
# Development server
php artisan serve

# Aplikasi akan tersedia di: http://localhost:8000
# Admin panel: http://localhost:8000/admin
```

## Penggunaan

### Akses Admin Panel
- URL: `/admin`
- Default Super Admin:
  - Email: `admin@jdih.go.id`
  - Password: `password123`

### Role dan Permissions
1. **Super Admin** - Akses penuh ke semua fitur sistem
2. **Admin** - Manajemen dokumen dan pengguna
3. **Koordinator** - Koordinasi alur kerja dokumen
4. **Pustakawan** - Entry dan pengelolaan dokumen

### Mengelola Dokumen
1. Login ke admin panel
2. Navigasi ke menu "Documents"
3. Klik "New Document" untuk menambah dokumen baru
4. Isi metadata dokumen sesuai standar JDIHN
5. Upload file dokumen
6. Publikasikan dokumen

### Menggunakan API
API endpoint tersedia di `/api/v1/`:

#### JDIHN API (Integrasi Nasional)
```
GET /api/v1/jdihn/documents - Daftar dokumen untuk JDIHN
GET /api/v1/jdihn/abstracts - Abstrak dokumen untuk JDIHN
```

#### Public API
```
GET /api/v1/documents - Daftar dokumen publik
GET /api/v1/documents/{id} - Detail dokumen
GET /api/v1/statistics - Statistik sistem
```

## Struktur Direktori

```
app/
├── Http/Controllers/     # Controllers
├── Models/              # Eloquent models
├── Filament/           # Filament admin resources
└── Providers/          # Service providers

database/
├── migrations/         # Database migrations
└── seeders/           # Database seeders

resources/
├── views/             # Blade templates
├── css/               # Stylesheets
└── js/                # JavaScript

docs/                  # Dokumentasi project
├── PRD-ILDIS-JDIH.md
├── TTD-API-integration.md
├── TTD-Application-Structure.md
├── TTD-Database-Design.md
└── TTD-Metadata-compliance.md
```

## API Documentation

### Authentication
API menggunakan Laravel Sanctum untuk autentikasi. Untuk endpoint yang memerlukan autentikasi, sertakan token di header:
```
Authorization: Bearer your-api-token
```

### Response Format
```json
{
  "success": true,
  "data": {...},
  "message": "Success message",
  "pagination": {
    "current_page": 1,
    "total_pages": 10,
    "total_items": 100
  }
}
```

### Error Handling
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {...}
  }
}
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=DocumentTest

# Run with coverage
php artisan test --coverage
```

## Deployment

### Production Environment
1. Set environment ke production:
```env
APP_ENV=production
APP_DEBUG=false
```

2. Optimize aplikasi:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

3. Setup queue worker untuk background jobs:
```bash
php artisan queue:work --daemon
```

4. Setup cron job untuk scheduled tasks:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Server Requirements
- Web server (Nginx/Apache)
- SSL certificate untuk HTTPS
- Firewall configuration
- Regular backup system
- Monitoring dan logging

## Keamanan

- Semua input divalidasi dan disanitasi
- CSRF protection untuk form
- SQL injection protection dengan Eloquent ORM
- XSS protection dengan Blade templating
- File upload restrictions dan validation
- Role-based access control
- Activity logging untuk audit trail

## Kontribusi

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## Lisensi

Distributed under the MIT License. See `LICENSE` file for more information.

## Kontak dan Dukungan

- Email: admin@jdih.go.id
- Documentation: `/docs` folder
- Issues: GitHub Issues

## Changelog

### Version 1.0.0
- Initial release
- Complete JDIH functionality
- JDIHN integration
- Admin panel with Filament
- RESTful API
- Full-text search capability
