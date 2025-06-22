<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role; // Add this line to import the Role model
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasRoles;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $with = ['role']; // Always eager load the role relationship

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role associated with the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole($roleName)
    {
        Log::info('User Model - Checking if user has role: ' . $roleName);
        Log::info('User Model - User ID: ' . $this->id);
        
        if (!$this->role) {
            Log::warning('User Model - User has no role assigned');
            return false;
        }

        Log::info('User Model - User Role ID: ' . $this->role->id);
        Log::info('User Model - User Role Name: ' . $this->role->name);
        
        $hasRole = strtolower($this->role->name) === strtolower($roleName);
        Log::info('User Model - Has role ' . $roleName . ': ' . ($hasRole ? 'Yes' : 'No'));
        
        return $hasRole;
    }

    public function hasAnyRole($roles)
    {
        Log::info('User Model - Checking if user has any of roles: ' . (is_array($roles) ? implode(', ', $roles) : $roles));
        
        if (!$this->role) {
            Log::warning('User Model - User has no role assigned');
            return false;
        }
        
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }
        
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                Log::info('User Model - User has at least one of the required roles');
                return true;
            }
        }
        
        Log::info('User Model - User has none of the required roles');
        return false;
    }

    public function hasPermission($permission)
    {
        Log::info('User Model - Checking if user has permission: ' . $permission);
        
        if (!$this->role) {
            Log::warning('User Model - User has no role assigned');
            return false;
        }
        
        $hasPermission = $this->role->permissions->contains('name', $permission);
        Log::info('User Model - Has permission ' . $permission . ': ' . ($hasPermission ? 'Yes' : 'No'));
        
        return $hasPermission;
    }
}
