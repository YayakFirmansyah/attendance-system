# Manual Book Sistem Absensi Wajah

Dokumen ini adalah panduan operasional lengkap untuk alur proses dan penginputan data pada sistem absensi berbasis face recognition.

## 1. Tujuan Sistem

Sistem ini digunakan untuk:

- Mengelola data akademik dasar (cohort, mahasiswa, ruangan, mata kuliah, jadwal kelas).
- Memastikan data mahasiswa sinkron dengan hasil training model wajah (Flask API `model-info`).
- Menjalankan absensi per sesi kelas dengan validasi enrollment.
- Menghasilkan data kehadiran yang konsisten untuk laporan.

## 2. Peran Pengguna

- Admin:
    - Kelola master data.
    - Kelola enrollment mahasiswa ke kelas.
    - Monitoring data dan laporan.
- Dosen:
    - Buka/tutup sesi absensi.
    - Jalankan scanner absensi.
    - Kelola data absensi kelas yang diajar.

## 3. Prasyarat Sebelum Operasional

### 3.1 Aplikasi Laravel

1. Pastikan konfigurasi `.env` database benar.
2. Jalankan migrasi dan seeder awal:

```bash
php artisan migrate:fresh --seed
```

3. Jalankan aplikasi:

```bash
php artisan serve
```

### 3.2 Flask API Face Recognition

1. Flask API harus aktif.
2. Endpoint utama harus bisa diakses:

- `GET /api/health`
- `GET /api/model-info`
- `POST /api/verify-face`

3. Pastikan `python_api_url` di config Laravel mengarah ke URL Flask yang benar.

### 3.3 Pipeline Training Wajah (External)

1. Mahasiswa merekam video wajah.
2. Dataset disimpan di Google Drive.
3. Retraining model di Google Colab.
4. Hasil training memperbarui daftar nama di `api/model-info`.
5. Hanya nama yang ada di `model-info` boleh dibuat sebagai data mahasiswa.

## 4. Urutan Input Data (Wajib Berurutan)

Ikuti urutan ini agar relasi tidak gagal:

1. User (Admin dan Dosen)
2. Cohort
3. Mahasiswa (Student)
4. Ruangan (Room)
5. Mata Kuliah (Course)
6. Jadwal Kelas (Class)
7. Enrollment Mahasiswa ke Kelas (Class Enrollment)
8. Sesi Absensi (Attendance Session)
9. Kehadiran (Attendance)

## 5. Panduan Penginputan Master Data

### 5.1 Kelola Cohort

Menu: `Cohorts`

Input minimal:

- `name`
- `angkatan`
- `fakultas`
- `program_studi`
- `kelas`
- `semester`

Catatan:

- Cohort adalah sumber data akademik mahasiswa (program/fakultas/semester).

### 5.2 Kelola Mahasiswa

Menu: `Students -> Add Student`

Input:

- `student_id`
- `name` (harus dari daftar `model-info`)
- `email`
- `phone` (opsional)
- `status`
- `cohort_id`
- `profile_photo` (opsional)

Aturan penting:

- Nama mahasiswa tidak boleh input bebas di luar daftar model wajah.
- Mahasiswa harus selalu terhubung ke cohort.

### 5.3 Kelola Ruangan

Menu: `Rooms`

Input minimal:

- `room_code`
- `room_name`
- `building`
- `capacity`
- `type`
- `status`

### 5.4 Kelola Mata Kuliah

Menu: `Courses`

Input minimal:

- `course_code`
- `course_name`
- `credits`
- `faculty`
- `lecturer_id`
- `status`

### 5.5 Kelola Jadwal Kelas

Menu: `Classes`

Input minimal:

- `course_id`
- `cohort_id`
- `room_id`
- `day`
- `start_time`
- `end_time`
- `status`

Validasi sistem:

- Kombinasi `course_id + cohort_id` tidak boleh duplikat.
- Jadwal ruangan tidak boleh bentrok pada hari dan jam yang sama.

### 5.6 Kelola Enrollment Mahasiswa

Menu: `Classes -> Enrollments`

Proses:

1. Pilih kelas.
2. Tambahkan mahasiswa aktif ke kelas.
3. Status enrollment aktif akan dipakai sebagai acuan absensi.

Catatan:

- Enrollment adalah sumber kebenaran utama peserta kelas.

## 6. Alur Operasional Absensi Harian

### 6.1 Buka Sesi Absensi

Dilakukan oleh dosen.

Langkah:

1. Masuk ke menu scanner absensi.
2. Pilih kelas.
3. Klik buka sesi (`open session`).

### 6.2 Jalankan Scanner Wajah

Langkah:

1. Kamera mengirim frame ke Flask API.
2. Flask mengembalikan `student_name` dan `confidence`.
3. Laravel mencocokkan nama ke data mahasiswa aktif.
4. Laravel memvalidasi enrollment mahasiswa pada kelas terkait.
5. Jika valid, attendance dicatat sebagai `present`.

Status yang mungkin:

- `new_attendance`
- `already_attended`
- `not_enrolled`
- `not_found`

### 6.3 Tutup Sesi Absensi

Langkah:

1. Dosen klik tutup sesi (`close session`).
2. Sistem melakukan `auto mark absent` untuk mahasiswa enrolled aktif yang belum tercatat hadir.
3. Sesi berubah dari `active` menjadi `closed`.

## 7. Alur Koreksi Data

### 7.1 Koreksi Absensi Manual

Menu: `Attendance Manage`

Bisa dilakukan:

- Tambah absensi manual.
- Edit status absensi.
- Bulk update status absensi.

### 7.2 Drop Mahasiswa dari Kelas

Menu: `Class Enrollments`

Langkah:

1. Pilih kelas.
2. Hapus/drop enrollment mahasiswa.
3. Status enrollment berubah ke `dropped`.

## 8. Laporan

Menu: `Reports` dan `Attendance History`

Laporan utama:

- Rekap kehadiran per kelas.
- Ringkasan kehadiran per mahasiswa.
- Riwayat absensi per tanggal/status.

## 9. SOP Harian Singkat

### Admin

1. Cek API Flask aktif.
2. Cek data master terbaru (cohort, room, course, class).
3. Cek enrollment kelas.
4. Pantau laporan dan data invalid.

### Dosen

1. Buka sesi sebelum kelas dimulai.
2. Jalankan scanner selama proses absensi.
3. Tutup sesi setelah absensi selesai.
4. Koreksi manual jika diperlukan.

## 10. Troubleshooting

### Kasus 1: Nama mahasiswa tidak bisa disimpan

Penyebab:

- Nama tidak ada di `api/model-info`.

Solusi:

1. Pastikan retraining sudah update model.
2. Refresh cache model-info dari panel admin.
3. Gunakan nama persis sesuai model-info.

### Kasus 2: Mahasiswa dikenali tapi status `not_enrolled`

Penyebab:

- Mahasiswa belum masuk enrollment kelas.

Solusi:

1. Buka `Classes -> Enrollments`.
2. Tambahkan mahasiswa ke kelas.
3. Pastikan status enrollment `active`.

### Kasus 3: Absensi tidak tercatat

Penyebab umum:

- Sesi belum dibuka.
- API Flask tidak aktif.
- Confidence terlalu rendah.

Solusi:

1. Pastikan sesi status `active`.
2. Cek endpoint `/api/health`.
3. Perbaiki kualitas kamera/pencahayaan.

### Kasus 4: Jadwal kelas gagal disimpan (conflict)

Penyebab:

- Ruangan bentrok di hari/jam yang sama.

Solusi:

1. Ubah ruangan atau waktu kelas.
2. Simpan ulang jadwal.

## 11. Checklist Go-Live

Sebelum dipakai operasional penuh, pastikan:

- [ ] `migrate:fresh --seed` sukses.
- [ ] Admin dan dosen bisa login.
- [ ] API Flask sehat (`/api/health`).
- [ ] `model-info` menampilkan daftar nama terbaru.
- [ ] Semua kelas punya enrollment aktif.
- [ ] Simulasi open session -> scan -> close session berhasil.
- [ ] Laporan attendance dapat ditampilkan.

## 12. Penutup

Jika alur input data mengikuti urutan di dokumen ini, sistem absensi akan konsisten:
Cohort -> Student -> Room -> Course -> Class -> Enrollment -> Session -> Attendance -> Report.

Gunakan manual ini sebagai SOP utama tim admin dan dosen.
