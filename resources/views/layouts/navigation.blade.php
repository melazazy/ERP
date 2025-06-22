<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
    @php
        $user = auth()->user();
        $userRole = $user ? $user->role->name : null;
    @endphp
    
    @foreach(\App\Helpers\RouteHelper::getAvailableRoutes() as $route)
        @if(in_array('*', $route['roles']) || ($userRole && in_array($userRole, $route['roles'])))
            <x-nav-link :href="route($route['route'])" :active="request()->routeIs($route['route'])">
                <i class="{{ $route['icon'] }} mr-2"></i>
                {{ $route['name'] }}
            </x-nav-link>
        @endif
    @endforeach
</div>

<!-- Responsive Navigation Menu -->
<div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
    @foreach(\App\Helpers\RouteHelper::getAvailableRoutes() as $route)
        @if(in_array('*', $route['roles']) || ($userRole && in_array($userRole, $route['roles'])))
            <x-responsive-nav-link :href="route($route['route'])" :active="request()->routeIs($route['route'])">
                <i class="{{ $route['icon'] }} mr-2"></i>
                {{ $route['name'] }}
            </x-responsive-nav-link>
        @endif
    @endforeach
</div> 