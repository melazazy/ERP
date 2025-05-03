<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): RedirectResponse
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
        
        return Redirect::to('/');
    }
}