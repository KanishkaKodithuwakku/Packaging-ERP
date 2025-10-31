<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\SupplierReelSize;
use Livewire\Component;

class SupplierDetail extends Component
{
    protected $layout = 'components.layouts.app';

    public int $supplierId;
    public ?Supplier $supplier = null;

    public bool $showReelModal = false;
    public ?int $editingReelId = null;
    public array $reelForm = [
        'ply' => '3',
        'flute' => 'B',
        'size_mm' => '',
        'is_default' => false,
        'notes' => '',
    ];

    public function mount(int $id)
    {
        $this->supplierId = $id;
        $this->loadSupplier();
    }

    public function loadSupplier(): void
    {
        $this->supplier = Supplier::with('reelSizes')->findOrFail($this->supplierId);
    }

    public function openReelModal(?int $id = null)
    {
        $this->editingReelId = $id;
        if ($id) {
            $reel = SupplierReelSize::findOrFail($id);
            $this->reelForm = [
                'ply' => (string)($reel->ply),
                'flute' => (string)($reel->flute),
                'size_mm' => (string)$reel->size_mm,
                'is_default' => (bool)$reel->is_default,
                'notes' => (string)($reel->notes ?? ''),
            ];
        } else {
            $this->reelForm = ['ply' => '3','flute' => 'B','size_mm' => '', 'is_default' => false, 'notes' => ''];
        }
        $this->showReelModal = true;
    }

    public function closeReelModal()
    {
        $this->showReelModal = false;
    }

    public function saveReel()
    {
        $validated = $this->validate([
            'reelForm.ply' => 'required|in:3,5,7',
            'reelForm.flute' => 'required|in:A,B,C,E,BC,BE',
            'reelForm.size_mm' => 'required|numeric|min:0.01',
            'reelForm.is_default' => 'boolean',
            'reelForm.notes' => 'nullable|string',
        ])['reelForm'];

        $validated['supplier_id'] = $this->supplierId;

        if ($this->editingReelId) {
            SupplierReelSize::where('id', $this->editingReelId)->update($validated);
            session()->flash('success', 'Reel size updated');
        } else {
            SupplierReelSize::create($validated);
            session()->flash('success', 'Reel size added');
        }

        $this->closeReelModal();
        $this->loadSupplier();
    }

    public function deleteReel($id)
    {
        SupplierReelSize::where('id', $id)->delete();
        $this->loadSupplier();
    }

    public function render()
    {
        return view('livewire.suppliers.detail', [
            'supplier' => $this->supplier,
        ]);
    }
}


