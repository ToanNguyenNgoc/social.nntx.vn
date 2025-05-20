<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GenRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:gen-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $super_admin = Role::findOrCreate(User::ROLE_SUPER_ADMIN, 'api');
        $admin = Role::findOrCreate(User::ROLE_ADMIN, 'api');

        User::firstOrCreate(
            ['email' => config('app.instance.instance_spa')],
            [
                'name' => config('app.instance.instance_spa'),
                'password' => config('app.instance.instance_spa_password'),
            ]
        )->assignRole($super_admin);

        $this->output->comment('Generate roles success');
    }
}
