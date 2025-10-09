<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    protected $table = 'ledgers';
    public $timestamps = false;


    protected $fillable = [
        'group_id',
        'name',
        'code',
        'op_balance',
        'op_balance_dc',
        'type',
        'reconciliation',
        'notes'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public static function getOpeningDifference()
    {
        return [
            'opdiff_balance' => 0,
            'opdiff_balance_dc' => 'D',
        ];
    }

    public function entries()
    {
        return $this->hasMany(EntryItem::class);
    }


    public static function getOpeningDiff()
    {
        $ledgers = self::all();
        $total = 0;

        foreach ($ledgers as $ledger) {
            $opBalance = (float) $ledger->op_balance;
            if ($ledger->op_balance_dc === 'D') {
                $total += $opBalance;
            } elseif ($ledger->op_balance_dc === 'C') {
                $total -= $opBalance;
            }
        }

        // Determine the balancing direction
        if ($total >= 0) {
            return [
                'opdiff_balance_dc' => 'C',
                'opdiff_balance' => $total
            ];
        } else {
            return [
                'opdiff_balance_dc' => 'D',
                'opdiff_balance' => abs($total)
            ];
        }
    }
}
