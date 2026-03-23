<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case ABSENT = 'absent';
    case EXCUSED = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::PRESENT => 'Hadir',
            self::LATE => 'Terlambat',
            self::ABSENT => 'Tidak Hadir',
            self::EXCUSED => 'Izin',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PRESENT => 'success',
            self::LATE => 'warning',
            self::ABSENT => 'danger',
            self::EXCUSED => 'info',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PRESENT => 'badge-success',
            self::LATE => 'badge-warning',
            self::ABSENT => 'badge-danger',
            self::EXCUSED => 'badge-info',
        };
    }

    public function isCounted(): bool
    {
        // Present and Late count as attended
        return in_array($this, [self::PRESENT, self::LATE]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
