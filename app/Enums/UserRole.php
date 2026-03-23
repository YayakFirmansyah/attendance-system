<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case DOSEN = 'dosen';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::DOSEN => 'Dosen',
        };
    }

    public function canManageMasterData(): bool
    {
        return $this === self::ADMIN;
    }

    public function canScanAttendance(): bool
    {
        return $this === self::DOSEN;
    }

    public function canManageAttendance(): bool
    {
        return $this === self::DOSEN;
    }

    public function canViewReports(): bool
    {
        return true; // Both can view
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
