<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    @foreach($reports as $report)
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="p-3 rounded-lg {{ $report['color'] }} bg-opacity-10">
                    <i data-feather="{{ $report['icon'] }}" class="w-6 h-6"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ __('messages.' . Str::snake($report['title'])) }}
                    </h3>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ 
                        __('messages.value') . ' ' . $report['value'] }}</p>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script src="https://unpkg.com/feather-icons"></script>
<script>
    feather.replace();
</script>
@endpush