<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Presensi — Attendly</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 18px;
            color: #4f46e5;
            margin: 0 0 4px 0;
        }
        .header p {
            margin: 2px 0;
            font-size: 10px;
            color: #64748b;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 9px;
        }
        .meta-table td {
            padding: 2px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.data-table th {
            background-color: #4f46e5;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 9px;
            border: 1px solid #4f46e5;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 8px;
        }
        .status-ontime { background-color: #d1fae5; color: #065f46; }
        .status-late { background-color: #fef3c7; color: #92400e; }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>ATTENDLY — LAPORAN REKAPITULASI PRESENSI</h1>
        <p><strong>PT Attendly Digital Indonesia</strong> — Sistem Absensi Karyawan Terintegrasi</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Periode Laporan:</strong></td>
            <td style="width: 35%;">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
            <td style="width: 15%;"><strong>Dicetak Pada:</strong></td>
            <td style="width: 35%;">{{ $generatedAt }}</td>
        </tr>
        <tr>
            <td><strong>Total Baris:</strong></td>
            <td>{{ $attendances->count() }} Record</td>
            <td><strong>Dicetak Oleh:</strong></td>
            <td>{{ $generatedBy }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 10%;">NIK</th>
                <th style="width: 18%;">Nama Karyawan</th>
                <th style="width: 12%;">Departemen</th>
                <th style="width: 14%;">Cabang</th>
                <th style="width: 9%;">Masuk</th>
                <th style="width: 9%;">Pulang</th>
                <th style="width: 7%;">Jarak</th>
                <th style="width: 11%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $idx => $att)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>{{ $att->attendance_date->format('d/m/Y') }}</td>
                    <td>{{ $att->employee->employee_code }}</td>
                    <td><strong>{{ $att->employee->full_name }}</strong></td>
                    <td>{{ $att->employee->department->name ?? '-' }}</td>
                    <td>{{ $att->branch->name }}</td>
                    <td>{{ $att->check_in_at ? $att->check_in_at->format('H:i:s') : '-' }}</td>
                    <td>{{ $att->check_out_at ? $att->check_out_at->format('H:i:s') : '-' }}</td>
                    <td>{{ $att->check_in_distance ? $att->check_in_distance.'m' : '-' }}</td>
                    <td>
                        <span class="status-badge {{ $att->check_in_status === 'on_time' ? 'status-ontime' : 'status-late' }}">
                            {{ $att->check_in_status_label }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 15px;">Tidak ada data presensi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh sistem Attendly pada {{ $generatedAt }}.
    </div>

</body>
</html>
