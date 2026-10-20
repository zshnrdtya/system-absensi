<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Presensi Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 13px;
            font-weight: normal;
            margin: 3px 0 0 0;
        }
        .header p {
            font-size: 10px;
            color: #64748b;
            margin: 3px 0 0 0;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        .signatures {
            width: 100%;
            margin-top: 30px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 11px;
        }
        .sign-space {
            height: 60px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>1dex-sybsen &bull; Laporan Rekapitulasi Presensi</h1>
        <h2>Sistem Informasi Presensi Siswa Terpadu</h2>
        <p>Laporan Resmi Rekapitulasi Kehadiran Siswa Semester Ganjil 2026/2027</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Kelas:</strong></td>
            <td style="width: 35%;">{{ $kelas ? $kelas->nama_kelas . ' (' . $kelas->jurusan . ')' : 'Semua Kelas' }}</td>
            <td style="width: 15%;"><strong>Periode:</strong></td>
            <td style="width: 35%;">{{ $dari }} s/d {{ $sampai }}</td>
        </tr>
        <tr>
            <td><strong>Wali Kelas:</strong></td>
            <td>{{ $kelas && $kelas->waliKelas ? $kelas->waliKelas->name : '-' }}</td>
            <td><strong>Jenis Rekap:</strong></td>
            <td>{{ $tipe === 'jam' ? 'Presensi Per Jam Pelajaran' : 'Presensi Harian' }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th class="text-center" style="width: 15%;">NIS</th>
                <th style="width: 35%;">Nama Lengkap Siswa</th>
                <th class="text-center" style="width: 8%;">Hadir</th>
                <th class="text-center" style="width: 8%;">Izin</th>
                <th class="text-center" style="width: 8%;">Sakit</th>
                <th class="text-center" style="width: 8%;">Alpa</th>
                <th class="text-center" style="width: 13%;">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapList as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $row['nis'] }}</td>
                    <td>{{ $row['nama'] }}</td>
                    <td class="text-center">{{ $row['hadir'] }}</td>
                    <td class="text-center">{{ $row['izin'] }}</td>
                    <td class="text-center">{{ $row['sakit'] }}</td>
                    <td class="text-center">{{ $row['alpa'] }}</td>
                    <td class="text-center font-bold">{{ $row['persentase'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada data rekaman presensi pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah / Kurikulum
                <div class="sign-space"></div>
                <strong>( Administrator Kurikulum )</strong><br>
                NIP. 19800512 200501 1 002
            </td>
            <td>
                Dicetak pada: {{ date('d F Y') }}<br>
                Wali Kelas {{ $kelas ? $kelas->nama_kelas : '' }}
                <div class="sign-space"></div>
                <strong>( {{ $kelas && $kelas->waliKelas ? $kelas->waliKelas->name : 'Wali Kelas' }} )</strong><br>
                NIP. 19850720 201001 2 015
            </td>
        </tr>
    </table>
</body>
</html>
