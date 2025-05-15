<!-- resources/views/livewire/language-switcher.blade.php -->
<div>
    <!-- Button to toggle language -->
    <button onclick="toggleLanguage()"
        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1 rtl:mr-0 rtl:ml-1">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
            <path d="M2 12h20"></path>
        </svg>
        {{ strtoupper(($selectedLang == 'ar') ? 'EN' : 'AR') }}
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
    }
</script>