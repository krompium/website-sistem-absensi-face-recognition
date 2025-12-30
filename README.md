# Website Sistem Absensi Face Recognition

Sistem absensi berbasis web dengan teknologi face recognition menggunakan Laravel dan Python.  Aplikasi ini memungkinkan pencatatan kehadiran siswa menggunakan pengenalan wajah dengan teknologi DeepFace.

## 📋 Deskripsi

Website ini adalah sistem absensi yang memanfaatkan teknologi pengenalan wajah (face recognition) untuk mencatat kehadiran siswa.  Sistem ini terdiri dari: 
- **Backend Laravel** - Mengelola data siswa, kelas, guru, dan absensi
- **Python Flask API** - Melakukan pemrosesan face recognition menggunakan DeepFace
- **Frontend** - Interface web dengan Tailwind CSS

## 🛠️ Teknologi yang Digunakan

- **PHP** (Laravel 11.x)
- **Blade** (Template Engine)
- **Python 3.x** (Flask + DeepFace untuk Face Recognition)
- **MySQL** (Database)
- **Tailwind CSS** & **Vite** (Frontend)
- **JavaScript**

## 📦 Prasyarat

Pastikan Anda telah menginstal: 

- **PHP** >= 8.2
- **Composer**
- **MySQL/MariaDB** >= 8.0
- **Python** >= 3.8
- **pip** (Python package manager)
- **Node.js** >= 18.x & **npm**
- **Git**

## 🚀 Cara Instalasi Step-by-Step

### Step 1: Clone Repository

```bash
git clone https://github.com/krompium/website-sistem-absensi-face-recognition.git
cd website-sistem-absensi-face-recognition
```

### Step 2: Install Dependencies PHP (Composer)

```bash
composer install
```

### Step 3: Install Dependencies Node.js (npm)

```bash
npm install
```

### Step 4: Konfigurasi Environment

```bash
# Copy file .env.example menjadi .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Konfigurasi Database

**Pilihan A: Import Database yang Sudah Ada**

Ada file database `absensi_face_db` di root project. Anda bisa import langsung:

```bash
# Buat database baru
mysql -u root -p -e "CREATE DATABASE absensi_face_recognition;"

# Import database
mysql -u root -p absensi_face_recognition < absensi_face_db
```

**Pilihan B: Menggunakan Migration**

Atau buat database baru dan jalankan migration:

```bash
# Buat database
mysql -u root -p -e "CREATE DATABASE absensi_face_recognition;"

# Jalankan migration
php artisan migrate

# (Opsional) Jalankan seeder untuk data dummy
php artisan db:seed
```

Kemudian edit file `.env` sesuai konfigurasi database Anda: 

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_face_recognition
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 6: Konfigurasi Storage Laravel

```bash
# Buat symbolic link untuk storage
php artisan storage:link

# Set permission (Linux/Mac)
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Step 7: Install Dependencies Python untuk Face Recognition

Masuk ke folder Python dan install dependencies:

```bash
cd resources/python

# Buat virtual environment (sangat direkomendasikan)
python -m venv venv

# Aktivasi virtual environment
# Untuk Windows:
venv\Scripts\activate
# Untuk Linux/Mac:
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Kembali ke root project
cd ../.. 
```

> **Catatan:** Instalasi DeepFace dan OpenCV mungkin memakan waktu beberapa menit

### Step 8: Build Frontend Assets

```bash
# Build untuk production
npm run build

# ATAU jalankan development server (dengan hot reload)
npm run dev
```

### Step 9: Jalankan Aplikasi

Anda perlu menjalankan **3 service** secara bersamaan:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Python Flask API (Face Recognition):**
```bash
cd resources/python
# Aktivasi virtual environment jika belum
source venv/bin/activate  # Linux/Mac
# ATAU
venv\Scripts\activate  # Windows

# Jalankan Flask
python app.py
```

**Terminal 3 - Vite Dev Server (opsional, jika development):**
```bash
npm run dev
```

> **Tip:** Anda bisa menggunakan `concurrently` yang sudah ada di package.json untuk menjalankan beberapa command sekaligus

### Step 10: Akses Aplikasi

Buka browser dan akses: 
```
http://localhost:8000
```

Python Flask API akan berjalan di: 
```
http://localhost:5000
```

## 🎯 Cara Penggunaan

### 1. Login ke Sistem

Gunakan kredensial default (jika menggunakan seeder atau database import):
- Cek tabel `users` di database Anda untuk kredensial login

### 2. Kelola Data Master

- **Kelas:** Tambah/edit data kelas (contoh: "XIIRPL", "XTKJ1")
- **Siswa:** Tambah data siswa dengan kode siswa, nama, kelas, jenis kelamin, dll
- **Guru:** Kelola data guru dan assign ke kelas

### 3. Registrasi Wajah Siswa

1.  Masuk ke menu **Training/Registrasi Wajah**
2. Pilih siswa
3. Upload foto wajah atau gunakan webcam
4. Sistem akan menyimpan data wajah menggunakan API Flask
5. Foto akan disimpan di folder `resources/python/known_faces/`

### 4. Absensi dengan Face Recognition

1. Akses halaman **Absensi**
2. Izinkan akses kamera browser
3. Siswa posisikan wajah di depan kamera
4. Sistem akan: 
   - Mendeteksi wajah
   - Mengirim ke Python API untuk pengenalan
   - Mencatat kehadiran (jam masuk/keluar)
   - Mendeteksi indikasi mabuk (opsional)

### 5. Lihat Laporan & Rekap

- Akses menu **Laporan/Rekap Absensi**
- Filter berdasarkan tanggal, kelas, atau siswa
- Export data ke Excel/PDF (jika tersedia)

## 📁 Struktur Project Penting

```
website-sistem-absensi-face-recognition/
├── app/                          # Laravel application
│   ├── Models/                   # Model (User, Siswa, Kelas, Absensi, dll)
│   └── Http/Controllers/         # Controllers
├── database/
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── resources/
│   ├── python/                   # 🔥 Python Flask API
│   │   ├── app.py               # Main Flask application
│   │   ├── requirements.txt     # Python dependencies
│   │   ├── known_faces/         # Folder penyimpanan foto training
│   │   └── face_database.json   # Database face encoding
│   └── views/                    # Blade templates
├── public/                       # Public assets
├── routes/
│   └── web.php                  # Web routes
├── . env.example                 # Environment template
├── absensi_face_db              # 🔥 Database SQL dump
├── composer.json                # PHP dependencies
├── package.json                 # Node.js dependencies
└── vite.config.js              # Vite configuration
```

## 🔧 Troubleshooting

### Error:  "Class not found"
```bash
composer dump-autoload
php artisan clear
php artisan config:clear
```

### Error: "Permission denied" pada storage
```bash
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data: www-data storage bootstrap/cache  # Linux
```

### Error: Python module tidak ditemukan
```bash
cd resources/python
pip install -r requirements.txt --upgrade
```

### Error: Face recognition tidak bekerja

1. Pastikan Python Flask API berjalan di `http://localhost:5000`
2. Cek console browser untuk error CORS
3. Pastikan folder `resources/python/known_faces/` ada dan writable
4. Cek apakah ada data di `face_database.json`

### Error:  Kamera tidak terdeteksi

- Pastikan browser memiliki izin akses kamera
- Gunakan HTTPS atau localhost (browser security requirement)
- Cek apakah kamera sedang digunakan aplikasi lain
- Coba browser lain (Chrome/Firefox recommended)

### Error: npm run build gagal

```bash
# Clear cache
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
npm run build
```

### Error: DeepFace instalasi gagal (Windows)

Jika ada error saat install DeepFace di Windows:
```bash
# Install Visual C++ Build Tools dulu
# Download dari: https://visualstudio.microsoft.com/visual-cpp-build-tools/

# Atau gunakan pre-built wheels
pip install --upgrade pip
pip install deepface --no-cache-dir
```

## 📊 Database Schema

### Tabel Utama: 
- **users** - Data guru dan administrator
- **siswa** - Data siswa
- **kelas** - Data kelas
- **absensi** - Rekam absensi harian
- **_indikasi_siswa** - Hasil deteksi indikasi dari AI
- **_guru_kelas** - Relasi guru dengan kelas yang diajar

## 🔌 API Endpoints (Python Flask)

- `POST /api/train` - Upload dan training foto wajah
- `POST /api/recognize` - Recognize wajah dari foto/video
- `DELETE /api/delete/<name>` - Hapus data wajah
- `GET /api/list` - List semua wajah yang terdaftar

## 🤝 Kontribusi

Kontribusi selalu diterima! Silakan: 

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b fitur-baru`)
3. Commit perubahan (`git commit -m 'Menambahkan fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

## 📝 Lisensi

Project ini bersifat open source untuk keperluan edukasi. 

## 👤 Author

**krompium**
- GitHub: [@krompium](https://github.com/krompium)
- Repository: [website-sistem-absensi-face-recognition](https://github.com/krompium/website-sistem-absensi-face-recognition)

## 🆘 Support

Jika ada pertanyaan atau menemukan bug: 
- Buat [Issue](https://github.com/krompium/website-sistem-absensi-face-recognition/issues)
- Atau hubungi melalui GitHub

---

⭐ **Jangan lupa berikan star jika project ini bermanfaat!**

## 📸 Screenshot

_(Anda bisa tambahkan screenshot aplikasi di sini)_

## 🔄 Update Log

- **v1.0** - Initial release dengan fitur face recognition dasar
- Sistem absensi siswa
- Deteksi indikasi mabuk
- Multi-kelas dan guru

---
