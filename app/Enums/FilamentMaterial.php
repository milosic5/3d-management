<?php

namespace App\Enums;

/**
 * Filament materials. The database stores the value as a plain string,
 * so adding a material (e.g. ABS, ASA, TPU) only needs a new case here.
 */
enum FilamentMaterial: string
{
    case Pla = 'pla';
    case Petg = 'petg';

    public function label(): string
    {
        return strtoupper($this->value);
    }

    /**
     * Tailwind classes for the material badge (app/Enums is in tailwind.config.js content).
     */
    public function color(): string
    {
        return match ($this) {
            self::Pla => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-950 dark:text-orange-400 dark:border-orange-900',
            self::Petg => 'bg-violet-100 text-violet-800 border-violet-200 dark:bg-violet-950 dark:text-violet-400 dark:border-violet-900',
        };
    }

    /**
     * @return array<int, array{value: string, label: string, color: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $material) => [
            'value' => $material->value,
            'label' => $material->label(),
            'color' => $material->color(),
        ], self::cases());
    }
}
