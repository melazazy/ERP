<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\Role;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        // Convert multiple parameters into a single array
        // Laravel passes "System Administrator,Warehouse Manager,Receiving Clerk" as separate parameters
        if (count($roles) === 1 && str_contains($roles[0], ',')) {
            $allowedRoles = array_map('trim', explode(',', $roles[0]));
        } else {
            $allowedRoles = $roles;
        }
        
        // DEBUG: Check what middleware is actually applied (remove in production)
        $route = $request->route();
        $middleware = $route ? $route->gatherMiddleware() : [];
        
        // Get user role name
        $userRoleName = null;
        if ($user && $user->role_id) {
            $userRole = Role::find($user->role_id);
            $userRoleName = $userRole ? $userRole->name : 'Invalid Role';
        }
        
        // dd([
        //     'user_role_name' => $userRoleName ?: 'No user/role',
        //     'required_roles' => $allowedRoles,
        //     'roles_parameter_received' => $roles,
        //     'route_name' => $route ? $route->getName() : 'No route',
        //     'route_uri' => $route ? $route->uri() : 'No URI',
        //     'all_middleware' => $middleware,
        //     'should_have_access' => $userRoleName ? in_array($userRoleName, $allowedRoles) : false,
        // ]);
        
        // 1. Check if user is authenticated
        if (!$user) {
            Log::warning('CheckRole: No authenticated user found. Redirecting to login.');
            return redirect()->route('login');
        }
        
        // 2. Check if user has a role_id assigned in the users table
        if (!$user->role_id) {
            Log::warning("CheckRole: User ID {$user->id} has no role_id assigned.");
            return redirect()->route('dashboard')->with('error', 'You do not have any role assigned.');
        }
        
        // 3. Find the role name from the roles table using the role_id
        $userRole = Role::find($user->role_id);
        
        if (!$userRole) {
            Log::error("CheckRole: Role with ID {$user->role_id} not found in roles table for User ID {$user->id}.");
            return redirect()->route('dashboard')->with('error', 'Your assigned role is invalid.');
        }
        
        $userRoleName = $userRole->name;
        
        // 5. Perform a case-insensitive check
        $hasAccess = false;
        foreach ($allowedRoles as $allowedRole) {
            if (strcasecmp($userRoleName, $allowedRole) == 0) {
                $hasAccess = true;
                break;
            }
        }
        
        // 6. Check for access and take action
        if ($hasAccess) {
            return $next($request);
        }
        
        // If access is denied:
        Log::warning("CheckRole: Access DENIED for User ID {$user->id} (Role: {$userRoleName}) to a route requiring one of: " . implode(', ', $allowedRoles));
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'You do not have permission to access this resource.',
                'user_role' => $userRoleName,
                'allowed_roles' => $allowedRoles
            ], 403);
        }
        
        return redirect()->route('dashboard')->with('error', 'You do not have permission to access this page. Your role: ' . $userRoleName);
    }
}