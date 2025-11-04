<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersManagement extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    public array $form = [
        'code' => '',
        'name' => '',
        'address' => '',
        'contact_person' => '',
        'phone' => '',
        'email' => '',
        'currency' => 'LKR',
        'is_active' => true,
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null)
    {
        $this->editingId = $id;
        if ($id) {
            $customer = Customer::findOrFail($id);
            $this->form = [
                'code' => $customer->code ?? '',
                'name' => $customer->name,
                'address' => $customer->address ?? '',
                'contact_person' => $customer->contact_person ?? '',
                'phone' => $customer->phone ?? '',
                'email' => $customer->email ?? '',
                'currency' => $customer->currency ?? 'LKR',
                'is_active' => $customer->is_active ?? true,
            ];
        } else {
            $this->form = [
                'code' => '',
                'name' => '',
                'address' => '',
                'contact_person' => '',
                'phone' => '',
                'email' => '',
                'currency' => 'LKR',
                'is_active' => true,
            ];
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->form = [
            'code' => '',
            'name' => '',
            'address' => '',
            'contact_person' => '',
            'phone' => '',
            'email' => '',
            'currency' => 'LKR',
            'is_active' => true,
        ];
        $this->editingId = null;
    }

    public function save()
    {
        $rules = [
            'form.name' => 'required|string|max:255',
            'form.email' => 'required|email|max:255|unique:customers,email',
            'form.phone' => 'nullable|string|max:50',
            'form.address' => 'nullable|string',
        ];

        // Add code validation if code field exists
        if (isset($this->form['code'])) {
            if ($this->editingId) {
                $rules['form.code'] = 'nullable|string|max:50|unique:customers,code,' . $this->editingId;
            } else {
                $rules['form.code'] = 'nullable|string|max:50|unique:customers,code';
            }
        }

        // Add email unique validation for updates
        if ($this->editingId) {
            $rules['form.email'] = 'required|email|max:255|unique:customers,email,' . $this->editingId;
        }

        $validated = $this->validate($rules)['form'];

        // Ensure is_active is boolean
        if (isset($validated['is_active'])) {
            $validated['is_active'] = (bool) $validated['is_active'];
        }

        if ($this->editingId) {
            Customer::where('id', $this->editingId)->update($validated);
            session()->flash('success', 'Customer updated successfully!');
        } else {
            Customer::create($validated);
            session()->flash('success', 'Customer created successfully!');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Customer::where('id', $id)->delete();
        session()->flash('success', 'Customer deleted successfully!');
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.customers.management', [
            'customers' => $customers,
        ]);
    }
}

