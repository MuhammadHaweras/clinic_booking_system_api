<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     *
     * These three roles represent every actor in the system.
     * - admin    : full system access
     * - provider : manages own services, hours, and appointments
     * - customer : browses, books, and pays for appointments
     */
    public function run(): void
    {
        foreach (['admin', 'provider', 'customer'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
