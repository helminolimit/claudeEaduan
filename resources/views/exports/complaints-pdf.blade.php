<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h2 { font-size: 14px; margin: 0 0 4px; }
        .meta { font-size: 9px; color: #666; margin-bottom: 10px; }
        .stats { display: flex; gap: 8px; margin-bottom: 12px; }
        .stat-box { border: 1px solid #d1d5db; border-radius: 4px; padding: 6px 10px; min-width: 80px; }
        .stat-label { font-size: 8px; color: #6b7280; }
        .stat-value { font-size: 16px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { background: #f3f4f6; text-align: left; padding: 5px 6px; border: 1px solid #d1d5db; font-size: 9px; }
        td { padding: 4px 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        tr { page-break-inside: avoid; }
        .even td { background: #f9fafb; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 9999px; font-size: 8px; font-weight: 600; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-in_review { background: #dbeafe; color: #1e40af; }
        .badge-in_progress { background: #cffafe; color: #155e75; }
        .badge-resolved { background: #dcfce7; color: #166534; }
        .badge-closed { background: #f4f4f5; color: #3f3f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .limit-notice { background: #fef3c7; border: 1px solid #fcd34d; padding: 4px 8px; margin-bottom: 8px; font-size: 9px; color: #92400e; }
        .footer { margin-top: 12px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>
    <h2>Laporan Aduan — e-Aduan</h2>
    <p class="meta">
        Tempoh: {{ $dateFrom ? date('d/m/Y', strtotime($dateFrom)) : '—' }} hingga {{ $dateTo ? date('d/m/Y', strtotime($dateTo)) : '—' }}
        &nbsp;·&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
        @if($limited ?? false)
            &nbsp;·&nbsp; Dihadkan kepada 500 daripada {{ $total }} rekod
        @endif
    </p>

    {{-- Summary stats --}}
    <table style="width:auto;margin-bottom:12px;">
        <tr>
            <th>Jumlah</th>
            <th>Pending</th>
            <th>Dalam Semakan</th>
            <th>Dalam Proses</th>
            <th>Selesai</th>
            <th>Ditutup</th>
            <th>Ditolak</th>
        </tr>
        <tr>
            <td><strong>{{ $stats['total'] }}</strong></td>
            <td>{{ $stats['pending'] }}</td>
            <td>{{ $stats['in_review'] }}</td>
            <td>{{ $stats['in_progress'] }}</td>
            <td>{{ $stats['resolved'] }}</td>
            <td>{{ $stats['closed'] }}</td>
            <td>{{ $stats['rejected'] }}</td>
        </tr>
    </table>

    @if(($limited ?? false))
        <p class="limit-notice">PDF dihadkan kepada 500 rekod. Guna eksport Excel untuk semua {{ $total }} rekod.</p>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:12%">No. Rujukan</th>
                <th style="width:20%">Tajuk</th>
                <th style="width:12%">Kategori</th>
                <th style="width:9%">Status</th>
                <th style="width:8%">Keutamaan</th>
                <th style="width:12%">Pengadu</th>
                <th style="width:12%">Pegawai</th>
                <th style="width:15%">Tarikh Hantar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($complaints as $i => $complaint)
                <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
                    <td style="font-family:monospace;font-size:8px;">{{ $complaint->aduan_no }}</td>
                    <td>{{ $complaint->title }}</td>
                    <td>{{ $complaint->category?->name ?? '—' }}</td>
                    <td>
                        <span class="badge badge-{{ $complaint->status->value }}">
                            {{ $complaint->status->label() }}
                        </span>
                    </td>
                    <td>{{ $complaint->priority->label() }}</td>
                    <td>{{ $complaint->user?->name ?? '—' }}</td>
                    <td>{{ $complaint->officer?->name ?? '—' }}</td>
                    <td>{{ $complaint->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">
        Dijana oleh e-Aduan &nbsp;·&nbsp; {{ now()->format('d/m/Y H:i') }}
    </p>
</body>
</html>
