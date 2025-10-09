<!DOCTYPE html>
<html>
<head>
    <title>Modal Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body>
    <div class="p-8">
        <h1 class="text-2xl font-bold mb-4">Simple Modal Test</h1>
        
        <button wire:click="openModal" class="bg-blue-500 text-white px-4 py-2 rounded">
            Open Modal
        </button>
        
        @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg">
                <h2 class="text-xl font-bold mb-4">Modal is Open!</h2>
                <p class="mb-4">This modal is working correctly.</p>
                <button wire:click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded">
                    Close Modal
                </button>
            </div>
        </div>
        @endif
        
        @if (session()->has('message'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mt-4">
                {{ session('message') }}
            </div>
        @endif
    </div>
    
    @livewireScripts
</body>
</html>
