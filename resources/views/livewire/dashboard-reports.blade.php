<div>
    {{-- Totals Section --}}
    @if(!empty($numbers) && !empty($numbers['totals']))
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        @if($permissions['can_view_receivings'] && isset($numbers['totals']['receivings']))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Total Receivings Amount</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                ${{ number_format($numbers['totals']['receivings'], 2) }}
            </div>
        </div>
        @endif
        
        @if($permissions['can_view_requisitions'] && isset($numbers['totals']['requisitions']))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Total Requisitions Amount</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                ${{ number_format($numbers['totals']['requisitions'], 2) }}
            </div>
        </div>
        @endif
        
        @if($permissions['can_view_trusts'] && isset($numbers['totals']['trusts']))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Total Trusts Amount</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                ${{ number_format($numbers['totals']['trusts'], 2) }}
            </div>
        </div>
        @endif
        
        @if($permissions['can_view_users'] && isset($numbers['totals']['users']))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Total Users</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                {{ number_format($numbers['totals']['users']) }}
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Role-Specific Reports Section --}}
    @if(!empty($numbers['role_specific']))
    <div class="mb-8">
        <div class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Role-Specific Reports</div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            @foreach($numbers['role_specific'] as $key => $value)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                        {{ ucwords(str_replace('_', ' ', $key)) }}
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        @if(is_array($value))
                            @if(isset($value['this_month']) && isset($value['last_month']))
                                <div class="text-xl">{{ $value['this_month'] }}</div>
                                <div class="text-sm text-gray-500 mt-1">vs {{ $value['last_month'] }} last month</div>
                            @elseif(isset($value['budget']) && isset($value['actual']))
                                <div class="text-xl">${{ number_format($value['actual'], 2) }}</div>
                                <div class="text-sm text-gray-500 mt-1">Budget: ${{ number_format($value['budget'], 2) }}</div>
                            @elseif(isset($value['efficiency_score']))
                                <div class="text-xl">{{ $value['efficiency_score'] }}%</div>
                                @if(isset($value['efficiency_level']))
                                <div class="text-sm text-gray-500 mt-1">{{ $value['efficiency_level'] }}</div>
                                @endif
                            @elseif(isset($value['risk_score']))
                                <div class="text-xl">{{ $value['risk_score'] }}</div>
                                @if(isset($value['risk_level']))
                                <div class="text-sm text-gray-500 mt-1">{{ $value['risk_level'] }} Risk</div>
                                @endif
                            @else
                                <div class="text-lg">{{ json_encode($value) }}</div>
                            @endif
                        @elseif(is_numeric($value))
                            @if(str_contains($key, 'rate') || str_contains($key, 'percentage'))
                                {{ $value }}%
                            @elseif(str_contains($key, 'amount') || str_contains($key, 'value') || str_contains($key, 'cost'))
                                ${{ number_format($value, 2) }}
                            @else
                                {{ number_format($value) }}
                            @endif
                        @else
                            {{ $value }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Department Breakdown Section --}}
    @if($permissions['can_view_department_breakdown'] && !empty($numbers['perDept']))
    <div class="mb-8 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6 text-gray-900 dark:text-gray-100 font-semibold text-lg border-b border-gray-200 dark:border-gray-700">
            @if($permissions['can_view_all_departments'])
                Top Departments by Remaining Stock Amount
            @else
                Your Department Stock Amount
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Remaining Amount</th>
                        @if($permissions['can_view_receivings'])
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Received Amount</th>
                        @endif
                        @if($permissions['can_view_requisitions'])
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requisitioned Amount</th>
                        @endif
                        @if($permissions['can_view_trusts'])
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Trusts Amount</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($numbers['perDept'] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $row['department'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                                ${{ number_format($row['remaining'], 2) }}
                            </td>
                            @if($permissions['can_view_receivings'])
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                ${{ number_format($row['received'], 2) }}
                            </td>
                            @endif
                            @if($permissions['can_view_requisitions'])
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                ${{ number_format($row['requisitioned'], 2) }}
                            </td>
                            @endif
                            @if($permissions['can_view_trusts'])
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                ${{ number_format($row['trusted'], 2) }}
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-8 text-center text-gray-500 dark:text-gray-400" colspan="{{ $permissions['can_view_receivings'] + $permissions['can_view_requisitions'] + $permissions['can_view_trusts'] + 2 }}">
                                <div class="text-lg font-medium">No Data Available</div>
                                <div class="text-sm mt-2">No department data found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Charts Section --}}
    @if($permissions['can_view_charts'] && !empty($charts))
    <div class="mb-8">
        <div class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Analytics & Charts</div>
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
            @foreach($charts as $chart)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __($chart['title']) }}
                    </div>
                    <div class="relative" style="height: 300px;">
                        <canvas id="{{ $chart['id'] }}" class="w-full h-full" wire:ignore></canvas>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- No Data Message --}}
    @if(empty($numbers['totals']) && empty($numbers['perDept']) && empty($numbers['role_specific']) && empty($charts))
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8">
        <div class="text-center text-gray-500 dark:text-gray-400">
            <div class="text-xl font-medium mb-2">No Data Available</div>
            <div class="text-sm">You don't have permission to view any dashboard data.</div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
@if($permissions['can_view_charts'] && !empty($charts))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        let chartsInitialized = false;
        
        const initCharts = () => {
            if (!window.Chart || chartsInitialized) return;
            
            const chartsCfg = @json($charts ?? []);
            if (chartsCfg.length === 0) return;
            
            setTimeout(() => {
                chartsCfg.forEach(cfg => {
                    const el = document.getElementById(cfg.id);
                    if (!el) {
                        console.warn('Canvas element not found:', cfg.id);
                        return;
                    }

                    // Destroy existing chart if it exists
                    if (window._dashCharts && window._dashCharts[cfg.id]) {
                        window._dashCharts[cfg.id].destroy();
                    }

                    // Initialize new chart
                    if (!window._dashCharts) window._dashCharts = {};
                    
                    try {
                        window._dashCharts[cfg.id] = new Chart(el, {
                            type: cfg.type,
                            data: {
                                labels: cfg.labels,
                                datasets: cfg.datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: 500 },
                                plugins: { 
                                    legend: { 
                                        display: true, 
                                        position: 'bottom',
                                        labels: {
                                            padding: 20,
                                            usePointStyle: true
                                        }
                                    } 
                                },
                                scales: cfg.type === 'doughnut' ? {} : {
                                    y: { 
                                        beginAtZero: true, 
                                        ticks: { precision: 0 } 
                                    }
                                }
                            }
                        });
                        console.log('Chart initialized:', cfg.id);
                    } catch (error) {
                        console.error('Error initializing chart:', cfg.id, error);
                    }
                });
                
                chartsInitialized = true;
            }, 200);
        };

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }

        // Initialize when Livewire loads
        document.addEventListener('livewire:load', () => {
            chartsInitialized = false;
            initCharts();
        });

        // Re-initialize after Livewire updates
        document.addEventListener('livewire:updated', () => {
            chartsInitialized = false;
            setTimeout(initCharts, 100);
        });

        // Re-initialize after Livewire DOM morphing
        if (window.Livewire && window.Livewire.hook) {
            window.Livewire.hook('message.processed', () => {
                chartsInitialized = false;
                setTimeout(initCharts, 100);
            });
        }

        // Fallback: try to initialize after a delay
        setTimeout(initCharts, 800);
    })();
</script>
@endif
@endpush