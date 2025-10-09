<?php

namespace Database\Seeders;

use App\Models\EntryType;
use Illuminate\Database\Seeder;

class EntryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $entryTypes = [
            [
                'label' => 'journal',
                'name' => 'Journal Voucher',
                'description' => 'General journal entry',
                'base_type' => EntryType::BASE_TYPE_JOURNAL,
                'numbering' => EntryType::NUMBERING_AUTO,
                'prefix' => 'JV',
                'suffix' => '',
                'zero_padding' => 4,
                'restriction_bankcash' => EntryType::RESTRICTION_NONE,
            ],
            [
                'label' => 'receipt',
                'name' => 'Receipt Voucher',
                'description' => 'Cash/Bank receipt entry',
                'base_type' => EntryType::BASE_TYPE_RECEIPT,
                'numbering' => EntryType::NUMBERING_AUTO,
                'prefix' => 'RV',
                'suffix' => '',
                'zero_padding' => 4,
                'restriction_bankcash' => EntryType::RESTRICTION_EXACTLY_ONE,
            ],
            [
                'label' => 'payment',
                'name' => 'Payment Voucher',
                'description' => 'Cash/Bank payment entry',
                'base_type' => EntryType::BASE_TYPE_PAYMENT,
                'numbering' => EntryType::NUMBERING_AUTO,
                'prefix' => 'PV',
                'suffix' => '',
                'zero_padding' => 4,
                'restriction_bankcash' => EntryType::RESTRICTION_EXACTLY_ONE,
            ],
            [
                'label' => 'contra',
                'name' => 'Contra Voucher',
                'description' => 'Cash/Bank transfer entry',
                'base_type' => EntryType::BASE_TYPE_CONTRA,
                'numbering' => EntryType::NUMBERING_AUTO,
                'prefix' => 'CV',
                'suffix' => '',
                'zero_padding' => 4,
                'restriction_bankcash' => EntryType::RESTRICTION_AT_LEAST_ONE,
            ],
            [
                'label' => 'sales',
                'name' => 'Sales Voucher',
                'description' => 'Sales invoice entry',
                'base_type' => EntryType::BASE_TYPE_JOURNAL,
                'numbering' => EntryType::NUMBERING_AUTO,
                'prefix' => 'INV',
                'suffix' => '',
                'zero_padding' => 4,
                'restriction_bankcash' => EntryType::RESTRICTION_NONE,
            ],
            [
                'label' => 'purchase',
                'name' => 'Purchase Voucher',
                'description' => 'Purchase invoice entry',
                'base_type' => EntryType::BASE_TYPE_JOURNAL,
                'numbering' => EntryType::NUMBERING_AUTO,
                'prefix' => 'PINV',
                'suffix' => '',
                'zero_padding' => 4,
                'restriction_bankcash' => EntryType::RESTRICTION_NONE,
            ],
        ];

        foreach ($entryTypes as $entryType) {
            EntryType::create($entryType);
        }
    }
}
