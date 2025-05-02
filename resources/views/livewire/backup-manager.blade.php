<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Backup Manager</h1>

    @if($message)
        <div class="mb-4 p-4 rounded {{ $messageType === 'error' ? 'bg-red-100 text-red-700' : ($messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700') }}">
            {{ $message }}
        </div>
    @endif

    <!-- Create Backup Button -->
    <div class="mb-6">
        <button wire:click="createBackup" 
                wire:loading.attr="disabled"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50">
            <span wire:loading wire:target="createBackup">Creating...</span>
            <span wire:loading.remove>Create New Backup</span>
        </button>
    </div>

    <!-- Backups List -->
    <div class="bg-white rounded-lg shadow-md">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Backup Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($backups as $backup)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $backup['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $backup['last_modified'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap space-x-2">
                            <button wire:click="downloadBackup('{{ $backup['path'] }}')" 
                                    class="text-blue-600 hover:text-blue-900">
                                Download
                            </button>
                            <button wire:click="restoreBackup('{{ $backup['path'] }}')"
                                    onclick="confirm('Are you sure you want to restore this backup? This will overwrite your current database.') || event.stopImmediatePropagation()"
                                    class="text-yellow-600 hover:text-yellow-900">
                                Restore
                            </button>
                            <button wire:click="deleteBackup('{{ $backup['path'] }}')"
                                    onclick="confirm('Are you sure you want to delete this backup?') || event.stopImmediatePropagation()"
                                    class="text-red-600 hover:text-red-900">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No backups found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>