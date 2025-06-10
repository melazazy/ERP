<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/vite.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="{{ __('messages.erp_store_description') }}" />
    <meta name="keywords" content="{{ __('messages.erp_store_keywords') }}" />
    <meta name="author" content="{{ __('messages.erp_store_author') }}" />
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="{{ __('messages.erp_store') }} - {{ __('messages.modern_inventory_management_system') }}" />
    <meta property="og:description" content="{{ __('messages.comprehensive_erp_system_description') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/') }}" />
    
    <title>{{ __('messages.erp_store') }} - {{ __('messages.modern_inventory_management_system') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 transition-all duration-300 bg-white shadow-md py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-indigo-600">
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                        <path d="m3.3 7 8.7 5 8.7-5"></path>
                        <path d="M12 22V12"></path>
                    </svg>
                    <span class="ml-2 text-xl font-bold text-gray-800">{{ __('messages.erp_store') }}</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    @guest
                        <a 
                            href="{{ route('login') }}" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 hover:text-indigo-600 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('messages.login') }}
                        </a>
                        <a 
                            href="{{ route('register') }}" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('messages.register') }}
                        </a>
                    @else
                        <!-- Language Switcher -->
                        <livewire:language-switcher />
                        
                        <!-- Management Dropdown -->
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    {{ __('messages.management') }}
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
                                    {{ __('messages.items') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management.users')" wire:navigate>
                                    {{ __('messages.users') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management.departments')" wire:navigate>
                                    {{ __('messages.departments') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management.suppliers')" wire:navigate>
                                    {{ __('messages.suppliers') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management-category')" wire:navigate>
                                    {{ __('messages.categories') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management-subcategory')" wire:navigate>
                                    {{ __('messages.subcategories') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('backup-manager')" wire:navigate>
                                    {{ __('messages.backup_manager') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('logout')">
                                    {{ __('messages.logout') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative min-h-screen pt-16">
        <div class="absolute inset-0 z-0">
            <img 
                src="https://images.pexels.com/photos/7703380/pexels-photo-7703380.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" 
                alt="{{ __('messages.warehouse_inventory') }}" 
                class="w-full h-full object-cover opacity-20"
            />
        </div>
        
        <div class="relative z-10 flex items-center justify-center min-h-[calc(100vh-4rem)] px-4">
            <div class="w-full max-w-4xl mx-auto text-center">
                <div class="bg-white bg-opacity-80 backdrop-filter backdrop-blur-lg rounded-2xl shadow-xl p-8 sm:p-12 transition-all duration-500 hover:shadow-2xl">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                        {{ __('messages.streamline_inventory_management') }}
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-700 mb-8 max-w-2xl mx-auto">
                        {{ __('messages.comprehensive_erp_system_description') }}
                    </p>
                    
                    <div class="flex flex-col sm:flex-row justify-center gap-4 sm:gap-6 mb-8">
                        <a 
                            href="{{ route('login') }}" 
                            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors transform hover:scale-105 duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('messages.login') }}
                        </a>
                        <a 
                            href="{{ route('register') }}" 
                            class="inline-flex items-center justify-center px-6 py-3 border border-indigo-600 text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50 transition-colors transform hover:scale-105 duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('messages.register') }}
                        </a>
                    </div>
                    
                    <div class="text-gray-600">
                        {{ __('messages.already_have_an_account') }} <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ __('messages.login_here') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section class="py-20 px-4 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    {{ __('messages.powerful_features') }}
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ __('messages.comprehensive_erp_system_features') }}
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature Cards -->
                @php
                $features = [
                    [
                        'title' => __('messages.inventory_tracking'),
                        'description' => __('messages.inventory_tracking_description'),
                        'icon' => 'bar-chart-2'
                    ],
                    [
                        'title' => __('messages.receiving_management'),
                        'description' => __('messages.receiving_management_description'),
                        'icon' => 'package'
                    ],
                    [
                        'title' => __('messages.requisition_processing'),
                        'description' => __('messages.requisition_processing_description'),
                        'icon' => 'clipboard'
                    ],
                    [
                        'title' => __('messages.department_management'),
                        'description' => __('messages.department_management_description'),
                        'icon' => 'layout'
                    ],
                    [
                        'title' => __('messages.supplier_management'),
                        'description' => __('messages.supplier_management_description'),
                        'icon' => 'truck'
                    ],
                    [
                        'title' => __('messages.document_management'),
                        'description' => __('messages.document_management_description'),
                        'icon' => 'file-text'
                    ]
                ];
                @endphp

                @foreach($features as $feature)
                <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
                    <div class="mb-4 inline-flex items-center justify-center p-2 bg-indigo-100 rounded-lg">
                        <i data-feather="{{ $feature['icon'] }}" class="h-8 w-8 text-indigo-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ $feature['title'] }}</h3>
                    <p class="text-gray-600">{{ $feature['description'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8">
                <div class="flex items-center mb-6 md:mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-indigo-400">
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                        <path d="m3.3 7 8.7 5 8.7-5"></path>
                        <path d="M12 22V12"></path>
                    </svg>
                    <span class="ml-2 text-xl font-bold">{{ __('messages.erp_store') }}</span>
                </div>
                
                <div class="flex flex-wrap justify-center gap-6">
                    <a href="#about" class="text-gray-300 hover:text-white transition-colors">{{ __('messages.about') }}</a>
                    <a href="#features" class="text-gray-300 hover:text-white transition-colors">{{ __('messages.features') }}</a>
                    <a href="#pricing" class="text-gray-300 hover:text-white transition-colors">{{ __('messages.pricing') }}</a>
                    <a href="#contact" class="text-gray-300 hover:text-white transition-colors">{{ __('messages.contact') }}</a>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm mb-4 md:mb-0">
                        &copy; {{ date('Y') }} {{ __('messages.erp_store') }}. {{ __('messages.all_rights_reserved') }}
                    </p>
                    <div class="flex space-x-6">
                        <a href="#terms" class="text-gray-400 hover:text-gray-300 text-sm">{{ __('messages.terms_of_service') }}</a>
                        <a href="#privacy" class="text-gray-400 hover:text-gray-300 text-sm">{{ __('messages.privacy_policy') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();
        
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 10) {
                nav.classList.add('bg-white', 'shadow-md', 'py-2');
                nav.classList.remove('bg-transparent', 'py-4');
            } else {
                nav.classList.add('bg-transparent', 'py-4');
                nav.classList.remove('bg-white', 'shadow-md', 'py-2');
            }
        });
    </script>
</body>
</html>