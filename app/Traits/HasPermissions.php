<?php

namespace App\Traits;

trait HasPermissions
{
    /**
     * Check if the user has a specific permission through their role
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role && $this->role->hasPermission($permission);
    }

    /**
     * Check if the user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user can perform an action on a resource
     */
    public function can(string $verb, string $resource): bool
    {
        return $this->role && $this->role->permissions()
            ->where('verb', $verb)
            ->where('resource', $resource)
            ->exists();
    }
}
