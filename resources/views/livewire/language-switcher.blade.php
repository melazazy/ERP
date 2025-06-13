<!-- resources/views/livewire/language-switcher.blade.php -->
<div>
    <!-- Button to toggle language -->
    <button onclick="toggleLanguage()"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-gray-700 bg-white/80 hover:bg-white transition-all duration-300 backdrop-blur-sm shadow-sm hover:shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-2 rtl:mr-0 rtl:ml-2">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
            <path d="M2 12h20"></path>
        </svg>
        <span class="gradient-text font-semibold">{{ strtoupper(($selectedLang == 'ar') ? 'EN' : 'AR') }}</span>
    </button>

    <!-- Hidden select element bound to Livewire -->
    <select wire:model.live="selectedLang" class="hidden">
        <option value="en"></option>
        <option value="ar"></option>
    </select>
</div>

<script>
    function toggleLanguage() {
        const currentLang = "{{ app()->getLocale() }}";
        const newLang = currentLang === 'en' ? 'ar' : 'en';
        const selectElement = document.querySelector('select[wire\\:model\\.live="selectedLang"]');
        selectElement.value = newLang;
        selectElement.dispatchEvent(new Event('input'));
        selectElement.dispatchEvent(new Event('change'));
        
        // Add RTL/LTR handling
        document.documentElement.dir = newLang === 'ar' ? 'rtl' : 'ltr';
    }
    
    // Set initial direction
    document.addEventListener('DOMContentLoaded', () => {
        document.documentElement.dir = "{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}";
    });
</script>