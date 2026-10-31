<?php

namespace App\Enums;

/**
 * Order lifecycle. Labels live in the i18n files under "status.<value>";
 * colours are defined here once and shared with the frontend and the PDF.
 */
enum OrderStatus: string
{
    case Received = 'received';
    case Printing = 'printing';
    case Finished = 'finished';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /**
     * Statuses of orders still waiting for or in production.
     *
     * @return array<int, self>
     */
    public static function active(): array
    {
        return [self::Received, self::Printing];
    }

    /**
     * Tailwind classes for badges and status selects (app/Enums is in tailwind.config.js content).
     */
    public function color(): string
    {
        return match ($this) {
            self::Received => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200',
            self::Printing => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
            self::Finished => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200',
            self::Delivered => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            self::Cancelled => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
        };
    }

    /**
     * Same palette as hex [background, text] for places without Tailwind (PDF).
     *
     * @return array{0: string, 1: string}
     */
    public function hexColors(): array
    {
        return match ($this) {
            self::Received => ['#f1f5f9', '#475569'],
            self::Printing => ['#dbeafe', '#1d4ed8'],
            self::Finished => ['#fed7aa', '#c2410c'],
            self::Delivered => ['#dcfce7', '#16a34a'],
            self::Cancelled => ['#fee2e2', '#dc2626'],
        };
    }

    /**
     * @return array<int, array{value: string, color: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $status) => [
            'value' => $status->value,
            'color' => $status->color(),
        ], self::cases());
    }
}
