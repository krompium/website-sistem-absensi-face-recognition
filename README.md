# Website Sistem Absensi Face Recognition

Sistem absensi berbasis web dengan teknologi face recognition menggunakan Laravel dan Python. 

## 📋 Deskripsi

Website ini adalah sistem absensi yang memanfaatkan teknologi pengenalan wajah (face recognition) untuk mencatat kehadiran.  Sistem ini dibangun menggunakan framework Laravel untuk backend dan Python untuk pemrosesan face recognition.

## 🛠️ Teknologi yang Digunakan

- **PHP** (Laravel Framework)
- **Blade** (Template Engine)
- **Python** (Face Recognition)
- **MySQL** (Database)
- **CSS** & **JavaScript**

## 📦 Prasyarat

Sebelum memulai, pastikan Anda telah menginstal: 

- PHP >= 8.0
- Composer
- MySQL/MariaDB
- Python >= 3.7
- pip (Python package manager)
- Git
- Web Server (Apache/Nginx) atau bisa menggunakan Laravel built-in server

## 🚀 Cara Instalasi

### Step 1: Clone Repository

```bash
git clone https://github.com/krompium/website-sistem-absensi-face-recognition.git
cd website-sistem-absensi-face-recognition
```

### Step 2: Install Dependencies PHP (Composer)

```bash
composer install
```

### Step 3: Konfigurasi Environment

```bash
# Copy file .env.example menjadi .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Konfigurasi Database

Buka file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_face_recognition
DB_USERNAME=root
DB_PASSWORD=
```

### Step 5: Buat Database

Buat database baru di MySQL dengan nama sesuai yang Anda set di `.env`:

```sql
CREATE DATABASE absensi_face_recognition;
```

### Step 6: Migrasi Database

```bash
php artisan migrate
```

Jika ada seeder, jalankan juga:

```bash
php artisan db:seed
```

### Step 7: Install Dependencies Python

```bash
# Buat virtual environment (opsional tapi direkomendasikan)
python -m venv venv

# Aktivasi virtual environment
# Untuk Windows:
venv\Scripts\activate
# Untuk Linux/Mac:
source venv/bin/activate

# Install dependencies Python
pip install -r requirements.txt
```

> **Catatan:** Jika file `requirements.txt` tidak ada, install package berikut secara manual:
> ```bash
> pip install face-recognition opencv-python numpy
> ```

### Step 8: Konfigurasi Storage Laravel

```bash
# Buat symbolic link untuk storage
php artisan storage:link
```

### Step 9: Set Permission (untuk Linux/Mac)

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Step 10: Jalankan Aplikasi

```bash
php artisan serve
```

Website akan berjalan di `http://localhost:8000`

## 🎯 Cara Penggunaan

### Akses Website

Buka browser dan akses: 
```
http://localhost:8000
```

### Login ke Sistem

Gunakan kredensial default (jika sudah ada seeder):
- **Email:** admin@admin.com
- **Password:** password

### Menggunakan Fitur Face Recognition

1. **Registrasi Wajah:**
   - Masuk ke menu Registrasi/Tambah User
   - Upload foto wajah atau gunakan webcam
   - Sistem akan menyimpan data wajah ke database

2. **Absensi:**
   - Akses halaman absensi
   - Izinkan akses kamera
   - Posisikan wajah di depan kamera
   - Sistem akan mengenali dan mencatat kehadiran

3. **Lihat Laporan:**
   - Akses menu Laporan/History
   - Filter berdasarkan tanggal atau user
   - Export data jika diperlukan

## 📁 Struktur Project

```
website-sistem-absensi-face-recognition/
├── app/                    # Aplikasi Laravel
├── bootstrap/              # Bootstrap Laravel
├── config/                 # Konfigurasi
├── database/              # Migrations & Seeders
├── public/                # File publik (CSS, JS, Images)
├── resources/             # Views (Blade templates)
├── routes/                # Route definitions
├── storage/               # File storage
├── vendor/                # Dependencies PHP
├── . env                   # Environment variables
├── composer.json          # PHP dependencies
├── requirements.txt       # Python dependencies
└── README.md             # Dokumentasi
```

## 🔧 Troubleshooting

### Error:  "Class not found"
```bash
composer dump-autoload
```

### Error: "Permission denied" pada storage
```bash
chmod -R 775 storage bootstrap/cache
```

### Error Python module tidak ditemukan
```bash
pip install -r requirements.txt --upgrade
```

### Error kamera tidak terdeteksi
- Pastikan browser memiliki izin akses kamera
- Gunakan HTTPS atau localhost
- Cek apakah kamera sedang digunakan aplikasi lain

## 🤝 Kontribusi

Kontribusi selalu diterima! Silakan: 

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b fitur-baru`)
3. Commit perubahan (`git commit -m 'Menambahkan fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

## 📝 Lisensi

[Sesuaikan dengan lisensi yang Anda gunakan]

## 👤 Author

**krompium**

- GitHub: [@krompium](https://github.com/krompium)

## 📞 Kontak & Support

Jika ada pertanyaan atau menemukan bug, silakan buat [Issue](https://github.com/krompium/website-sistem-absensi-face-recognition/issues) di repository ini.

---

⭐ Jangan lupa berikan star jika project ini bermanfaat! 
