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
    
    <!-- Add AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <style>
        .gradient-text {
            /* background: linear-gradient(135deg, #2563EB, #4F46E5); */
            background: linear-gradient(135deg, #4F46E5, #7C3AED);

            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .glass-card {
            /* background: rgba(255, 255, 255, 0.9); */
            background: rgba(255, 255, 255, 0.8);

            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.1);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #2563EB 0%, #4F46E5 100%);
        }
        
        .gradient-bg-alt {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        }
        
        .hover-gradient {
            transition: all 0.3s ease;
        }
        
        .hover-gradient:hover {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .hero-overlay {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.4), rgba(79, 70, 229, 0.4));
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="animate-float">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-blue-600">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                            <path d="m3.3 7 8.7 5 8.7-5"></path>
                            <path d="M12 22V12"></path>
                        </svg>
                    </div>
                    <span class="gradient-text text-xl font-bold">{{ __('messages.erp_store') }}</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Language Switcher -->
                    <livewire:language-switcher />
                    
                    @guest
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-blue-600 bg-white hover:bg-blue-50 transition-all duration-300 shadow-sm hover:shadow-md">
                            {{ __('messages.login') }}
                        </a>
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-white gradient-bg hover:opacity-90 transition-all duration-300 shadow-md hover:shadow-lg">
                            {{ __('messages.register') }}
                        </a>
                    @else
                        <!-- Management Dropdown -->
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-gray-700 bg-white hover:bg-blue-50 transition-all duration-300 shadow-sm hover:shadow-md">
                                    {{ __('messages.management') }}
                                    <svg class="fill-current h-4 w-4 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="glass-card rounded-xl shadow-xl">
                                    <x-dropdown-link :href="route('management.items')" wire:navigate class="hover:bg-blue-50">
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
                                </div>
                            </x-slot>
                        </x-dropdown>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative min-h-screen pt-16 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 hero-overlay"></div>
            <img src="https://images.pexels.com/photos/7703380/pexels-photo-7703380.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" 
                 alt="{{ __('messages.warehouse_inventory') }}" 
                 class="w-full h-full object-cover"
            />
        </div>
        
        <div class="relative z-10 flex items-center justify-center min-h-[calc(100vh-4rem)] px-4">
            <div class="w-full max-w-4xl mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                <div class="glass-card rounded-3xl shadow-2xl p-8 sm:p-12">
                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6 gradient-text">
                        {{ __('messages.streamline_inventory_management') }}
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-700 mb-8 max-w-2xl mx-auto">
                        {{ __('messages.comprehensive_erp_system_description') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section class="py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold mb-4 gradient-text">
                    {{ __('messages.powerful_features') }}
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ __('messages.comprehensive_erp_system_features') }}
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                $features = [
                    [
                        'title' => __('messages.items_management'),
                        'description' => __('messages.items_management_description'),
                        'icon' => 'box',
                        'delay' => '0'
                    ],
                    [
                        'title' => __('messages.receiving_management'),
                        'description' => __('messages.receiving_management_description'),
                        'icon' => 'package',
                        'delay' => '100'
                    ],
                    [
                        'title' => __('messages.requisition_management'),
                        'description' => __('messages.requisition_management_description'),
                        'icon' => 'clipboard',
                        'delay' => '200'
                    ],
                    [
                        'title' => __('messages.department_management'),
                        'description' => __('messages.department_management_description'),
                        'icon' => 'users',
                        'delay' => '300'
                    ],
                    [
                        'title' => __('messages.supplier_management'),
                        'description' => __('messages.supplier_management_description'),
                        'icon' => 'truck',
                        'delay' => '400'
                    ],
                    [
                        'title' => __('messages.category_management'),
                        'description' => __('messages.category_management_description'),
                        'icon' => 'grid',
                        'delay' => '500'
                    ],
                    [
                        'title' => __('messages.reports_management'),
                        'description' => __('messages.reports_management_description'),
                        'icon' => 'file-text',
                        'delay' => '600'
                    ],
                    [
                        'title' => __('messages.advanced_search'),
                        'description' => __('messages.advanced_search_description'),
                        'icon' => 'search',
                        'delay' => '700'
                    ],
                    [
                        'title' => __('messages.export_import'),
                        'description' => __('messages.export_import_description'),
                        'icon' => 'download',
                        'delay' => '800'
                    ]
                ];
                @endphp

                @foreach($features as $feature)
                <div class="feature-card glass-card rounded-2xl p-8" data-aos="fade-up" data-aos-delay="{{ $feature['delay'] }}">
                    <div class="mb-4 inline-flex items-center justify-center p-3 rounded-xl gradient-bg">
                        <i data-feather="{{ $feature['icon'] }}" class="h-6 w-6 text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ $feature['title'] }}</h3>
                    <p class="text-gray-600">{{ $feature['description'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="gradient-bg text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-t border-white/10 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-white/80 text-sm mb-4 md:mb-0">
                        &copy; {{ date('Y') }} {{ __('messages.erp_store') }}. {{ __('messages.all_rights_reserved') }}
                    </p>
                    <div class="flex space-x-6">
                        <a href="#terms" class="text-white/80 hover:text-white text-sm">{{ __('messages.terms_of_service') }}</a>
                        <a href="#privacy" class="text-white/80 hover:text-white text-sm">{{ __('messages.privacy_policy') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        // Initialize Feather Icons
        feather.replace();
        
        // Initialize AOS animations
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Navbar scroll effect
        const nav = document.querySelector('nav');
        const handleScroll = () => {
            if (window.scrollY > 10) {
                nav.classList.add('glass-card');
                nav.classList.remove('bg-transparent');
            } else {
                nav.classList.remove('glass-card');
                nav.classList.add('bg-transparent');
            }
        };
        
        window.addEventListener('scroll', handleScroll);
        handleScroll(); // Initial check
    </script>
</body>
</html>