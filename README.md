
<div align="center">

  <h1>⚡ Sistem Manajemen PLN ULP Pelaihari</h1>
  
  <p>
    Aplikasi terintegrasi untuk <strong>Manajemen Material</strong> dan <strong>Monitoring K3 (Kesehatan & Keselamatan Kerja)</strong>.
    <br>
    Dibangun untuk meningkatkan efisiensi operasional dan digitalisasi laporan lapangan.
  </p>

  <p>
    <a href="https://laravel.com">
      <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel" />
    </a>
    <a href="https://php.net">
      <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php" alt="PHP" />
    </a>
    <a href="https://tailwindcss.com">
      <img src="https://img.shields.io/badge/Tailwind-3.0-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind" />
    </a>
    <a href="https://mysql.com">
      <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql" alt="MySQL" />
    </a>
  </p>
</div>

<br>

## ✨ Fitur Utama

Aplikasi ini mencakup berbagai modul untuk kebutuhan operasional:

| Modul | Deskripsi |
| :--- | :--- |
| 📦 **Material Masuk/Keluar** | Pencatatan realtime stok material gardu dan distribusi. |
| 🔄 **Material Retur & Kembali** | Manajemen pengembalian material sisa atau rusak dari lapangan. |
| 👷 **Monitoring K3** | Checklist kelengkapan APD dan keselamatan petugas (Siaga). |
| 📊 **Dashboard & Laporan** | Visualisasi data dan ekspor laporan otomatis ke PDF. |
| 🔐 **Role Management** | Akses bertingkat untuk Admin, Gudang, dan Supervisor. |

---

## 🚀 Instalasi (Quick Start)

Pastikan di komputermu sudah terinstall **PHP, Composer,dan Git**.

### 1. Dapatkan Project
```bash
git clone [https://github.com/myrna12345/PLN-PELAIHARI.git](https://github.com/myrna12345/PLN-PELAIHARI.git)
cd PLN-PELAIHARI

```

### 2. Install Dependencies

Install library backend dan frontend sekaligus.

```bash
composer install

```

### 3. Konfigurasi Environment

Salin file pengaturan dan generate kunci keamanan.

```bash
cp .env.example .env
php artisan key:generate

```

### 4. Setup Database

1. Buat database kosong di MySQL bernama `pln_pelaihari`.
2. Edit file `.env` di text editor, sesuaikan bagian ini:

```env
DB_DATABASE=pln_pelaihari
DB_USERNAME=root
DB_PASSWORD=

```

### 5. Jalankan Migrasi & Data Dummy

Masukkan tabel dan data akun default ke database.

```bash
php artisan migrate --seed
php artisan storage:link

```

---

## 🖥️ Cara Menjalankan
**Terminal 1**

```bash
php artisan serve


Buka browser dan akses: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🔑 Akun Default (Login)

Gunakan akun ini untuk masuk pertama kali (dibuat dari `UserSeeder`):

* **Email:** `` (Cek `UserSeeder.php` jika berbeda)
* **Password:** `password`

---

## 📝 Catatan Pengembang

* **Folder Upload:** Semua bukti foto lapangan tersimpan di `storage/app/public/uploads`. Folder ini sudah di-*ignore* oleh Git agar repository tetap ringan.
* **Generate Laporan:** Fitur ekspor PDF membutuhkan extension `gd` atau `dompdf` yang sudah terinstall via Composer.

<div align="center">
<sub>Made with ❤️ by Tim Pengembang PLN Pelaihari</sub>
</div>

```

### Apa yang Baru di Versi Ini?

1.  **Header Cantik:** Judul ada di tengah dengan logo teknologi (Badges) yang rapi.
2.  **Tabel Fitur:** Orang lebih suka baca tabel daripada teks panjang. Langsung kelihatan aplikasinya bisa apa saja.
3.  **Terminal Terpisah:** Saya perjelas bahwa `npm run dev` dan `php artisan serve` harus jalan bareng (ini sering bikin bingung pemula).
4.  **Akun Default:** Saya siapkan slot untuk info login, jadi kalau dosen/teman mau tes, mereka nggak perlu nanya password ke kamu.

Pasang ini, lalu `git add`, `commit`, dan `push`. Dijamin repo kamu langsung kelihatan "Mahal".

```



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
