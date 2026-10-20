# PRD: Sistem Presensi & Pengajuan Izin/Sakit Siswa Berbasis Web

Capstone Project PAS Pemrograman Web | XI PPLG | Opsi C: Attendance & Workflow | Stack: Laravel + Vue.js + MySQL | Individual

## 1. Ringkasan Produk

**Nama produk:** SiPresensi (nama bisa diganti)

**Deskripsi singkat:** Aplikasi web untuk mencatat presensi harian dan per jam pelajaran siswa, mengelola pengajuan izin/sakit secara digital lengkap dengan upload bukti, proses persetujuan oleh Wali Kelas, dan rekap kehadiran otomatis per siswa, kelas, dan periode, serta grafik kehadiran per siswa.

**Catatan stack:** Jobsheet menyebut stack PHP Native, sedangkan project ini memakai Laravel (backend) dan Vue.js (frontend). **Penggunaan stack ini sudah diizinkan oleh guru pengampu.** Semua requirement jobsheet tetap dipenuhi lewat padanan di Laravel/Vue:

| Requirement Jobsheet | Padanan di Laravel + Vue |
| --- | --- |
| Struktur folder modular (config, assets, views, actions) | Struktur standar Laravel (config/, app/Http/Controllers, routes/) + Vue (resources/js/views, components, services) |
| password_hash() / password_verify() | Hash::make() / Hash::check() (bcrypt) |
| session_start() & middleware | Laravel Sanctum mode SPA (session cookie) + middleware auth dan role |
| Validasi input server-side | Form Request Validation |
| Validasi form client-side JS native | Validasi manual di komponen Vue (tanpa library validasi) |
| Query JOIN, GROUP BY, WHERE | Query Builder / Eloquent dengan join dan groupBy |
| File database.sql | Export dump MySQL (migration + seeder tetap disertakan) |
| Print CSS / PDF Generator | barryvdh/laravel-dompdf dan/atau Print CSS di Vue |

## 2. Latar Belakang & Problem Statement

Rekap kehadiran harian memakan waktu jam pelajaran. Surat izin/sakit fisik sering tidak sampai ke wali kelas atau tercecer, sehingga rekap absensi bulanan sekolah tidak valid.

**Masalah yang diselesaikan:**

- Input presensi manual lambat dan rawan salah.
- Surat izin/sakit tidak punya jejak dan status yang jelas.
- Rekap bulanan harus dihitung manual dan sering tidak akurat.
- Siswa tidak bisa memantau persentase kehadirannya sendiri.

## 3. Tujuan & Indikator Keberhasilan

| Tujuan | Indikator |
| --- | --- |
| Presensi satu kelas bisa diinput cepat | Wali kelas input 1 kelas (±36 siswa) kurang dari 2 menit |
| Pengajuan izin sepenuhnya digital | Siswa submit izin + foto bukti, status terlihat real-time |
| Data presensi konsisten dengan izin | Izin yang disetujui otomatis tercatat di presensi, tanpa input ulang |
| Rekap otomatis | Rekap persentase per siswa/kelas/periode tampil dan bisa dicetak |
| Memenuhi jobsheet | Semua item spesifikasi teknis dan rubrik terpenuhi |
| Perkembangan kehadiran siswa mudah dipantau | Grafik per siswa (komposisi status, bulanan, tren persentase) tampil sesuai data rekap |

## 4. Ruang Lingkup

**In scope (wajib):**

- Autentikasi multi-role (Admin/Kurikulum, Wali Kelas, Guru Mapel, Guru Piket, Siswa)
- Master data: User, Tahun Ajaran, Kelas, Siswa, Mata Pelajaran, Jadwal Pelajaran (dengan guru pengajar), Delegasi Guru Piket
- Input presensi harian per kelas dan presensi per jam pelajaran
- Pengajuan izin/sakit + upload foto bukti
- Approval izin oleh Wali Kelas atau Guru Piket (pengganti wali kelas pada kelas yang didelegasikan)
- Rekap presensi dengan persentase + filter periode
- Grafik kehadiran per siswa
- Cetak laporan PDF
- Validasi client-side (Vue) dan server-side (Laravel)
- Dokumentasi pengujian blackbox

**Out of scope (tidak dikerjakan):**

- Presensi berbasis GPS/QR/face recognition
- Notifikasi WhatsApp/email
- Aplikasi mobile native (cukup responsif)
- Presensi guru

**Stretch goal (kalau waktu sisa):** grafik ringkasan kehadiran di dashboard, notifikasi in-app, ekspor rekap ke Excel.

## 5. Aktor & Hak Akses

| Fitur | Admin/Kurikulum | Wali Kelas | Guru Mapel | Guru Piket | Siswa |
|---|:-:|:-:|:-:|:-:|:-:|
| Login/logout | ✔ | ✔ | ✔ | ✔ | ✔ |
| Kelola user | ✔ | ✘ | ✘ | ✘ | ✘ |
| Kelola tahun ajaran, kelas, siswa | ✔ | ✘ | ✘ | ✘ | ✘ |
| Kelola mapel & jadwal pelajaran | ✔ | ✘ | ✘ | ✘ | ✘ |
| Kelola delegasi guru piket | ✔ | ✘ | ✘ | ✘ | ✘ |
| Lihat jadwal pelajaran | ✔ | ✔ (kelasnya) | ✔ (jadwal yang ia ajar) | ✔ (kelas delegasi) | ✔ (kelasnya) |
| Input/koreksi presensi harian | ✘ | ✔ (kelasnya) | ✘ | ✔ (kelas delegasi) | ✘ |
| Input/koreksi presensi per jam | ✘ | ✔ (kelasnya) | ✔ (jam yang ia ajar) | ✔ (kelas delegasi) | ✘ |
| Ajukan izin/sakit | ✘ | ✘ | ✘ | ✘ | ✔ |
| Approve/reject izin | ✘ | ✔ (kelasnya) | ✘ | ✔ (kelas delegasi, pengganti wali kelas) | ✘ |
| Lihat rekap presensi | ✔ (semua) | ✔ (kelasnya) | ✔ (rekap per jam yang ia ajar) | ✔ (kelas delegasi) | ✔ (dirinya) |
| Lihat grafik kehadiran per siswa | ✔ (semua) | ✔ (kelasnya) | ✘ | ✔ (kelas delegasi) | ✔ (dirinya) |
| Cetak laporan PDF | ✔ | ✔ | ✔ (rekap jam yang ia ajar) | ✔ | ✔ (dirinya) |

## 6. User Stories

**Admin/Kurikulum**

- Sebagai admin, saya ingin mengelola akun, kelas, jadwal, dan delegasi guru piket agar data dasar sistem selalu benar.
- Sebagai admin, saya ingin melihat rekap kehadiran seluruh sekolah per periode agar laporan bulanan valid.

**Wali Kelas**

- Sebagai wali kelas, saya ingin menginput presensi satu kelas dalam satu layar agar tidak memakan jam pelajaran.
- Sebagai wali kelas, saya ingin melihat pengajuan izin siswa saya, memeriksa bukti, lalu menyetujui atau menolak.
- Sebagai wali kelas, saya ingin melihat grafik kehadiran tiap siswa agar siswa yang sering alpa cepat terdeteksi.
- Sebagai wali kelas, saya ingin mencetak rekap kelas per bulan.

**Siswa**

- Sebagai siswa, saya ingin mengajukan izin/sakit dengan foto bukti dari HP.
- Sebagai siswa, saya ingin memantau status pengajuan dan persentase kehadiran saya.
- Sebagai siswa, saya ingin melihat grafik kehadiran saya per bulan agar tahu tren kehadiran saya.

**Guru Piket**

- Sebagai guru piket, saya ingin menyetujui izin dan menginput presensi pada kelas yang didelegasikan kepada saya ketika wali kelas berhalangan.
- Sebagai guru piket, saya ingin melihat rekap dan grafik kehadiran siswa dari kelas yang didelegasikan.

**Guru Mapel**

- Sebagai guru mapel, saya ingin menginput presensi per jam pada kelas dan jam yang saya ajar agar kehadiran siswa di mata pelajaran saya tercatat.
- Sebagai guru mapel, saya ingin melihat rekap kehadiran per jam untuk mata pelajaran saya.

## 7. Functional Requirements

### 7.1 Autentikasi & Session (FR-AUTH)

- **FR-AUTH-01:** Login dengan username/email + password; password disimpan dengan bcrypt.
- **FR-AUTH-02:** Setelah login, user diarahkan ke dashboard sesuai role.
- **FR-AUTH-03:** Login gagal menampilkan pesan error yang jelas (tanpa membocorkan mana yang salah).
- **FR-AUTH-04:** Semua halaman dan endpoint internal diproteksi middleware auth + role; akses tanpa login diarahkan ke halaman login (frontend) atau 401 (API).
- **FR-AUTH-05:** Logout menghapus session.
- **FR-AUTH-06:** Akun nonaktif tidak bisa login.

### 7.2 Master Data (FR-MD)

- **FR-MD-01:** CRUD User (nama, username, role, status aktif, reset password).
- **FR-MD-02:** CRUD Tahun Ajaran; hanya satu yang boleh berstatus aktif.
- **FR-MD-03:** CRUD Kelas (nama, tingkat, jurusan, tahun ajaran, wali kelas).
- **FR-MD-04:** CRUD Siswa (NIS, NISN, nama, jenis kelamin, kelas, akun user terkait).
- **FR-MD-05:** CRUD Mata Pelajaran dan Jadwal Pelajaran (kelas, mapel, hari, jam mulai/selesai, guru pengajar yang dipilih dari akun Guru Mapel).
- **FR-MD-06:** Semua tabel master memiliki pagination, pencarian, dan konfirmasi sebelum hapus.
- **FR-MD-07:** Data yang masih dipakai relasi (misal kelas yang punya siswa) tidak boleh dihapus; tampilkan pesan yang jelas.
- **FR-MD-08:** Delegasi Guru Piket: Admin menetapkan guru piket ke satu atau lebih kelas dengan rentang tanggal mulai-selesai; di luar rentang itu Guru Piket tidak punya akses ke kelas tersebut.
- **FR-MD-09:** Setiap jadwal pelajaran wajib terhubung ke satu akun Guru Mapel sebagai guru pengajar.

### 7.3 Presensi Harian & Per Jam Pelajaran (FR-ATT)

- **FR-ATT-01:** Presensi harian: Wali Kelas, atau Guru Piket pada kelas yang didelegasikan, memilih kelas dan tanggal (default hari ini); sistem menampilkan daftar siswa kelas tersebut.
- **FR-ATT-02:** Status per siswa: Hadir, Izin, Sakit, Alpa, dengan tombol Set Semua Hadir.
- **FR-ATT-03:** Siswa yang izinnya sudah disetujui pada tanggal tersebut otomatis berstatus Izin/Sakit (harian dan semua jam pelajaran hari itu) dan terkunci, tidak bisa diubah manual.
- **FR-ATT-04:** Presensi bisa dikoreksi; setiap data mencatat siapa yang menginput.
- **FR-ATT-05:** Satu siswa hanya punya satu record presensi harian per tanggal (unique constraint).
- **FR-ATT-06:** Tidak bisa input presensi untuk tanggal di masa depan.
- **FR-ATT-07:** Presensi per jam pelajaran: Guru Mapel memilih jam pelajaran yang ia ajar pada hari itu (dari jadwal), lalu sistem menampilkan daftar siswa kelas tersebut dengan status Hadir/Izin/Sakit/Alpa per sesi. Wali Kelas dan Guru Piket (kelas delegasi) bisa menginput atau mengoreksi untuk kelasnya.
- **FR-ATT-08:** Satu siswa hanya punya satu record per jam pelajaran per tanggal (unique siswa, jadwal, tanggal). Jam pelajaran hanya bisa diinput sesuai hari jadwalnya.
- **FR-ATT-09:** Siswa berstatus Izin/Sakit pada presensi harian otomatis terisi Izin/Sakit di semua jam hari itu; siswa Hadir harian yang Alpa pada jam tertentu tercatat sebagai alpa jam (membolos).
- **FR-ATT-10:** Rekap presensi harian dan rekap per jam dihitung terpisah.
- **FR-ATT-11:** Guru Mapel hanya bisa melihat dan menginput jam pelajaran yang terhubung dengan akunnya di jadwal, dan tidak bisa mengubah presensi harian.

### 7.4 Pengajuan Izin/Sakit (FR-LV)

- **FR-LV-01:** Siswa mengisi form: jenis (Izin/Sakit), tanggal mulai, tanggal selesai, alasan, upload foto bukti.
- **FR-LV-02:** Validasi upload: format jpg/jpeg/png, maksimal 2 MB; bukti wajib untuk Sakit, opsional untuk Izin (bisa diubah sesuai kesepakatan dengan guru).
- **FR-LV-03:** Tanggal selesai tidak boleh sebelum tanggal mulai; rentang pengajuan tidak boleh bertumpuk dengan pengajuan lain yang masih pending/approved.
- **FR-LV-04:** Status awal Pending; siswa bisa membatalkan atau mengedit selama masih Pending.
- **FR-LV-05:** Siswa melihat riwayat pengajuan beserta statusnya (Pending/Approved/Rejected) dan catatan penolakan.
- **FR-LV-06:** File bukti disimpan di storage privat; hanya pemilik, wali kelas terkait, guru piket, dan admin yang bisa melihatnya.

### 7.5 Approval Izin (FR-APR)

- **FR-APR-01:** Wali Kelas melihat daftar pengajuan dari kelasnya; Guru Piket melihat pengajuan dari kelas yang didelegasikan kepadanya sebagai pengganti wali kelas. Tersedia filter status dan kelas.
- **FR-APR-02:** Detail pengajuan menampilkan data siswa, alasan, dan preview foto bukti (modal).
- **FR-APR-03:** Wali Kelas atau Guru Piket bisa Approve atau Reject; Reject wajib menyertakan catatan; sistem mencatat siapa yang memproses.
- **FR-APR-04:** Saat Approve, sistem otomatis membuat/memperbarui presensi harian untuk setiap hari kerja dalam rentang izin, dan presensi per jam untuk semua jam pelajaran pada hari tersebut, dengan status Izin/Sakit (dalam satu database transaction).
- **FR-APR-05:** Pengajuan yang sudah diproses tidak bisa diubah statusnya lagi (atau hanya admin yang bisa membatalkan).

### 7.6 Rekap & Laporan (FR-RPT)

- **FR-RPT-01:** Rekap per kelas: jumlah Hadir, Izin, Sakit, Alpa, dan persentase kehadiran tiap siswa dalam periode (harian dan per jam).
- **FR-RPT-02:** Filter rentang tanggal, kelas, dan tahun ajaran.
- **FR-RPT-03:** Rekap per siswa (detail harian dan per jam) untuk siswa, wali kelas, guru piket (kelas delegasi), dan guru mapel (rekap per jam yang ia ajar).
- **FR-RPT-04:** Cetak laporan dalam PDF (unduh, dibuat dengan dompdf) dengan kop sekolah, periode, dan tanda tangan wali kelas.
- **FR-RPT-05:** Dashboard ringkas per role (statistik kehadiran hari ini, jumlah izin pending, persentase kehadiran pribadi).
- **FR-RPT-06:** Grafik kehadiran per siswa pada halaman detail siswa: donut komposisi status (Hadir/Izin/Sakit/Alpa), bar jumlah status per bulan, dan line tren persentase kehadiran per bulan. Ada filter periode/tahun ajaran dan tooltip nilai.
- **FR-RPT-07:** Akses grafik: siswa hanya miliknya sendiri, wali kelas untuk siswa kelasnya, guru piket untuk siswa di kelas delegasinya, dan admin untuk semua siswa. Grafik bisa disertakan pada PDF rekap per siswa.

## 8. Aturan Bisnis (Business Rules)

1. Persentase kehadiran = (Hadir + Izin + Sakit) / total hari efektif x 100%. Izin dan Sakit dihitung hadir (sudah disepakati dengan guru); Alpa dihitung tidak hadir.
2. Hari efektif = hari Senin-Jumat (atau sesuai jadwal) dalam rentang periode, di luar hari libur yang tidak diinput.
3. Satu siswa hanya boleh terdaftar di satu kelas pada satu tahun ajaran aktif.
4. Wali kelas hanya bisa mengakses data kelas yang ia walikan; Guru Piket hanya bisa mengakses kelas yang didelegasikan kepadanya, dan hanya selama periode delegasi aktif.
5. Siswa hanya bisa mengakses data miliknya sendiri, termasuk grafiknya.
6. Izin yang disetujui otomatis menimpa status Alpa pada tanggal terkait (harian dan semua jam pelajaran hari itu).
7. Siswa yang akunnya nonaktif tidak muncul di daftar presensi baru.
8. Persentase per jam pelajaran = jumlah jam hadir (termasuk izin/sakit) / total jam terjadwal x 100%.
9. Guru Mapel hanya bisa menginput presensi per jam untuk jadwal yang ditugaskan kepadanya.

## 9. Non-Functional Requirements

| Aspek | Kebutuhan |
| --- | --- |
| Keamanan | Bcrypt, CSRF (Sanctum), validasi server-side, proteksi mass-assignment, upload file divalidasi tipe dan ukuran, endpoint dibatasi per role |
| Responsif | Tampilan nyaman di desktop dan HP (siswa mengajukan izin dari HP) |
| Performa | Halaman utama termuat kurang dari 3 detik di jaringan lokal; list memakai pagination |
| Kegunaan | Pesan error dan sukses jelas; loading state; konfirmasi aksi destruktif |
| Maintainability | Clean code, penamaan konsisten, pemisahan controller/service/request, komponen Vue reusable |
| Portabilitas | Berjalan di XAMPP/Laragon dengan PHP 8.2+, MySQL/MariaDB, Node.js LTS |

## 10. Arsitektur Teknis

**Pola:** SPA (Vue.js) + REST API (Laravel) dalam satu repository, dengan autentikasi Sanctum mode SPA berbasis session cookie (selaras dengan syarat session di jobsheet).

**Teknologi:**

- Backend: Laravel 11/12, Sanctum, Form Request, Eloquent, dompdf
- Frontend: Vue 3 (Composition API), Vue Router, Pinia, Axios, Chart.js (vue-chartjs), Vite
- UI: Bootstrap 5 atau Tailwind CSS (pilih satu)
- Database: MySQL/MariaDB

**Struktur folder (ringkas):**

- app/Http/Controllers/Api: AuthController, UserController, KelasController, SiswaController, JadwalController, PresensiController, PresensiJamController, IzinController, LaporanController, GrafikSiswaController, DelegasiPiketController
- app/Http/Requests: validasi per fitur
- app/Http/Middleware: CheckRole, CheckKelasAccess (cek wali kelas, delegasi piket, guru mapel)
- app/Models, app/Services (logika approval dan rekap)
- database/migrations, database/seeders
- routes/api.php
- resources/js: views/, components/, router/, stores/, services/api.js

## 11. Desain Basis Data

Minimal 4-5 tabel terelasi dipenuhi (di sini 10 tabel). Semua relasi memakai foreign key valid.

| Tabel | Kolom Utama | Relasi |
| --- | --- | --- |
| users | id, name, username (unique), password, role (admin/wali_kelas/guru_mapel/guru_piket/siswa), is_active | - |
| tahun_ajaran | id, nama, semester, tgl_mulai, tgl_selesai, is_active | - |
| kelas | id, nama_kelas, tingkat, jurusan, tahun_ajaran_id, wali_kelas_id | FK tahun_ajaran, FK users |
| siswa | id, user_id (unique), nis (unique), nisn, nama, jenis_kelamin, kelas_id | FK users, FK kelas |
| mata_pelajaran | id, kode, nama | - |
| jadwal_pelajaran | id, kelas_id, mapel_id, guru_id, hari, jam_mulai, jam_selesai | FK kelas, FK mata_pelajaran, FK users (guru mapel) |
| piket_delegasi | id, guru_id, kelas_id, tgl_mulai, tgl_selesai, dibuat_oleh | FK users (guru piket), FK kelas, FK users (admin) |
| izin | id, siswa_id, jenis (izin/sakit), tgl_mulai, tgl_selesai, alasan, bukti_path, status (pending/approved/rejected), diproses_oleh, catatan, diproses_pada | FK siswa, FK users |
| presensi (harian) | id, siswa_id, kelas_id, tanggal, status (hadir/izin/sakit/alpa), keterangan, izin_id (nullable), dicatat_oleh, unique(siswa_id, tanggal) | FK siswa, FK kelas, FK izin, FK users |
| presensi_jam | id, siswa_id, jadwal_id, tanggal, status (hadir/izin/sakit/alpa), keterangan, izin_id (nullable), dicatat_oleh, unique(siswa_id, jadwal_id, tanggal) | FK siswa, FK jadwal_pelajaran, FK izin, FK users |

**Relasi inti:** tahun_ajaran 1-N kelas; kelas 1-N siswa; users 1-1 siswa; users (wali) 1-N kelas; siswa 1-N izin; siswa 1-N presensi; izin 1-N presensi; jadwal_pelajaran 1-N presensi_jam; siswa 1-N presensi_jam; izin 1-N presensi_jam; users (guru mapel) 1-N jadwal_pelajaran; users (guru piket) 1-N piket_delegasi; kelas 1-N piket_delegasi.

**Query yang wajib muncul (untuk rubrik SQL):**

- INNER JOIN siswa-kelas-presensi untuk rekap kelas
- GROUP BY siswa_id dengan SUM(CASE WHEN status = ...) untuk hitung Hadir/Izin/Sakit/Alpa, dan GROUP BY YEAR/MONTH(tanggal) untuk data grafik per siswa
- WHERE tanggal BETWEEN untuk filter periode
- JOIN izin-siswa-kelas untuk daftar approval wali kelas dan guru piket (JOIN piket_delegasi untuk kelas delegasi)

**Seeder data sampel minimal:** 1 admin, 2 wali kelas, 2-3 guru mapel, 1 guru piket (didelegasikan ke 1-2 kelas), 2 kelas, 10+ siswa, beberapa jadwal, presensi harian minimal 2-3 bulan (agar grafik per siswa terisi), presensi per jam beberapa hari, beberapa izin dengan status berbeda.

## 12. Rancangan Endpoint API

| Method | Endpoint | Fungsi | Role |
| --- | --- | --- | --- |
| POST | /api/login | Login | Publik |
| POST | /api/logout | Logout | Semua |
| GET | /api/me | Data user login | Semua |
| CRUD | /api/users | Kelola user | Admin |
| CRUD | /api/tahun-ajaran | Kelola tahun ajaran | Admin |
| CRUD | /api/kelas | Kelola kelas | Admin |
| CRUD | /api/siswa | Kelola siswa | Admin |
| CRUD | /api/mapel, /api/jadwal | Mapel dan jadwal (dengan guru pengajar) | Admin (baca: sesuai role) |
| CRUD | /api/piket-delegasi | Kelola delegasi guru piket ke kelas | Admin |
| GET | /api/jadwal/saya | Jadwal mengajar milik guru | Guru Mapel |
| GET | /api/presensi?kelas_id=&tanggal= | Daftar presensi harian kelas | Wali Kelas, Guru Piket (kelas delegasi) |
| POST | /api/presensi | Simpan presensi harian massal | Wali Kelas, Guru Piket (kelas delegasi) |
| GET | /api/presensi-jam?kelas_id=&tanggal=&jadwal_id= | Daftar presensi per jam pelajaran | Guru Mapel, Wali Kelas, Guru Piket (kelas delegasi) |
| POST | /api/presensi-jam | Simpan presensi per jam massal | Guru Mapel, Wali Kelas, Guru Piket (kelas delegasi) |
| GET | /api/izin | Riwayat/daftar izin sesuai role | Siswa, Wali Kelas, Guru Piket (kelas delegasi) |
| POST | /api/izin | Ajukan izin (multipart) | Siswa |
| PUT/DELETE | /api/izin/{id} | Edit/batalkan saat pending | Siswa |
| POST | /api/izin/{id}/approve | Setujui | Wali Kelas, Guru Piket (kelas delegasi) |
| POST | /api/izin/{id}/reject | Tolak (catatan wajib) | Wali Kelas, Guru Piket (kelas delegasi) |
| GET | /api/laporan/rekap | Data rekap (filter periode) | Sesuai role |
| GET | /api/laporan/rekap/pdf | Unduh PDF rekap | Sesuai role |
| GET | /api/siswa/{id}/grafik?dari=&sampai= | Data grafik kehadiran per siswa | Sesuai role (siswa hanya miliknya) |
| GET | /api/dashboard | Statistik dashboard | Semua |

## 13. Daftar Halaman (untuk Wireframe)

**Umum:** Login.

**Admin:** Dashboard; Kelola User; Tahun Ajaran; Kelas; Siswa; Mata Pelajaran; Jadwal Pelajaran; Delegasi Guru Piket; Rekap Sekolah.

**Wali Kelas:** Dashboard (izin pending, kehadiran hari ini); Input Presensi Harian; Input Presensi per Jam; Daftar Pengajuan Izin (modal detail + bukti); Rekap Kelas; Detail & Grafik Siswa; Cetak Laporan PDF.

**Guru Piket:** Dashboard (izin pending kelas delegasi); Pilih Kelas & Input Presensi (harian dan per jam); Daftar Pengajuan Izin kelas delegasi; Rekap & Grafik Siswa; Cetak Laporan PDF.

**Guru Mapel:** Dashboard (jadwal mengajar hari ini); Input Presensi per Jam; Rekap Kehadiran per Jam (mata pelajaran saya); Cetak Rekap PDF.

**Siswa:** Dashboard (persentase kehadiran); Ajukan Izin; Riwayat Izin; Jadwal Pelajaran; Riwayat Presensi; Grafik Kehadiran Saya.

**Elemen interaktif (syarat jobsheet):** live search di tabel master, modal preview bukti dan konfirmasi hapus, form input presensi dengan tombol Set Semua Hadir dan hitungan otomatis, grafik interaktif per siswa (tooltip dan filter periode), preview gambar sebelum upload, validasi form real-time.

## 14. Rencana Pengujian (Blackbox)

| No | Modul | Skenario | Hasil Diharapkan |
| --- | --- | --- | --- |
| 1 | Login | Password salah | Ditolak, muncul pesan kesalahan login |
| 2 | Login | Akun nonaktif | Ditolak dengan pesan akun nonaktif |
| 3 | Validasi client | Klik Simpan pada form kosong | Submit ditahan, fokus ke kolom kosong |
| 4 | Session security | Akses URL halaman admin tanpa login | Redirect ke login |
| 5 | Otorisasi | Siswa mengakses halaman/API admin | Ditolak (403) |
| 6 | Otorisasi | Wali kelas A membuka data kelas wali B | Ditolak (403) |
| 7 | Upload | Upload file PDF/ukuran lebih dari 2 MB | Ditolak dengan pesan validasi |
| 8 | Logic izin | Tanggal selesai sebelum tanggal mulai | Ditolak |
| 9 | Logic izin | Rentang izin bertumpuk | Ditolak |
| 10 | Approval | Approve izin 3 hari | Presensi 3 hari otomatis berstatus Izin/Sakit |
| 11 | Approval | Reject tanpa catatan | Ditolak, catatan wajib |
| 12 | Presensi | Input presensi ganda di tanggal sama | Data diperbarui, tidak duplikat |
| 13 | Rekap | Filter periode | Angka dan persentase sesuai data |
| 14 | Hapus master | Hapus kelas yang masih punya siswa | Ditolak dengan pesan jelas |
| 15 | Presensi per jam | Input presensi untuk jadwal yang bukan hari itu | Ditolak dengan pesan validasi |
| 16 | Approval | Approve izin 1 hari | Semua jam pelajaran hari itu otomatis Izin/Sakit |
| 17 | Guru piket | Guru piket approve izin siswa dari kelas yang didelegasikan | Berhasil, tercatat diproses oleh guru piket |
| 18 | Grafik | Buka grafik siswa dengan data 3 bulan | Donut, bar bulanan, dan tren sesuai rekap |
| 19 | Otorisasi grafik | Siswa membuka grafik siswa lain (ubah ID di URL) | Ditolak (403) |
| 20 | Delegasi piket | Guru piket approve/membuka kelas yang tidak didelegasikan | Ditolak (403) |
| 21 | Delegasi piket | Guru piket mengakses kelas setelah tanggal delegasi berakhir | Ditolak (403) |
| 22 | Guru mapel | Guru mapel input presensi jam untuk jadwal guru lain | Ditolak (403) |
| 23 | Guru mapel | Guru mapel mengakses presensi harian | Ditolak (403) |

## 15. Roadmap 8 Minggu

| Minggu | Fokus | Deliverable |
| --- | --- | --- |
| 1 | Analisis kebutuhan | Dokumen ini sebagai dasar SRS + daftar fitur per role |
| 2 | Desain sistem | ERD + kamus data, flowchart (login, presensi, izin-approval), wireframe UI |
| 3 | Setup & autentikasi | Project Laravel + Vue jalan, migration + seeder, login/logout 5 role, middleware role dan akses kelas |
| 4 | CRUD master data | User, tahun ajaran, kelas, siswa, mapel, jadwal (dengan guru pengajar), delegasi piket + pagination |
| 5 | Transaksi & business logic | Input presensi harian + per jam, pengajuan izin + upload, approval (wali kelas/piket delegasi) + auto-presensi, query JOIN |
| 6 | Interaktivitas frontend | Validasi form Vue, live search, modal, UI responsif, dashboard, grafik per siswa (Chart.js) |
| 7 | Laporan & QA | Rekap + filter periode, cetak PDF (dompdf), hasil blackbox testing |
| 8 | Finalisasi | Refactor clean code, export database.sql, laporan PDF akhir, presentasi + demo |

## 16. Pemetaan ke Rubrik Penilaian

| Komponen (Bobot) | Bukti yang disiapkan |
| --- | --- |
| Kedisiplinan Milestone (15%) | Progress mingguan sesuai tabel roadmap, commit Git per minggu |
| Analisis & Perancangan (15%) | PRD/SRS, ERD dengan FK valid, flowchart, wireframe |
| Kualitas Database (15%) | 10 tabel ternormalisasi, FK dan unique constraint, query JOIN/GROUP BY, database.sql |
| Backend & Keamanan (25%) | CRUD lengkap, bcrypt, session auth + middleware role, logika auto-presensi, modul laporan |
| Frontend & Interaktivitas (15%) | UI rapi dan responsif, validasi client Vue, live search, modal, grafik per siswa |
| Dokumentasi & Presentasi (15%) | Laporan PDF lengkap, kode bersih, demo lancar |

## 17. Risiko & Mitigasi

| Risiko | Mitigasi |
| --- | --- |
| Scope bertambah (presensi per jam, role piket, grafik) | Kerjakan presensi harian dulu, presensi per jam memakai pola kode yang sama; grafik dibuat setelah rekap akurat |
| Scope terlalu besar untuk 8 minggu | Kerjakan fitur wajib dulu, stretch goal hanya jika Minggu 6 sudah aman |
| Setup Sanctum SPA (CORS/cookie) bermasalah | Jalankan Vue dan Laravel di domain yang sama (Vite proxy) sejak awal |
| Rekap salah hitung | Buat seeder data uji dengan hasil yang sudah dihitung manual sebagai pembanding |
| Demo gagal karena environment | Sertakan database.sql, .env.example, dan panduan instalasi di README |

## 18. Keputusan yang Sudah Disepakati

1. Stack Laravel + Vue.js: diizinkan oleh guru.
2. Persentase kehadiran: Izin dan Sakit dihitung hadir.
3. Presensi: harian per kelas dan per jam pelajaran.
4. Role Guru Piket: ada, sebagai pengganti wali kelas, hanya pada kelas yang didelegasikan.
5. Role Guru Mapel: ada, menginput presensi per jam untuk jadwal yang ia ajar.
6. Format cetak laporan: PDF.
7. Tambahan: grafik kehadiran per siswa.

### Masih perlu dikonfirmasi

1. Siapa yang mengatur delegasi guru piket: Admin/Kurikulum (asumsi PRD ini), atau wali kelas yang berhalangan?
2. Apakah Guru Mapel boleh mengoreksi presensi per jam setelah harinya lewat, dan sampai berapa hari?
