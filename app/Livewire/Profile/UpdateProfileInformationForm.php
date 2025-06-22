<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileInformationForm extends Component
{
    public $name;
    public $email;

    public function mount()
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore(Auth::user()->id)],
        ]);

        Auth::user()->update($validated);

        $this->dispatch('profile-updated', name: $this->name);
        $this->dispatch('profile-information-updated');
    }

    public function render()
    {
        return view('livewire.profile.update-profile-information-form');
    }
} 