<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Simple Modal Test</h1>
    
    <div class="mb-4">
        <strong>Modal State:</strong> 
        <span class="text-blue-600">{{ $showModal ? 'OPEN' : 'CLOSED' }}</span>
    </div>
    
    <button wire:click="toggleModal" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
        Toggle Modal
    </button>
    
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4">Test Modal</h3>
            <p class="mb-4">This is a simple test modal.</p>
            <button wire:click="toggleModal" 
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                Close
            </button>
        </div>
    </div>
    @endif
</div>
