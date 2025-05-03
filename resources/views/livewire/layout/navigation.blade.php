<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>
                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex sm:items-center">
                    <!-- Inventory Operations -->
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ __('Inventory') }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('receiving')" wire:navigate>
                                {{ __('Receiving') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('requisition')" wire:navigate>
                                {{ __('Requisition') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('transfer')" wire:navigate>
                                {{ __('Transfer') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('trusts')" wire:navigate>
                                {{ __('Trusts') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- Items -->
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ __('Items') }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('item-card')" wire:navigate>
                                {{ __('Item Card') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('item-monitor')" wire:navigate>
                                {{ __('Item Monitor') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('item-report')" wire:navigate>
                                {{ __('Item Report') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- Reports -->
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ __('Reports') }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('receiving-search')" wire:navigate>
                                {{ __('Receiving Search') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('requisition-search')" wire:navigate>
                                {{ __('Requisition Search') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('trust-search')" wire:navigate>
                                {{ __('Trust Search') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('export-reports')" wire:navigate>
                                {{ __('Export Reports') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('inventory-reports')" wire:navigate>
                                {{ __('Inventory Reports') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('department-reports')" wire:navigate>
                                {{ __('Department Report') }}
                            </x-dropdown-link>
                            {{-- document search --}}
                            <x-dropdown-link :href="route('document-search')" wire:navigate>
                                {{ __('Document Search') }}
                            </x-dropdown-link>

                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            {{ __('Management') }}
                            <svg class="fill-current h-4 w-4 ml-1" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('management.items')" wire:navigate>
                            {{ __('Items') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('management.users')" wire:navigate>
                            {{ __('Users') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('management.departments')" wire:navigate>
                            {{ __('Departments') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('management.suppliers')" wire:navigate>
                            {{ __('Suppliers') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('management-category')" wire:navigate>
                            {{ __('Categories') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('management-subcategory')" wire:navigate>
                            {{ __('Subcategories') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('backup-manager')" wire:navigate>
                            {{ __('Backup Manager') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('logout')">
                            {{ __('Logout') }}
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <!-- Dashboard -->
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <!-- Inventory Section -->
            <div class="px-3 text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Inventory') }}</div>
            <x-responsive-nav-link :href="route('receiving')" :active="request()->routeIs('receiving')" wire:navigate>
                {{ __('Receiving') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('requisition')" :active="request()->routeIs('requisition')" wire:navigate>
                {{ __('Requisition') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('transfer')" :active="request()->routeIs('transfer')" wire:navigate>
                {{ __('Transfer') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('trusts')" :active="request()->routeIs('trusts')" wire:navigate>
                {{ __('Trusts') }}
            </x-responsive-nav-link>

            <!-- Items Section -->
            <div class="px-3 mt-4 text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Items') }}</div>
            <x-responsive-nav-link :href="route('item-card')" :active="request()->routeIs('item-card')" wire:navigate>
                {{ __('Item Card') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('item-monitor')" :active="request()->routeIs('item-monitor')" wire:navigate>
                {{ __('Item Monitor') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('item-report')" :active="request()->routeIs('item-report')" wire:navigate>
                {{ __('Item Report') }}
            </x-responsive-nav-link>

            <!-- Reports Section -->
            <div class="px-3 mt-4 text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Reports') }}</div>
            <x-responsive-nav-link :href="route('receiving-search')" :active="request()->routeIs('receiving-search')" wire:navigate>
                {{ __('Receiving Search') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('export-reports')" :active="request()->routeIs('export-reports')" wire:navigate>
                {{ __('Export Reports') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('inventory-reports')" :active="request()->routeIs('inventory-reports')" wire:navigate>
                {{ __('Inventory Reports') }}
            </x-responsive-nav-link>

            <!-- Management Section -->
            <div class="px-3 mt-4 text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Management') }}</div>
            <x-responsive-nav-link :href="route('management.items')" :active="request()->routeIs('management.items')" wire:navigate>
                {{ __('Items') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('management.users')" :active="request()->routeIs('management.users')" wire:navigate>
                {{ __('Users') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('management.departments')" :active="request()->routeIs('management.departments')" wire:navigate>
                {{ __('Departments') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('management.suppliers')" :active="request()->routeIs('management.suppliers')" wire:navigate>
                {{ __('Suppliers') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('management-category')" :active="request()->routeIs('management-category')" wire:navigate>
                {{ __('Categories') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('management-subcategory')" :active="request()->routeIs('management-subcategory')" wire:navigate>
                {{ __('Subcategories') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('backup-manager')" :active="request()->routeIs('backup-manager')" wire:navigate>
                {{ __('Backup Manager') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            @if (auth()->check())
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                        x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                </div>
            @endif

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
