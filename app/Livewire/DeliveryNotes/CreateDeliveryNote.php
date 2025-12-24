<?php

namespace App\Livewire\DeliveryNotes;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\JobOrder;
use App\Services\DeliveryService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class CreateDeliveryNote extends Component
{
    protected $layout = 'components.layouts.app';

    public $form = [
        'job_order_id' => '',
        'dispatch_date' => '',
        'delivery_address' => '',
        'notes' => '',
    ];

    public $selectedJobOrder = null;
    public $availableFg = [];
    public $dispatchQuantities = [];

    protected $rules = [
        'form.job_order_id' => 'required|exists:job_orders,id',
        'form.dispatch_date' => 'required|date',
        'form.delivery_address' => 'nullable|string',
        'form.notes' => 'nullable|string',
    ];

    protected $messages = [
        'form.job_order_id.required' => 'Please select a job order.',
    ];

    public function mount()
    {
        $this->form['dispatch_date'] = now()->format('Y-m-d');
    }

    public function updatedFormJobOrderId()
    {
        if ($this->form['job_order_id']) {
            $this->selectedJobOrder = JobOrder::with(['boxes', 'dividers', 'customer'])->find($this->form['job_order_id']);
            $this->loadAvailableFg();
            
            // Auto-populate delivery address from customer
            if ($this->selectedJobOrder && $this->selectedJobOrder->customer) {
                $this->form['delivery_address'] = $this->selectedJobOrder->customer_address ?? '';
            }
        } else {
            $this->selectedJobOrder = null;
            $this->availableFg = [];
            $this->form['delivery_address'] = '';
        }
    }

    public function loadAvailableFg()
    {
        if (!$this->form['job_order_id']) {
            $this->availableFg = [];
            return;
        }

        $deliveryService = app(DeliveryService::class);
        $this->availableFg = $deliveryService->getAvailableFgForJobOrder($this->form['job_order_id']);
    }

    public function save()
    {
        $this->validate();

        if (empty($this->availableFg)) {
            session()->flash('error', 'No Finished Goods available for this job order.');
            return;
        }

        // Validate dispatch quantities
        foreach ($this->availableFg as $index => $fg) {
            $qty = $this->dispatchQuantities[$index] ?? 0;
            if ($qty <= 0) {
                session()->flash('error', 'Please enter quantities for at least one item.');
                return;
            }
            if ($qty > $fg['available_qty']) {
                session()->flash('error', 'Quantity cannot exceed available quantity for ' . $fg['description']);
                return;
            }
        }

        try {
            // Create delivery note
            $dn = DeliveryNote::create([
                'dn_number' => DeliveryNote::generateDnNumber(),
                'job_order_id' => $this->form['job_order_id'],
                'dispatch_date' => $this->form['dispatch_date'],
                'status' => 'draft',
                'delivery_address' => $this->form['delivery_address'],
                'notes' => $this->form['notes'],
            ]);

            // Create delivery note items with user-specified quantities
            foreach ($this->availableFg as $index => $fg) {
                $qty = $this->dispatchQuantities[$index] ?? $fg['available_qty'];
                
                DeliveryNoteItem::create([
                    'delivery_note_id' => $dn->id,
                    'item_type' => $fg['item_type'],
                    'item_id' => $fg['item_id'],
                    'description' => $fg['description'],
                    'material_code' => $fg['material_code'],
                    'quantity' => $qty,
                    'dispatched_qty' => 0,
                    'remaining_qty' => $qty,
                    'status' => 'pending',
                ]);
            }

            session()->flash('success', 'Delivery note created successfully!');
            return $this->redirect(route('delivery-note-detail', $dn->id), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error creating delivery note: ' . $e->getMessage());
            session()->flash('error', 'Error creating delivery note: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.delivery-notes.create-delivery-note', [
            'jobOrders' => JobOrder::with('customer')
                ->where('status', 'confirmed')
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }
}
