<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Modal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Modal Debug Test</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Livewire Modal Test -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Livewire Modal Test</h2>
                
                <div class="space-y-4">
                    <div class="p-3 bg-blue-50 rounded">
                        <strong>Livewire State:</strong> 
                        <span class="text-blue-600 font-bold">{{ $showModal ? 'OPEN' : 'CLOSED' }}</span>
                    </div>
                    
                    <button wire:click="toggleModal" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                        Toggle Livewire Modal
                    </button>
                    
                    <div class="text-xs text-gray-500">
                        <p>This uses Livewire wire:click</p>
                    </div>
                </div>
            </div>
            
            <!-- JavaScript Modal Test -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">JavaScript Modal Test</h2>
                
                <div class="space-y-4">
                    <div class="p-3 bg-green-50 rounded">
                        <strong>JS State:</strong> 
                        <span class="text-green-600 font-bold" id="js-state">CLOSED</span>
                    </div>
                    
                    <button onclick="toggleJsModal()" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                        Toggle JS Modal
                    </button>
                    
                    <div class="text-xs text-gray-500">
                        <p>This uses pure JavaScript</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Livewire Modal -->
        @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-8 rounded-lg max-w-md w-full mx-4 shadow-xl">
                <h3 class="text-xl font-semibold mb-4 text-blue-600">🎉 Livewire Modal Works!</h3>
                <p class="mb-6 text-gray-600">This modal was opened using Livewire.</p>
                <button wire:click="toggleModal" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    Close Livewire Modal
                </button>
            </div>
        </div>
        @endif
        
        <!-- JavaScript Modal -->
        <div id="js-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white p-8 rounded-lg max-w-md w-full mx-4 shadow-xl">
                <h3 class="text-xl font-semibold mb-4 text-green-600">🎉 JavaScript Modal Works!</h3>
                <p class="mb-6 text-gray-600">This modal was opened using pure JavaScript.</p>
                <button onclick="toggleJsModal()" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    Close JS Modal
                </button>
            </div>
        </div>
        
        <!-- Debug Info -->
        <div class="mt-8 bg-yellow-50 p-4 rounded-lg">
            <h3 class="font-semibold mb-2">Debug Information:</h3>
            <ul class="text-sm space-y-1">
                <li><strong>Livewire Loaded:</strong> <span id="livewire-status">Checking...</span></li>
                <li><strong>Alpine.js Loaded:</strong> <span id="alpine-status">Checking...</span></li>
                <li><strong>Console Errors:</strong> Check browser console for errors</li>
            </ul>
        </div>
    </div>
    
    
    <script>
        // JavaScript Modal Functions
        function toggleJsModal() {
            const modal = document.getElementById('js-modal');
            const state = document.getElementById('js-state');
            
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                state.textContent = 'OPEN';
                state.className = 'text-green-600 font-bold';
            } else {
                modal.classList.add('hidden');
                state.textContent = 'CLOSED';
                state.className = 'text-green-600 font-bold';
            }
        }
        
        // Debug Information
        document.addEventListener('DOMContentLoaded', function() {
            // Check Livewire
            const livewireStatus = document.getElementById('livewire-status');
            if (window.Livewire) {
                livewireStatus.textContent = '✅ Yes';
                livewireStatus.className = 'text-green-600';
            } else {
                livewireStatus.textContent = '❌ No';
                livewireStatus.className = 'text-red-600';
            }
            
            // Check Alpine.js
            const alpineStatus = document.getElementById('alpine-status');
            if (window.Alpine) {
                alpineStatus.textContent = '✅ Yes';
                alpineStatus.className = 'text-green-600';
            } else {
                alpineStatus.textContent = '❌ No';
                alpineStatus.className = 'text-red-600';
            }
            
            // Log any errors
            window.addEventListener('error', function(e) {
                console.error('JavaScript Error:', e.error);
            });
        });
    </script>
</body>
</html>

