<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'reference_no', 'category', 'amount', 'expense_date', 'description',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Auto-generate reference number: EXP-20260522-001
     */
    public static function generateReferenceNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = "EXP-{$date}-";
        $lastExpense = static::where('reference_no', 'like', "{$prefix}%")
            ->orderByDesc('reference_no')
            ->first();

        if ($lastExpense) {
            $lastNumber = (int) str_replace($prefix, '', $lastExpense->reference_no);
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }
}
