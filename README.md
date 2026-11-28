8# M-Tix Clone

Clone website Cinema XXI (m.21cineplex.com) berbasis **REST API** menggunakan **Laravel v13** dan **MySQL**, dengan penerapan konsep PBO (Abstraksi, Inheritance, Encapsulation, Polymorphism) dan prinsip SOLID. Dibuat sebagai Tugas Akhir PPBO oleh **KELOMPOK-YTTA**.

## Anggota Kelompok

| No | Nama Lengkap | NIM |
|----|--------------|-----|
| 1  | Rezki Firmansyah (Ketua) | H1101251032  |
| 2  | Riko | H1101251065 |
| 3  | Pierre Tristan Lama Rio |H1101251076 |
| 4  | Indra Prianto |H1101251077|
| 5  | Elki Doggala | H1101251064 |
| 6  | Aldan Putra Setiawan | H1101251046 |

## Fitur Utama

- **Autentikasi**: register, login (nomor HP + PIN), logout, profil user
- **Manajemen Film**: Now Playing, Coming Soon, detail film, pencarian, upload poster
- **Manajemen Bioskop dan Studio**: CRUD bioskop, studio, dan layout kursi (termasuk lorong)
- **Jadwal Tayang**: filter berdasarkan film, bioskop, tanggal, dan jam
- **Booking dan Pemilihan Kursi**: seat map interaktif, konfirmasi pesanan, proteksi kursi bentrok
- **Pembayaran dan Tiket**: simulasi pembayaran, kode booking unik, tiket digital
- **Admin Panel**: dashboard statistik dan pengelolaan seluruh data

## Alur Pemesanan Tiket

1. Beranda: lihat film Now Playing dan Coming Soon
2. Detail film: poster, genre, durasi, format, sinopsis
3. Pilih jadwal: bioskop, tanggal, jam tayang, dan studio
4. Pilih kursi: tersedia / dibooking / terisi
5. Konfirmasi pesanan: film, studio, kursi, total harga
6. Pembayaran (simulasi)
7. Tiket dan kode booking, tersimpan di "Pesanan Saya"

## Teknologi

- PHP dan Laravel v13
- MySQL
- Laravel Sanctum (autentikasi token)
- REST API (response JSON)
- Pola arsitektur: Controller, Service, Repository, Interface
- Git dan GitHub

## Skema Database

| Tabel | Keterangan |
|-------|------------|
| `users` | Data pengguna (phone, pin, role) |
| `movies` | Data film |
| `cinemas` | Data bioskop |
| `studios` | Studio per bioskop |
| `seats` | Kursi per studio |
| `schedules` | Jadwal tayang |
| `schedule_seats` | Status kursi per jadwal (available / booked / taken) |
| `bookings` | Pesanan tiket |
| `booking_seats` | Kursi yang dipesan per booking |
| `payments` | Data pembayaran |

## Struktur Folder

```
app/
  Contracts/       # Interface (MovieInterface, BookableInterface)
  Models/          # Model Eloquent
    Abstract/      # Abstract class (BaseUser, BaseContent, BaseVenue, BaseTransaction)
  Repositories/    # Query database
  Services/        # Business logic
  Http/
    Controllers/Api/   # Controller REST API
    Requests/          # Validasi input
    Resources/         # Format response JSON
    Middleware/        # AdminMiddleware
  Traits/          # HasBookingCode
database/          # Migration dan seeder
routes/api.php     # Definisi route API
```

## Endpoint API

### Publik

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/api/auth/register` | Register |
| POST | `/api/auth/login` | Login |
| GET | `/api/movies` | Daftar film |
| GET | `/api/movies/{slug}` | Detail film |
| GET | `/api/cinemas` | Daftar bioskop |
| GET | `/api/schedules` | Daftar jadwal (filter `movie_id`, `cinema_id`, `date`) |
| GET | `/api/schedules/{id}/seats` | Layout dan status kursi per jadwal |

### Butuh Login (`auth:sanctum`)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/api/auth/logout` | Logout |
| GET | `/api/user` | Profil user |
| POST | `/api/bookings` | Buat booking |
| GET | `/api/bookings/my` | Pesanan milik user |
| GET | `/api/bookings/{code}` | Detail booking |
| DELETE | `/api/bookings/{id}` | Batalkan booking |
| POST | `/api/payments` | Bayar booking |

### Admin (`auth:sanctum` + `admin`)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/api/admin/dashboard` | Statistik |
| CRUD | `/api/admin/movies` | Kelola film |
| CRUD | `/api/admin/cinemas` | Kelola bioskop |
| CRUD | `/api/admin/schedules` | Kelola jadwal |
| CRUD | `/api/admin/bookings` | Kelola booking |

### Format Response

```json
{
  "status": "success",
  "message": "Data berhasil diambil",
  "data": {}
}
```

```json
{
  "status": "error",
  "message": "Kursi sudah tidak tersedia",
  "errors": { "seat_id": ["Kursi J10 sudah dipesan"] }
}
```

## Persyaratan

- PHP >= 8.3
- Composer
- MySQL / MariaDB
- Node.js dan NPM

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

4. Atur koneksi database di `.env`

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Migrasi dan seeder

   ```bash
   php artisan migrate --seed
   ```

6. Link storage (untuk poster film)

   ```bash
   php artisan storage:link
   ```

7. Jalankan aplikasi

   ```bash
   php artisan serve
   ```

   API berjalan di <http://127.0.0.1:8000/api>

## Alur Git

| Branch | Keterangan |
|--------|------------|
| `main` | Production, merge hanya lewat Pull Request |
| `develop` | Integrasi semua fitur |
| `feature/auth` | Auth dan User |
| `feature/movies` | Manajemen Film |
| `feature/cinema` | Bioskop, Studio, dan Kursi |
| `feature/schedule` | Jadwal Tayang |
| `feature/booking` | Booking dan Pemilihan Kursi |
| `feature/payment-admin` | Pembayaran dan Admin Panel |

```bash
git checkout develop && git pull origin develop
git checkout -b feature/nama-modul
git add .
git commit -m "feat: deskripsi singkat"
git push origin feature/nama-modul
# Buat Pull Request ke develop
```

## Penerapan PBO dan SOLID

| Konsep | Penerapan |
|--------|-----------|
| Abstraksi | `BaseUser`, `BaseContent`, `BaseVenue`, `BaseTransaction` |
| Inheritance | `Movie`, `Cinema`, `Booking`, dst. mewarisi abstract class masing-masing |
| Encapsulation | `$fillable`, `$hidden`, akses data lewat Service |
| Polymorphism | `getStatusLabel()` pada model, harga berbeda per tipe studio |
| SRP | Controller, Service, dan Repository dipisah |
| OCP | Tipe kursi/studio baru tanpa mengubah `BookingService` |
| LSP | Semua implementor `MovieInterface` bisa dipakai di `MovieService` |
| ISP | Interface terpisah: `MovieInterface`, `BookableInterface`, `PayableInterface` |
| DIP | Controller dan Service bergantung pada interface |

## Lisensi

Proyek ini dibuat untuk keperluan pembelajaran. Dibangun di atas [Laravel](https://laravel.com) yang berlisensi [MIT](https://opensource.org/licenses/MIT).
