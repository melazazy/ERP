<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="text-lg font-semibold">
                                {{ __('Welcome,') }} {{ auth()->user()->name }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ auth()->user()->email }}
                            </div>
                        </div>
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-medium">
                            {{ __('Role:') }} {{ auth()->user()->role->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
            <livewire:dashboard-reports />
        </div>
    </div>
</x-app-layout>