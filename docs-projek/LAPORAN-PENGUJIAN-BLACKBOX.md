# Laporan Pengujian Blackbox: 1dex-sybsen

> Pengujian Kualitas dan Verifikasi Persyaratan Fungsional (PRD Opsi C & Rubrik Penilaian PAS)  
> Total Skenario: 23 Skenario | Status Keseluruhan: **100% LULUS (PASS)**

---

## Ringkasan Matriks Pengujian 23 Skenario

| No | Modul | Skenario Pengujian | Input Uji | Hasil Diharapkan | Hasil Pengujian | Status |
|---|---|---|---|---|---|:---:|
| 1 | Login | Autentikasi dengan password salah | Password keliru | Akses ditolak, muncul pesan kesalahan kredensial | Muncul pesan error validasi kredensial | **PASS** |
| 2 | Login | Autentikasi akun berstatus nonaktif | Akun `is_active = false` | Ditolak dengan pesan penonaktifan akun | Ditolak dengan pesan: *"Akun Anda berstatus nonaktif..."* | **PASS** |
| 3 | Validasi Client | Klik Simpan pada form kosong | Field formulir kosong | Pengiriman ditahan di browser, fokus pada kolom input kosong | Validasi browser / event ditahan dan pesan tampil | **PASS** |
| 4 | Session Security | Akses rute internal tanpa login | Akses URL `/dashboard` | Dialihkan (*redirect*) ke rute login | Status 302 redirect ke `/login` | **PASS** |
| 5 | Otorisasi | Siswa mengakses halaman admin | Akun role `siswa` akses `/admin/users` | Akses ditolak dengan status HTTP 403 Forbidden | Status HTTP 403 Forbidden | **PASS** |
| 6 | Otorisasi | Wali Kelas A membuka kelas Wali B | Akun Wali Kelas A akses kelas B | Akses ditolak dengan status HTTP 403 Forbidden | Status HTTP 403 Forbidden | **PASS** |
| 7 | Upload | Unggah berkas PDF / ukuran > 2 MB | File `.pdf` atau ukuran 2.5 MB | Ditolak dengan pesan validasi format/ukuran | Validasi server-side dan client-side menolak | **PASS** |
| 8 | Logic Izin | Tanggal selesai sebelum tanggal mulai | `tgl_selesai < tgl_mulai` | Ditolak dengan pesan tanggal tidak valid | Error validasi: `tgl_selesai after_or_equal` | **PASS** |
| 9 | Logic Izin | Rentang tanggal izin bertumpuk | Tanggal tumpang tindih | Ditolak dengan pesan rentang bertumpuk | Validasi overlap menolak pengajuan | **PASS** |
| 10 | Approval | Menyetujui izin rentang 3 hari | Izin 3 hari disetujui | Presensi 3 hari kerja otomatis terbuat berstatus Izin/Sakit | 3 record presensi harian otomatis tersimpan | **PASS** |
| 11 | Approval | Menolak pengajuan tanpa catatan | Catatan dikosongkan | Ditolak, catatan alasan penolakan wajib diisi | Error validasi `catatan wajib diisi` | **PASS** |
| 12 | Presensi | Input presensi berulang di tanggal sama | Submit presensi ganda | Data terbarui, tidak terjadi duplikasi baris | Baris unik diperbarui via `updateOrCreate` | **PASS** |
| 13 | Rekap | Filter periode rentang tanggal | Filter `dari` dan `sampai` | Angka rekap dan persentase kehadiran terhitung akurat | Perhitungan `(H+I+S)/Total*100%` tepat | **PASS** |
| 14 | Hapus Master | Hapus kelas yang masih memiliki siswa | Hapus kelas terisi | Ditolak dengan notifikasi kelas masih dipakai | Ditolak dengan pesan relasi siswa terdaftar | **PASS** |
| 15 | Presensi Jam | Input presensi untuk hari yang tidak cocok | Jadwal Senin diinput di hari Selasa | Ditolak dengan pesan validasi jadwal hari | Ditolak dengan pesan ketidakcocokan hari | **PASS** |
| 16 | Approval | Menyetujui izin 1 hari | Izin 1 hari disetujui | Seluruh jam pelajaran hari itu otomatis berstatus Izin/Sakit | Presensi jam otomatis tersimpan | **PASS** |
| 17 | Guru Piket | Guru piket approve kelas delegasi aktif | Guru piket pada kelas tugas | Berhasil, tercatat diproses oleh guru piket | Disetujui dan tercatat `diproses_oleh` piket | **PASS** |
| 18 | Grafik | Menampilkan grafik kehadiran siswa | Buka detail grafik siswa | Donut, bar bulanan, dan tren persentase tampil | Data komposisi dan bulanan terhitung tepat | **PASS** |
| 19 | Otorisasi Grafik | Siswa membuka grafik siswa lain di URL | Siswa akses `/siswa/{id_lain}/grafik` | Akses ditolak dengan status HTTP 403 Forbidden | Status HTTP 403 Forbidden | **PASS** |
| 20 | Delegasi Piket | Guru piket buka kelas tanpa delegasi | Piket akses kelas luar tugas | Akses ditolak dengan status HTTP 403 Forbidden | Status HTTP 403 Forbidden | **PASS** |
| 21 | Delegasi Piket | Akses kelas setelah masa delegasi habis | Piket akses tugas kedaluwarsa | Akses ditolak dengan status HTTP 403 Forbidden | Status HTTP 403 Forbidden | **PASS** |
| 22 | Guru Mapel | Guru mapel input jadwal guru lain | Mapel akses jadwal pengajar lain | Akses ditolak dengan status HTTP 403 Forbidden | Status HTTP 403 Forbidden | **PASS** |
| 23 | Guru Mapel | Guru mapel mengakses presensi harian | Mapel akses `/presensi/harian` | Akses ditolak dengan status HTTP 403 Forbidden | Status HTTP 403 Forbidden | **PASS** |

---

## Bukti Eksekusi Uji Otomatis (*Automated Feature Tests*)

Pengujian dilakukan melalui test suite PHPUnit:
- `tests/Feature/AuthTest.php` (Skenario 1, 2, 4)
- `tests/Feature/MasterDataTest.php` (Skenario 5, 14)
- `tests/Feature/IzinWorkflowTest.php` (Skenario 7, 8, 9, 10, 11, 16, 17, 20, 21)
- `tests/Feature/PresensiWorkflowTest.php` (Skenario 6, 12, 15, 22, 23)
- `tests/Feature/LaporanGrafikTest.php` (Skenario 13, 18, 19)

**Hasil Akhir Uji:** `33 passed, 96 assertions (100% Green)`
