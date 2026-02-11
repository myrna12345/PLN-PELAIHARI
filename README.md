<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Berikut adalah template **README.md** yang sudah saya rapikan agar terlihat profesional, modern, dan mudah dibaca di GitHub.

Saya sudah menambahkan **Badge**, **Formatting Code Block**, dan struktur yang rapi. Kamu tinggal copy dan paste kode di bawah ini ke dalam file bernama `README.md` di folder project kamu.

### Copy Kode di Bawah Ini:

```markdown
# ⚡ PLN Pelaihari System

Sistem Manajemen Material dan Keselamatan Kerja (K3) PLN ULP Pelaihari. Aplikasi ini dibangun menggunakan Laravel untuk mempermudah pencatatan keluar-masuk material dan monitoring petugas.

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

---

## 📋 Syarat Minimum (Requirements)

Pastikan perangkat Anda sudah terinstall:
* **PHP** ^8.1 atau lebih baru
* **Composer** (Manajer paket PHP)
* **MySQL** atau **MariaDB**
* **Node.js & NPM** (Untuk kompilasi aset frontend)
* **Git**

---

## 🚀 Panduan Instalasi (Installation Guide)

Ikuti langkah-langkah berikut untuk menjalankan project ini di komputer lokal Anda:

### 1️⃣ Clone Repository
Unduh source code project ini ke komputer lokal Anda.

```bash
git clone [https://github.com/username/PLN-PELAIHARI.git](https://github.com/username/PLN-PELAIHARI.git)
cd PLN-PELAIHARI

```

### 2️⃣ Install Dependency Backend

Install semua library PHP yang dibutuhkan oleh Laravel menggunakan Composer.

```bash
composer install

```

### 3️⃣ Setup Environment

Duplikat file konfigurasi `.env.example` menjadi `.env`.

**Untuk Windows:**

```bash
copy .env.example .env

```

**Untuk Mac / Linux:**

```bash
cp .env.example .env

```

### 4️⃣ Generate Application Key

Buat kunci enkripsi aplikasi Laravel.

```bash
php artisan key:generate

```

### 5️⃣ Konfigurasi Database

1. Buat database baru di MySQL (misal: `pln_pelaihari`).
2. Buka file `.env` dan sesuaikan konfigurasi database Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pln_pelaihari
DB_USERNAME=root
DB_PASSWORD=

```

### 6️⃣ Migrasi & Seeder

Jalankan perintah berikut untuk membuat tabel dan mengisi data awal (akun admin, data dummy, dll).

```bash
# Migrasi tabel saja
php artisan migrate

# Migrasi beserta data dummy (Disarankan)
php artisan migrate --seed

```

### 7️⃣ Install Dependency Frontend

Install dan compile aset frontend (CSS/JS) menggunakan Vite.

```bash
npm install
npm run dev

```

### 8️⃣ Setup Storage Link

Agar foto upload (bukti material/petugas) bisa diakses publik.

```bash
php artisan storage:link

```

### 9️⃣ Jalankan Server

Jalankan server lokal Laravel.

```bash
php artisan serve

```

Aplikasi sekarang dapat diakses melalui browser di:
🔗 **http://127.0.0.1:8000**

---

## 🛠️ Catatan Tambahan

* **Login Default:** Jika menggunakan seeder, cek file `UserSeeder.php` untuk melihat email/password default.
* **Masalah Permission:** Jika folder `storage` atau `bootstrap/cache` tidak bisa ditulis, jalankan: `chmod -R 775 storage bootstrap/cache` (Linux/Mac).

---

Made with ❤️ by Tim Pengembang

```

### Cara Memasangnya:
1.  Buka folder project kamu di VS Code.
2.  Cari file bernama `README.md` (kalau belum ada, buat baru).
3.  **Hapus semua isinya**, lalu **Paste** kode di atas.
4.  Simpan, lalu lakukan push ke GitHub:
    ```bash
    git add README.md
    git commit -m "Docs: Update README with professional installation guide"
    git push origin main
    ```

Setelah ini, halaman depan GitHub kamu akan terlihat sangat **profesional** seperti project open-source kelas dunia! 😎

```
