<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Normalize date inputs from various formats (d-m-Y, d/m/Y) to standard Y-m-d.
     */
    protected function normalizeDates(Request $request, array $dateFields): void
    {
        $merged = [];
        foreach ($dateFields as $field) {
            if ($request->filled($field)) {
                $val = trim((string) $request->input($field));
                if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $val, $matches)) {
                    $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $year = $matches[3];
                    $merged[$field] = "{$year}-{$month}-{$day}";
                }
            }
        }
        if (! empty($merged)) {
            $request->merge($merged);
        }
    }
}
