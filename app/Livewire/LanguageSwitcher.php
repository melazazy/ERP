<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Component
{
    public $selectedLang;

    public function mount()
    {
        $this->selectedLang = Session::get('locale', App::getLocale());
    }

    public function updatedSelectedLang($lang)
    {
        App::setLocale($lang);
        Session::put('locale', $lang);
        $this->redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}