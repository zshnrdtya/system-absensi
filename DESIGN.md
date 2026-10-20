# DESIGN.md

> **Direction and Identity Specification: 1dex-sybsen**  
> Mengacu pada standar Rules-Raditya CODE. Dokumen ini mendefinisikan jiwa, identitas, tipografi, palet warna, dan kalibrasi dial sistem.

---

## 1. Identity & Purpose

- **Product Name:** 1dex-sybsen
- **One-Line Value Proposition:** Sistem terintegrasi pencatatan presensi harian, presensi jam pelajaran, dan workflow persetujuan izin/sakit siswa untuk institusi pendidikan.
- **Target Audience:** Admin/Kurikulum, Wali Kelas, Guru Mata Pelajaran, Guru Piket, dan Siswa.
- **Brand Personality:** *Utilitarian, Disiplin, Jelas* (Fokus pada efisiensi entri cepat kurang dari 2 menit per kelas, konsistensi data, dan kejelasan status izin).

---

## 2. Visual Theme & Surface

- **Theme Mode:** Dual mode dengan saklar tema yang berfungsi penuh (Light mode sebagai tema default ruang kelas yang terang, Dark mode untuk kenyamanan admin/guru di ruangan redup).
- **Visual Motif:** Sistem arsip akademik digital modern, tabel data presensi padat dan terstruktur rapi, tanpa dekorasi grafis yang mengalihkan fokus.
- **Surface Treatment:** Matte flat, border tipis 1px tegas (`#E2E8F0` / `#334155`), elevasi bayangan halus hanya digunakan untuk dialog/modal konfirmasi dan dropdown. Bebas dari efek glassmorphism atau blur mengambang tanpa hierarki.

---

## 3. Color Palette

> **Antislop Constraint:** Dibatasi pada 2–3 warna inti + 1 warna aksen utama. Semua rasio kontras teks teruji memenuhi standar WCAG AA (>= 4.5:1).

- **Background (Canvas):** `#F8FAFC` (Light) / `#0F172A` (Dark)
- **Surface / Card:** `#FFFFFF` (Light) / `#1E293B` (Dark)
- **Primary Text:** `#0F172A` (Light, rasio kontras 17.06:1) / `#F8FAFC` (Dark, rasio kontras 17.06:1)
- **Muted Text / Border:** `#475569` (Light, rasio kontras 7.24:1) / `#94A3B8` (Dark, rasio kontras 6.96:1)
- **Deliberate Accent:** `#1D4ED8` (Blue 700, rasio kontras 6.70:1 pada putih)
  - *Accent Function:* Khusus tombol tindakan utama (Submit, Set Semua Hadir, Konfirmasi) dan indikator navigasi aktif.
- **Semantic Status Indicators (Badge Presensi):**
  - **Hadir:** Text `#047857` (Emerald 700) pada background `#ECFDF5` (rasio kontras 5.21:1)
  - **Izin:** Text `#B45309` (Amber 700) pada background `#FEF3C7` (rasio kontras 4.72:1)
  - **Sakit:** Text `#6D28D9` (Violet 700) pada background `#F5F3FF` (rasio kontras 5.86:1)
  - **Alpa:** Text `#B91C1C` (Red 700) pada background `#FEF2F2` (rasio kontras 5.68:1)

---

## 4. Typography Hierarchy

- **Font Sans (`--font-sans`):** `Space Grotesk`, fallback `system-ui, sans-serif` (digunakan untuk teks umum: body, heading, UI label, dan semua teks default).
- **Font Mono (`--font-mono`):** `Space Mono`, fallback `ui-monospace, monospace` (digunakan untuk teks monospasi: angka statistik, kode referensi, slug, dan timestamp).
- **Scale Rhythm:**
  - H1 / Page Header: `clamp(1.5rem, 2.5vw, 2rem)` (font-weight: 600)
  - Section Header: `1.25rem` (20px, font-weight: 600)
  - Card Title / Table Header: `0.9375rem` (15px, font-weight: 500)
  - Body Text: `0.875rem` (14px, line-height: 1.5, font-weight: 400)
  - Caption / Badge / Help text: `0.75rem` (12px, font-weight: 500)

---

## 5. Antislop Dials (R-37)

- **ENERGY: 1 (Calm)**  
  Fokus pada utilitas instrumen sekolah: bersih, tata letak bernapas teratur, kontras fungsional tinggi, tidak ada ornamen visual yang berisik.
- **RHYTHM: 2 (Structured Variation)**  
  Variasi layout sesuai kebutuhan aksi: grid ringkasan statistik yang ringkas, tabel presensi cepat yang fleksibel, formulir pengajuan dengan area unggah terstruktur.
- **MOTION: 1 (Instant / Hover Only)**  
  Transisi cepat 150ms khusus interaksi tombol, dropdown, dan modal. Bebas dari animasi melayang, looping, atau transisi lambat yang menghambat pekerjaan guru.

---

## 6. Layout & Sizing Rules

- **Container Max-Width:** `1280px` (dashboard & rekap), `640px` (formulir pengajuan siswa di perangkat seluler).
- **Border Radii:**
  - Button & Form Inputs: `6px` (tegas dan profesional)
  - Cards, Tables & Modals: `8px`
- **Mobile Tap Target:** Minimal `44x44px` untuk semua tombol status presensi (H/I/S/A) dan aksi penting.
- **Responsive Stacking:** Tabel presensi dan rekap otomatis reflow atau menyediakan kontrol baris sentuh yang nyaman di layar ponsel saat siswa atau guru mengaksesnya dari smartphone.

---

## 7. Voice & Copywriting Directives

- **Tone:** Lugas, informatif, dan baku dalam bahasa Indonesia institusi pendidikan.
- **Aturan Ketat:**
  - Tidak menggunakan em dash (`—`).
  - Tidak menggunakan kata klise marketing AI (contoh: *seamless, revolusioner, tingkatkan perjalanan, unlock, kekuatan kecerdasan*).
  - Pesan error dan konfirmasi harus menyebutkan penyebab dan tindakan koreksi secara spesifik.
