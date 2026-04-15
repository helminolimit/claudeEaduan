<?php

namespace App\Exports;

use App\Models\Complaint;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComplaintsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        public readonly string $dateFrom = '',
        public readonly string $dateTo = '',
        public readonly string $filterStatus = '',
        public readonly string $filterCategory = '',
        public readonly string $filterPriority = '',
        public readonly string $filterOfficer = '',
    ) {}

    public function query()
    {
        return Complaint::query()
            ->with(['user', 'category', 'officer'])
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterPriority, fn ($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterOfficer, fn ($q) => $q->where('officer_id', $this->filterOfficer))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'No. Rujukan',
            'Tajuk',
            'Kategori',
            'Status',
            'Keutamaan',
            'Pengadu',
            'Pegawai',
            'Lokasi',
            'Tarikh Hantar',
            'Tarikh Kemaskini',
        ];
    }

    /** @param  Complaint  $complaint */
    public function map($complaint): array
    {
        return [
            $complaint->aduan_no,
            $complaint->title,
            $complaint->category?->name ?? '—',
            $complaint->status->label(),
            $complaint->priority->label(),
            $complaint->user?->name ?? '—',
            $complaint->officer?->name ?? '—',
            $complaint->location,
            $complaint->created_at->format('d/m/Y H:i'),
            $complaint->updated_at->format('d/m/Y H:i'),
        ];
    }
}
