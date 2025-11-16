<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\SupplierReelSize;
use Livewire\Component;
use Livewire\WithPagination;

class SuppliersManagement extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    public bool $isViewMode = false;
    public string $activeTab = 'general';
    public bool $showDeleteConfirmModal = false;
    public ?int $supplierToDelete = null;

    // General Info Tab
    public array $form = [
        'name' => '',
        'code' => '',
        'address' => '',
        'phone' => '',
        'email' => '',
        'website' => '',
        'notes' => '',
        'status' => 'active',
    ];

    // Primary Contact Tab
    public array $contactForm = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'mobile' => '',
    ];

    // Finance Tab (view only, no database)
    public array $financeForm = [
        'payable_account' => '',
        'tax' => '',
        'bank' => '',
    ];

    // Reel Sizes
    public array $reelSizes = [];
    public array $reelForm = [
        'reel_size' => '',
    ];
    public ?int $editingReelId = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null)
    {
        $this->editingId = $id;
        $this->isViewMode = false;
        $this->activeTab = 'general';
        if ($id) {
            $supplier = Supplier::with('reelSizes')->findOrFail($id);
            $this->form = [
                'name' => $supplier->name,
                'code' => $supplier->code,
                'phone' => $supplier->phone ?? '',
                'email' => $supplier->email ?? '',
                'address' => $supplier->address ?? '',
                'website' => $supplier->website ?? '',
                'notes' => $supplier->notes ?? '',
                'status' => $supplier->status ?? 'active',
            ];

            // Load contact info if exists
            $this->contactForm = [
                'first_name' => $supplier->contact_first_name ?? '',
                'last_name' => $supplier->contact_last_name ?? '',
                'email' => $supplier->contact_email ?? '',
                'phone' => $supplier->contact_phone ?? '',
                'mobile' => $supplier->contact_mobile ?? '',
            ];

            // Load reel sizes
            $this->reelSizes = $supplier->reelSizes->map(function($reel) {
                return [
                    'id' => $reel->id,
                    'reel_size' => $reel->reel_size,
                ];
            })->toArray();
        } else {
            $this->form = [
                'name' => '', 'code' => '', 'phone' => '', 'email' => '',
                'address' => '', 'website' => '', 'notes' => '', 'status' => 'active'
            ];
            $this->contactForm = [
                'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'mobile' => ''
            ];
            $this->reelSizes = [];
        }
        $this->resetReelForm();
        $this->showModal = true;
    }

    public function viewSupplier($id)
    {
        $this->editingId = $id;
        $this->isViewMode = true;
        $this->activeTab = 'general';
        $supplier = Supplier::with('reelSizes')->findOrFail($id);
        $this->form = [
            'name' => $supplier->name,
            'code' => $supplier->code,
            'phone' => $supplier->phone ?? '',
            'email' => $supplier->email ?? '',
            'address' => $supplier->address ?? '',
            'website' => $supplier->website ?? '',
            'notes' => $supplier->notes ?? '',
            'status' => $supplier->status ?? 'active',
        ];

        // Load contact info if exists
        $this->contactForm = [
            'first_name' => $supplier->contact_first_name ?? '',
            'last_name' => $supplier->contact_last_name ?? '',
            'email' => $supplier->contact_email ?? '',
            'phone' => $supplier->contact_phone ?? '',
            'mobile' => $supplier->contact_mobile ?? '',
        ];

        // Load reel sizes
        $this->reelSizes = $supplier->reelSizes->map(function($reel) {
            return [
                'id' => $reel->id,
                'reel_size' => $reel->reel_size,
            ];
        })->toArray();

        $this->resetReelForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->isViewMode = false;
        $this->activeTab = 'general';
        $this->resetReelForm();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function resetReelForm()
    {
        $this->reelForm = ['reel_size' => ''];
        $this->editingReelId = null;
    }

    public function addReelSize()
    {
        $this->validate([
            'reelForm.reel_size' => 'required|numeric|min:0.01',
        ]);

        $reelSize = (float) $this->reelForm['reel_size'];

        // Check for duplicates
        foreach ($this->reelSizes as $reel) {
            if (abs($reel['reel_size'] - $reelSize) < 0.01) {
                session()->flash('error', 'This reel size already exists.');
                return;
            }
        }

        if ($this->editingReelId) {
            // Update existing
            foreach ($this->reelSizes as &$reel) {
                if ($reel['id'] == $this->editingReelId) {
                    $reel['reel_size'] = $reelSize;
                    break;
                }
            }
        } else {
            // Add new
            $this->reelSizes[] = [
                'id' => 'temp_' . time() . '_' . count($this->reelSizes),
                'reel_size' => $reelSize,
            ];
        }

        $this->resetReelForm();
        session()->flash('success', 'Reel size ' . ($this->editingReelId ? 'updated' : 'added') . ' successfully.');
    }

    public function editReelSize($index)
    {
        $reel = $this->reelSizes[$index];
        $this->reelForm = ['reel_size' => (string) $reel['reel_size']];
        $this->editingReelId = $reel['id'];
    }

    public function deleteReelSize($index)
    {
        unset($this->reelSizes[$index]);
        $this->reelSizes = array_values($this->reelSizes);
        $this->resetReelForm();
        session()->flash('success', 'Reel size deleted.');
    }

    public function save()
    {
        $validated = $this->validate([
            'form.name' => 'required|string|max:255',
            'form.code' => 'nullable|string|max:50',
            'form.phone' => 'nullable|string|max:50',
            'form.email' => 'nullable|email|max:255',
            'form.website' => 'nullable|url|max:255',
            'form.address' => 'nullable|string',
            'form.notes' => 'nullable|string',
            'form.status' => 'required|string',
            'contactForm.first_name' => 'nullable|string|max:255',
            'contactForm.last_name' => 'nullable|string|max:255',
            'contactForm.email' => 'nullable|email|max:255',
            'contactForm.phone' => 'nullable|string|max:50',
            'contactForm.mobile' => 'nullable|string|max:50',
        ]);

        $supplierData = array_merge($validated['form'], [
            'contact_first_name' => $validated['contactForm']['first_name'] ?? null,
            'contact_last_name' => $validated['contactForm']['last_name'] ?? null,
            'contact_email' => $validated['contactForm']['email'] ?? null,
            'contact_phone' => $validated['contactForm']['phone'] ?? null,
            'contact_mobile' => $validated['contactForm']['mobile'] ?? null,
        ]);

        if ($this->editingId) {
            $supplier = Supplier::where('id', $this->editingId)->first();
            $supplier->update($supplierData);

            // Update reel sizes
            $this->saveReelSizes($supplier->id);

            session()->flash('success', 'Supplier updated successfully');
        } else {
            $supplier = Supplier::create($supplierData);

            // Save reel sizes
            $this->saveReelSizes($supplier->id);

            session()->flash('success', 'Supplier added successfully');
        }

        $this->closeModal();
    }

    private function saveReelSizes($supplierId)
    {
        // Get existing reel sizes from database
        $existingReels = SupplierReelSize::where('supplier_id', $supplierId)->get()->keyBy('id');

        // Track which reel sizes we've processed
        $processedIds = [];

        // Update or create reel sizes
        foreach ($this->reelSizes as $reel) {
            if (strpos($reel['id'], 'temp_') === 0) {
                // New reel size
                SupplierReelSize::create([
                    'supplier_id' => $supplierId,
                    'reel_size' => $reel['reel_size'],
                ]);
            } else {
                // Existing reel size - update if needed
                $reelId = (int) $reel['id'];
                if ($existingReels->has($reelId)) {
                    $existingReel = $existingReels->get($reelId);
                    if ($existingReel->reel_size != $reel['reel_size']) {
                        $existingReel->update(['reel_size' => $reel['reel_size']]);
                    }
                    $processedIds[] = $reelId;
                }
            }
        }

        // Delete reel sizes that were removed
        $existingReels->each(function($reel) use ($processedIds) {
            if (!in_array($reel->id, $processedIds)) {
                $reel->delete();
            }
        });
    }

    public function openDeleteConfirmModal($id)
    {
        $this->supplierToDelete = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function closeDeleteConfirmModal()
    {
        $this->showDeleteConfirmModal = false;
        $this->supplierToDelete = null;
    }

    public function delete($id)
    {
        Supplier::where('id', $id)->delete();
        session()->flash('success', 'Supplier deleted');

        // Close modal
        $this->closeDeleteConfirmModal();
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.suppliers.management', [
            'suppliers' => $suppliers,
        ]);
    }
}


