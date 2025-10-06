<?php

namespace App\Repositories;

use App\Models\Quotation;
use Illuminate\Database\Eloquent\Collection;

class QuotationRepository
{
    public function getAll(): Collection
    {
        return Quotation::with('customer')->orderBy('created_at', 'desc')->get();
    }

    public function getById(int $id): ?Quotation
    {
        return Quotation::with('customer')->find($id);
    }

    public function create(array $data): Quotation
    {
        return Quotation::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $quotation = $this->getById($id);
        if (!$quotation) {
            return false;
        }
        return $quotation->update($data);
    }

    public function delete(int $id): bool
    {
        $quotation = $this->getById($id);
        if (!$quotation) {
            return false;
        }
        return $quotation->delete();
    }

    public function getByStatus(string $status): Collection
    {
        return Quotation::with('customer')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAcceptedQuotations(): Collection
    {
        return $this->getByStatus('accepted');
    }

    public function getPendingQuotations(): Collection
    {
        return Quotation::with('customer')
            ->whereIn('status', ['draft', 'sent'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getExpiredQuotations(): Collection
    {
        return Quotation::with('customer')
            ->where('valid_until', '<', now())
            ->where('status', 'sent')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function generateQuotationNumber(): string
    {
        $lastQuotation = Quotation::orderByDesc('id')->first();
        $lastNumber = $lastQuotation ? (int) substr($lastQuotation->qt_no, 3) : 0;
        return 'QT-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}
