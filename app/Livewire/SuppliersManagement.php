<?php

namespace App\Livewire;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SuppliersManagement extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    public array $form = [
        'name' => '',
        'code' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'status' => 'active',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null)
    {
        $this->editingId = $id;
        if ($id) {
            $supplier = Supplier::findOrFail($id);
            $this->form = [
                'name' => $supplier->name,
                'code' => $supplier->code,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
                'status' => $supplier->status ?? 'active',
            ];
        } else {
            $this->form = [
                'name' => '', 'code' => '', 'phone' => '', 'email' => '', 'address' => '', 'status' => 'active'
            ];
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $validated = $this->validate([
            'form.name' => 'required|string|max:255',
            'form.code' => 'required|string|max:50',
            'form.phone' => 'nullable|string|max:50',
            'form.email' => 'nullable|email|max:255',
            'form.address' => 'nullable|string',
            'form.status' => 'required|string',
        ])['form'];

        if ($this->editingId) {
            Supplier::where('id', $this->editingId)->update($validated);
            session()->flash('success', 'Supplier updated');
        } else {
            Supplier::create($validated);
            session()->flash('success', 'Supplier created');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Supplier::where('id', $id)->delete();
        session()->flash('success', 'Supplier deleted');
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


