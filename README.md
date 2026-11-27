# M-Tix Clone

Aplikasi web pemesanan tiket bioskop yang terinspirasi dari M-Tix (Cinema XXI). Dibangun menggunakan **Laravel** sebagai tugas proyek kelompok **KELOMPOK-YTTA**.

## Anggota Kelompok

| No | Nama Lengkap | NIM |
|----|--------------|-----|
| 1  | Rezki Firmansyah (Ketua) |  |
| 2  | Riko Riko |  |
| 3  | Pierre Tristan Lama Rio |  |
| 4  | Indra Prianto |  |
| 5  | Elki Doggala |  |
| 6  | Aldan Putra Setiawan | H1101251046 |

## Fitur

- Melihat daftar film yang sedang tayang
- Melihat detail film
- Memilih jadwal tayang
- Memilih kursi
- Pemesanan tiket


## Teknologi

- PHP & Laravel
- Vite (aset frontend)
- Composer & NPM
- Database (sesuaikan: MySQL / SQLite)

## Persyaratan

- PHP >= 8.2
- Composer
- Node.js & NPM
- Database server (MySQL/MariaDB) atau SQLite

## Cara Instalasi

1. Clone repository

   ```bash
   git clone https://github.com/KELOMPOK-YTTA/mtix-clone.git
   cd mtix-clone
   ```

2. Install dependensi

   ```bash
   composer install
   npm install
   ```

3. Salin file environment dan generate key

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Atur koneksi database di file `.env`

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Jalankan migrasi (dan seeder jika ada)

   ```bash
   php artisan migrate --seed
   ```

6. Jalankan aplikasi

   ```bash
   npm run dev
   php artisan serve
   ```

   Buka di browser: <http://127.0.0.1:8000>

## Struktur Folder

```
app/         # Logika aplikasi (Model, Controller)
config/      # Konfigurasi
database/    # Migration & seeder
public/      # Aset publik
resources/   # View & aset frontend
routes/      # Definisi route
tests/       # Pengujian
```

## Menjalankan Test

```bash
php artisan test
```

## Lisensi

Proyek ini dibuat untuk keperluan pembelajaran. Dibangun di atas [Laravel](https://laravel.com) yang berlisensi [MIT](https://opensource.org/licenses/MIT).
