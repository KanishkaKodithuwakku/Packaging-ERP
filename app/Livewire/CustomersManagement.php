<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\JobOrder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersManagement extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    public bool $isViewMode = false;
    public string $activeTab = 'general';
    public bool $showDeleteConfirmModal = false;
    public ?int $customerToDelete = null;

    // General Info Tab (no code field)
    public array $form = [
        'name' => '',
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

    // Credit Limit Tab
    public array $creditLimitForm = [
        'credit_limit_period' => '',
        'credit_limit_amount' => '',
    ];

    // Finance Tab
    public array $financeForm = [
        'account_receivable' => '',
        'sales_revenue' => '',
        'currency' => 'LKR',
        'tax' => '',
        'bank' => '',
    ];

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
            $customer = Customer::findOrFail($id);
            $this->form = [
                'name' => $customer->name,
                'phone' => $customer->phone ?? '',
                'email' => $customer->email ?? '',
                'address' => $customer->address ?? '',
                'website' => $customer->website ?? '',
                'notes' => $customer->notes ?? '',
                'status' => $customer->status ?? 'active',
            ];

            // Load contact info if exists
            $this->contactForm = [
                'first_name' => $customer->contact_first_name ?? '',
                'last_name' => $customer->contact_last_name ?? '',
                'email' => $customer->contact_email ?? '',
                'phone' => $customer->contact_phone ?? '',
                'mobile' => $customer->contact_mobile ?? '',
            ];

            // Load credit limit info
            $this->creditLimitForm = [
                'credit_limit_period' => $customer->credit_limit_period ?? '',
                'credit_limit_amount' => $customer->credit_limit_amount ?? '',
            ];

            // Load finance info
            $this->financeForm = [
                'account_receivable' => $customer->account_receivable ?? '',
                'sales_revenue' => $customer->sales_revenue ?? '',
                'currency' => $customer->currency ?? 'LKR',
                'tax' => $customer->tax ?? '',
                'bank' => $customer->bank ?? '',
            ];
        } else {
            $this->form = [
                'name' => '', 'phone' => '', 'email' => '',
                'address' => '', 'website' => '', 'notes' => '', 'status' => 'active'
            ];
            $this->contactForm = [
                'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'mobile' => ''
            ];
            $this->creditLimitForm = [
                'credit_limit_period' => '', 'credit_limit_amount' => ''
            ];
            $this->financeForm = [
                'account_receivable' => '', 'sales_revenue' => '', 'currency' => 'LKR', 'tax' => '', 'bank' => ''
            ];
        }
        $this->showModal = true;
    }

    public function viewCustomer($id)
    {
        $this->editingId = $id;
        $this->isViewMode = true;
        $this->activeTab = 'general';
        $customer = Customer::findOrFail($id);
        $this->form = [
            'name' => $customer->name,
            'phone' => $customer->phone ?? '',
            'email' => $customer->email ?? '',
            'address' => $customer->address ?? '',
            'website' => $customer->website ?? '',
            'notes' => $customer->notes ?? '',
            'status' => $customer->status ?? 'active',
        ];

        // Load contact info if exists
        $this->contactForm = [
            'first_name' => $customer->contact_first_name ?? '',
            'last_name' => $customer->contact_last_name ?? '',
            'email' => $customer->contact_email ?? '',
            'phone' => $customer->contact_phone ?? '',
            'mobile' => $customer->contact_mobile ?? '',
        ];

        // Load credit limit info
        $this->creditLimitForm = [
            'credit_limit_period' => $customer->credit_limit_period ?? '',
            'credit_limit_amount' => $customer->credit_limit_amount ?? '',
        ];

        // Load finance info
        $this->financeForm = [
            'account_receivable' => $customer->account_receivable ?? '',
            'sales_revenue' => $customer->sales_revenue ?? '',
            'currency' => $customer->currency ?? 'LKR',
            'tax' => $customer->tax ?? '',
            'bank' => $customer->bank ?? '',
        ];

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->isViewMode = false;
        $this->activeTab = 'general';
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    private function generateCustomerCode()
    {
        // Get all customers with CUST prefix codes
        $customers = Customer::whereNotNull('code')
            ->where('code', 'like', 'CUST%')
            ->get();

        $maxNumber = 0;
        
        foreach ($customers as $customer) {
            // Extract number from code (e.g., CUST001 -> 1)
            $codeNumber = (int) substr($customer->code, 4);
            if ($codeNumber > $maxNumber) {
                $maxNumber = $codeNumber;
            }
        }

        // Increment and format as CUST001, CUST002, etc.
        $nextNumber = $maxNumber + 1;
        return 'CUST' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
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
                        ? Rule::unique('customers', 'name')->ignore($excludeId)
                        : Rule::unique('customers', 'name'),
                ],
                'form.address' => [
                    'required',
                    'string',
                    $excludeId 
                        ? Rule::unique('customers', 'address')->ignore($excludeId)
                        : Rule::unique('customers', 'address'),
                ],
                'form.phone' => [
                    'required',
                    'string',
                    'max:50',
                    $excludeId 
                        ? Rule::unique('customers', 'phone')->ignore($excludeId)
                        : Rule::unique('customers', 'phone'),
                ],
                'form.email' => [
                    'required',
                    'email',
                    'max:255',
                    $excludeId 
                        ? Rule::unique('customers', 'email')->ignore($excludeId)
                        : Rule::unique('customers', 'email'),
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
                'creditLimitForm.credit_limit_period' => 'nullable|string|max:255',
                'creditLimitForm.credit_limit_amount' => 'nullable|numeric|min:0',
                'financeForm.account_receivable' => 'nullable|string|max:255',
                'financeForm.sales_revenue' => 'nullable|string|max:255',
                'financeForm.currency' => 'nullable|string|max:3',
                'financeForm.tax' => 'nullable|string|max:255',
                'financeForm.bank' => 'nullable|string|max:255',
            ]));
        } catch (ValidationException $e) {
            session()->flash('error', 'Please check some fields are empty or have duplicate values.');
            throw $e;
        }

        $customerData = array_merge($validated['form'], [
            'contact_first_name' => $validated['contactForm']['first_name'] ?? null,
            'contact_last_name' => $validated['contactForm']['last_name'] ?? null,
            'contact_email' => $validated['contactForm']['email'] ?? null,
            'contact_phone' => $validated['contactForm']['phone'] ?? null,
            'contact_mobile' => $validated['contactForm']['mobile'] ?? null,
            'credit_limit_period' => $validated['creditLimitForm']['credit_limit_period'] ?? null,
            'credit_limit_amount' => $validated['creditLimitForm']['credit_limit_amount'] ?? null,
            'account_receivable' => $validated['financeForm']['account_receivable'] ?? null,
            'sales_revenue' => $validated['financeForm']['sales_revenue'] ?? null,
            'currency' => $validated['financeForm']['currency'] ?? 'LKR',
            'tax' => $validated['financeForm']['tax'] ?? null,
            'bank' => $validated['financeForm']['bank'] ?? null,
        ]);

        if ($this->editingId) {
            $customer = Customer::where('id', $this->editingId)->first();
            $customer->update($customerData);
            session()->flash('success', 'Customer updated successfully');
        } else {
            // Auto-generate customer code
            $customerData['code'] = $this->generateCustomerCode();
            Customer::create($customerData);
            session()->flash('success', 'Customer added successfully');
        }

        $this->closeModal();
    }

    public function openDeleteConfirmModal($id)
    {
        // Check if customer is in use
        if ($this->isCustomerInUse($id)) {
            session()->flash('error', 'This customer is in use and cannot be deleted.');
            return;
        }
        
        $this->customerToDelete = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function closeDeleteConfirmModal()
    {
        $this->showDeleteConfirmModal = false;
        $this->customerToDelete = null;
    }

    public function delete($id)
    {
        $customer = Customer::find($id);
        
        if (!$customer) {
            session()->flash('error', 'Customer not found.');
            $this->closeDeleteConfirmModal();
            return;
        }

        // Check if customer is in use
        if ($this->isCustomerInUse($id)) {
            session()->flash('error', 'This customer is in use and cannot be deleted.');
            $this->closeDeleteConfirmModal();
            return;
        }

        $customer->delete();
        session()->flash('success', 'Customer deleted');

        // Close modal
        $this->closeDeleteConfirmModal();
    }

    private function isCustomerInUse($customerId): bool
    {
        // Check if customer is used in job orders
        if (JobOrder::where('customer_id', $customerId)->exists()) {
            return true;
        }

        // Check if customer is used in customer orders
        if (CustomerOrder::where('customer_id', $customerId)->exists()) {
            return true;
        }

        return false;
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        // Add in-use status to each customer
        $customers->getCollection()->transform(function ($customer) {
            $customer->is_in_use = $this->isCustomerInUse($customer->id);
            return $customer;
        });

        return view('livewire.customers.management', [
            'customers' => $customers,
        ]);
    }
}
