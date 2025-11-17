<?php

namespace App\Livewire;

use App\Models\JobOrder;
use App\Models\JobOrderBox;
use App\Models\JobOrderDivider;
use App\Models\PurchaseOrder;
use App\Models\ProductionOrder;
use App\Models\Supplier;
use App\Models\SupplierReelSize;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

    // Finance Tab
    public array $financeForm = [
        'payable_account' => '',
        'tax' => '',
        'bank' => '',
        'currency' => 'LKR',
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

            // Load finance info
            $this->financeForm = [
                'payable_account' => '',
                'tax' => '',
                'bank' => '',
                'currency' => $supplier->currency ?? 'LKR',
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

        // Load finance info
        $this->financeForm = [
            'payable_account' => '',
            'tax' => '',
            'bank' => '',
            'currency' => $supplier->currency ?? 'LKR',
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
        try {
            // Build unique validation rules for general info fields
            $excludeId = $this->editingId;

            $uniqueRules = [
                'form.name' => [
                    'required',
                    'string',
                    'max:255',
                    $excludeId
                        ? Rule::unique('suppliers', 'name')->ignore($excludeId)
                        : Rule::unique('suppliers', 'name'),
                ],
                'form.code' => [
                    'required',
                    'string',
                    'max:50',
                    $excludeId
                        ? Rule::unique('suppliers', 'code')->ignore($excludeId)
                        : Rule::unique('suppliers', 'code'),
                ],
                'form.address' => [
                    'required',
                    'string',
                    $excludeId
                        ? Rule::unique('suppliers', 'address')->ignore($excludeId)
                        : Rule::unique('suppliers', 'address'),
                ],
                'form.phone' => [
                    'required',
                    'string',
                    'max:50',
                    $excludeId
                        ? Rule::unique('suppliers', 'phone')->ignore($excludeId)
                        : Rule::unique('suppliers', 'phone'),
                ],
                'form.email' => [
                    'required',
                    'email',
                    'max:255',
                    $excludeId
                        ? Rule::unique('suppliers', 'email')->ignore($excludeId)
                        : Rule::unique('suppliers', 'email'),
                ],
            ];

            $validated = $this->validate(array_merge($uniqueRules, [
                'form.website' => 'nullable|url|max:255',
                'form.notes' => 'nullable|string',
                'form.status' => 'required|string',
                'contactForm.first_name' => 'nullable|string|max:255',
                'contactForm.last_name' => 'nullable|string|max:255',
                'contactForm.email' => 'nullable|email|max:255',
                'contactForm.phone' => 'nullable|string|max:50',
                'contactForm.mobile' => 'nullable|string|max:50',
                'financeForm.currency' => 'nullable|string|max:3',
            ]));
        } catch (ValidationException $e) {
            session()->flash('error', 'Please check some fields are empty or have duplicate values.');
            throw $e;
        }

        // Convert empty strings to null for nullable fields
        $formData = $validated['form'];
        if (isset($formData['website']) && $formData['website'] === '') {
            $formData['website'] = null;
        }

        $supplierData = array_merge($formData, [
            'contact_first_name' => !empty($validated['contactForm']['first_name']) ? $validated['contactForm']['first_name'] : null,
            'contact_last_name' => !empty($validated['contactForm']['last_name']) ? $validated['contactForm']['last_name'] : null,
            'contact_email' => !empty($validated['contactForm']['email']) ? $validated['contactForm']['email'] : null,
            'contact_phone' => !empty($validated['contactForm']['phone']) ? $validated['contactForm']['phone'] : null,
            'contact_mobile' => !empty($validated['contactForm']['mobile']) ? $validated['contactForm']['mobile'] : null,
            'currency' => $validated['financeForm']['currency'] ?? 'LKR',
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
        // Check if supplier is in use
        if ($this->isSupplierInUse($id)) {
            session()->flash('error', 'This supplier is in use and cannot be deleted.');
            return;
        }

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
        $supplier = Supplier::find($id);

        if (!$supplier) {
            session()->flash('error', 'Supplier not found.');
            $this->closeDeleteConfirmModal();
            return;
        }

        // Check if supplier is in use
        if ($this->isSupplierInUse($id)) {
            session()->flash('error', 'This supplier is in use and cannot be deleted.');
            $this->closeDeleteConfirmModal();
            return;
        }

        $supplier->delete();
        session()->flash('success', 'Supplier deleted');

        // Close modal
        $this->closeDeleteConfirmModal();
    }

    private function isSupplierInUse($supplierId): bool
    {
        // Check if supplier is used in job orders
        if (JobOrder::where('supplier_id', $supplierId)->exists()) {
            return true;
        }

        // Check if supplier is used in purchase orders
        if (PurchaseOrder::where('supplier_id', $supplierId)->exists()) {
            return true;
        }

        // Check if supplier is used in production orders
        if (ProductionOrder::where('supplier_id', $supplierId)->exists()) {
            return true;
        }

        // Check if supplier is used in job order boxes
        if (JobOrderBox::where('supplier_id', $supplierId)->exists()) {
            return true;
        }

        // Check if supplier is used in job order dividers
        if (JobOrderDivider::where('supplier_id', $supplierId)->exists()) {
            return true;
        }

        return false;
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

        // Add in-use status to each supplier
        $suppliers->getCollection()->transform(function ($supplier) {
            $supplier->is_in_use = $this->isSupplierInUse($supplier->id);
            return $supplier;
        });

        return view('livewire.suppliers.management', [
            'suppliers' => $suppliers,
        ]);
    }
}


