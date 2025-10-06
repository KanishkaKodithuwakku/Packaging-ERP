<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\CustomerOrder;
use App\Repositories\QuotationRepository;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    protected QuotationRepository $quotationRepository;

    public function __construct(QuotationRepository $quotationRepository)
    {
        $this->quotationRepository = $quotationRepository;
    }

    public function createQuotation(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            // Generate quotation number
            $data['qt_no'] = $this->quotationRepository->generateQuotationNumber();
            
            // Set default values
            $data['status'] = $data['status'] ?? 'draft';
            $data['valid_until'] = $data['valid_until'] ?? now()->addDays(30);
            $data['profit_margin'] = $data['profit_margin'] ?? 20;

            // Create quotation
            $quotation = $this->quotationRepository->create($data);

            // Calculate costs
            $quotation->calculateCosts();
            $quotation->save();

            return $quotation;
        });
    }

    public function updateQuotation(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $quotation = $this->quotationRepository->getById($id);
            if (!$quotation) {
                return false;
            }

            $updated = $this->quotationRepository->update($id, $data);
            
            if ($updated) {
                // Recalculate costs if relevant fields changed
                $quotation->refresh();
                $quotation->calculateCosts();
                $quotation->save();
            }

            return $updated;
        });
    }

    public function sendQuotation(int $id): bool
    {
        $quotation = $this->quotationRepository->getById($id);
        if (!$quotation || $quotation->status !== 'draft') {
            return false;
        }

        return $quotation->update(['status' => 'sent']);
    }

    public function acceptQuotation(int $id): ?CustomerOrder
    {
        return DB::transaction(function () use ($id) {
            $quotation = $this->quotationRepository->getById($id);
            if (!$quotation || !$quotation->canBeAccepted()) {
                return null;
            }

            // Accept the quotation
            $quotation->accept();

            // Create customer order from quotation
            $customerOrder = $this->createCustomerOrderFromQuotation($quotation);

            return $customerOrder;
        });
    }

    public function rejectQuotation(int $id): bool
    {
        $quotation = $this->quotationRepository->getById($id);
        if (!$quotation) {
            return false;
        }

        return $quotation->reject();
    }

    public function expireQuotations(): int
    {
        $expiredQuotations = $this->quotationRepository->getExpiredQuotations();
        $count = 0;

        foreach ($expiredQuotations as $quotation) {
            if ($quotation->update(['status' => 'expired'])) {
                $count++;
            }
        }

        return $count;
    }

    private function createCustomerOrderFromQuotation(Quotation $quotation): CustomerOrder
    {
        // Generate customer order number
        $lastOrder = CustomerOrder::orderByDesc('id')->first();
        $lastNumber = $lastOrder ? (int) substr($lastOrder->order_no, 3) : 0;
        $orderNo = 'CO-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return CustomerOrder::create([
            'customer_id' => $quotation->customer_id,
            'quotation_id' => $quotation->id,
            'order_no' => $orderNo,
            'item_desc' => $quotation->item_desc,
            'size_mm' => $quotation->size_mm,
            'ply' => $quotation->ply,
            'qty_ordered' => $quotation->qty_requested,
            'status' => 'pending',
        ]);
    }

    public function getQuotationStats(): array
    {
        return [
            'total' => Quotation::count(),
            'draft' => Quotation::where('status', 'draft')->count(),
            'sent' => Quotation::where('status', 'sent')->count(),
            'accepted' => Quotation::where('status', 'accepted')->count(),
            'rejected' => Quotation::where('status', 'rejected')->count(),
            'expired' => Quotation::where('status', 'expired')->count(),
        ];
    }
}
