<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineMonthlyStock;
use Carbon\Carbon;

class MedicineStockTracker
{
    public static function adjustUsage(Medicine $medicine, Carbon $date, int $quantity): void
    {
        if ($quantity === 0) {
            return;
        }

        $record = static::ensureRecord($medicine, $date);
        $record->usage_quantity = max(0, $record->usage_quantity + $quantity);
        $record->closing_stock = static::calculateClosing($record);
        $record->save();

        static::propagateForwardFrom($record);
    }

    public static function adjustAdjustment(Medicine $medicine, Carbon $date, int $quantity): void
    {
        if ($quantity === 0) {
            return;
        }

        $record = static::ensureRecord($medicine, $date);
        $record->adjustment_quantity += $quantity;
        $record->closing_stock = static::calculateClosing($record);
        $record->save();

        static::propagateForwardFrom($record);
    }

    protected static function ensureRecord(Medicine $medicine, Carbon $date): MedicineMonthlyStock
    {
        $periodStart = $date->copy()->startOfMonth();

        $record = MedicineMonthlyStock::firstOrNew([
            'medicine_id' => $medicine->id,
            'period_start' => $periodStart->toDateString(),
        ]);

        if (!$record->exists) {
            $opening = static::determineOpeningStock($medicine, $periodStart);

            $record->opening_stock = $opening;
            $record->usage_quantity = $record->usage_quantity ?? 0;
            $record->adjustment_quantity = $record->adjustment_quantity ?? 0;
            $record->closing_stock = static::calculateClosing($record);
            $record->save();
        }

        return $record->fresh();
    }

    protected static function determineOpeningStock(Medicine $medicine, Carbon $periodStart): int
    {
        $previousRecord = MedicineMonthlyStock::where('medicine_id', $medicine->id)
            ->where('period_start', '<', $periodStart->toDateString())
            ->orderBy('period_start', 'desc')
            ->first();

        if ($previousRecord) {
            return $previousRecord->closing_stock;
        }

        return $medicine->stock_initial ?? $medicine->stock_quantity;
    }

    protected static function propagateForwardFrom(MedicineMonthlyStock $record): void
    {
        $closing = $record->closing_stock;

        $nextRecords = MedicineMonthlyStock::where('medicine_id', $record->medicine_id)
            ->where('period_start', '>', $record->period_start)
            ->orderBy('period_start')
            ->get();

        foreach ($nextRecords as $next) {
            $next->opening_stock = $closing;
            $next->closing_stock = static::calculateClosing($next);
            $next->save();
            $closing = $next->closing_stock;
        }
    }

    protected static function calculateClosing(MedicineMonthlyStock $record): int
    {
        return (int) ($record->opening_stock + $record->adjustment_quantity - $record->usage_quantity);
    }
}
