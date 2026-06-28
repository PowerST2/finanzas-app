<?php

namespace App\Support;

use App\Models\CurrencyOption;

class Currency
{
    public static function convert(float $amount, ?string $from, ?string $to): float
    {
        $from = $from ?: 'PEN';
        $to = $to ?: 'PEN';

        if ($from === $to) {
            return round($amount, 2);
        }

        $rates = CurrencyOption::pluck('exchange_rate_to_pen', 'code');
        $fromRate = (float) ($rates[$from] ?? 1);
        $toRate = (float) ($rates[$to] ?? 1);

        return round(($amount * $fromRate) / max($toRate, 0.0001), 2);
    }
}
