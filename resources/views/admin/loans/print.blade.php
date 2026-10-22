<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman Peralatan - GhostGear Rental</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #0f172a;
            padding: 24px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .brand h1 {
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
        }
        .brand p {
            color: #64748b;
            font-size: 13px;
            margin-top: 4px;
        }
        .meta {
            text-align: right;
            font-size: 12px;
            color: #475569;
        }
        .action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.15s ease-in-out;
        }
        .btn-primary {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #0369a1;
        }
        .btn-secondary {
            background-color: #e2e8f0;
            color: #334155;
        }
        .btn-secondary:hover {
            background-color: #cbd5e1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 24px;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 11px;
        }
        tr:hover td {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 9999px;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }
        .footer-summary {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #cbd5e1;
            font-size: 13px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 48px;
            padding: 0 40px;
        }
        .sig-box {
            text-align: center;
            width: 200px;
        }
        .sig-space {
            height: 60px;
        }
        .sig-name {
            font-weight: 600;
            border-top: 1px solid #94a3b8;
            padding-top: 4px;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .container {
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            table {
                font-size: 10pt;
            }
            th, td {
                padding: 6px 8px;
            }
            @page {
                size: landscape;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="action-bar no-print">
            <a href="/admin/loans" class="btn btn-secondary">&larr; Kembali ke Panel Admin</a>
            <button onclick="window.print()" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Cetak Dokumen
            </button>
        </div>

        <div class="header">
            <div class="brand">
                <h1>GhostGear Rental</h1>
                <p>Pusat Penyewaan Peralatan Investigasi Paranormal Terakreditasi</p>
            </div>
            <div class="meta">
                <p><strong>Laporan Resmi Riwayat Peminjaman</strong></p>
                <p>Dicetak pada: {{ $printedAt->format('d/m/Y H:i:s') }} WIB</p>
                <p>Oleh Admin: {{ auth()->user()->profile?->full_name ?? auth()->user()->username }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">No</th>
                    <th>Anggota</th>
                    <th>Kode Unit</th>
                    <th>Nama Alat</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tenggat</th>
                    <th>Tanggal Kembali</th>
                    <th class="text-center">Hari Telat</th>
                    <th class="text-right">Denda</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($loans as $index => $loan)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $loan->member?->profile?->full_name ?? $loan->member?->username }}</strong>
                            <div style="font-size: 11px; color: #64748b;">&#64;{{ $loan->member?->username }}</div>
                        </td>
                        <td><code>{{ $loan->unit?->unit_code ?? '-' }}</code></td>
                        <td>{{ $loan->unit?->equipment?->name ?? '-' }}</td>
                        <td>{{ $loan->borrow_date ? $loan->borrow_date->format('d M Y') : '-' }}</td>
                        <td>{{ $loan->due_date ? $loan->due_date->format('d M Y') : '-' }}</td>
                        <td>{{ $loan->return_date ? $loan->return_date->format('d M Y') : '-' }}</td>
                        <td class="text-center">
                            @if ($loan->late_days > 0)
                                <span class="badge badge-danger">{{ $loan->late_days }} hari</span>
                            @else
                                <span style="color: #64748b;">0</span>
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($loan->fine_amount > 0)
                                <strong style="color: #b91c1c;">Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}</strong>
                                <div style="font-size: 10px; color: {{ $loan->fine_paid ? '#15803d' : '#b91c1c' }};">
                                    ({{ $loan->fine_paid ? 'Lunas' : 'Belum Lunas' }})
                                </div>
                            @else
                                <span style="color: #64748b;">Rp 0</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 32px; color: #64748b;">
                            Belum ada riwayat peminjaman yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer-summary">
            <div>
                <strong>Total Transaksi:</strong> {{ $loans->count() }} peminjaman
            </div>
            <div>
                <strong>Total Denda Terakumulasi:</strong>
                Rp {{ number_format($loans->sum('fine_amount'), 0, ',', '.') }}
            </div>
        </div>

        <div class="signatures">
            <div class="sig-box">
                <p>Mengetahui,</p>
                <p>Kepala Logistik Paranormal</p>
                <div class="sig-space"></div>
                <div class="sig-name">GhostGear HQ</div>
            </div>
            <div class="sig-box">
                <p>Petugas Validasi,</p>
                <p>Administrator Bertugas</p>
                <div class="sig-space"></div>
                <div class="sig-name">{{ auth()->user()->profile?->full_name ?? auth()->user()->username }}</div>
            </div>
        </div>
    </div>
</body>
</html>
