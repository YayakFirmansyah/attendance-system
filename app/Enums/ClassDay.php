<?php

namespace App\Enums;

enum ClassDay: string
{
    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY = 'thursday';
    case FRIDAY = 'friday';
    case SATURDAY = 'saturday';
    case SUNDAY = 'sunday';

    public function label(): string
    {
        return match ($this) {
            self::MONDAY => 'Senin',
            self::TUESDAY => 'Selasa',
            self::WEDNESDAY => 'Rabu',
            self::THURSDAY => 'Kamis',
            self::FRIDAY => 'Jumat',
            self::SATURDAY => 'Sabtu',
            self::SUNDAY => 'Minggu',
        };
    }

    public function labelShort(): string
    {
        return match ($this) {
            self::MONDAY => 'Sen',
            self::TUESDAY => 'Sel',
            self::WEDNESDAY => 'Rab',
            self::THURSDAY => 'Kam',
            self::FRIDAY => 'Jum',
            self::SATURDAY => 'Sab',
            self::SUNDAY => 'Min',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::MONDAY => 1,
            self::TUESDAY => 2,
            self::WEDNESDAY => 3,
            self::THURSDAY => 4,
            self::FRIDAY => 5,
            self::SATURDAY => 6,
            self::SUNDAY => 7,
        };
    }

    public static function fromDate(\DateTime $date): self
    {
        $dayName = strtolower($date->format('l'));
        return self::from($dayName);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}
