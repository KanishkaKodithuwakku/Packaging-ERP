<div>
    <h1>Test GRN Component</h1>
    <p>GRN: {{ $grn->grn_no }}</p>
    <p>Status: {{ $grn->status }}</p>
    <p>Show Modal: {{ $showModal ? 'true' : 'false' }}</p>
    
    <button wire:click="showModal" class="bg-blue-500 text-white px-4 py-2 rounded">
        Show Modal
    </button>
    
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="bg-white p-8 m-8 rounded">
            <h2>Modal is visible!</h2>
            <button wire:click="$set('showModal', false)" class="bg-red-500 text-white px-4 py-2 rounded">
                Close
            </button>
        </div>
    </div>
    @endif
</div>
