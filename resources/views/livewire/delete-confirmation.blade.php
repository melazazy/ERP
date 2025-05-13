<div>
@if ($show)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl">
            <h3 class="text-lg font-semibold mb-4">Confirm Deletion</h3>
            <p class="mb-4">{{ $message }}</p>
            <div class="flex justify-end gap-2">
                <button wire:click="cancel" 
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </button>
                <button wire:click="delete" 
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    {{ $type === 'all' ? 'Remove All' : 'Remove' }}
                </button>
            </div>
        </div>
    </div>
@endif
</div>