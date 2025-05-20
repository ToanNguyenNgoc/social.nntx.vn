<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
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
        //Gen permissions
        $routes = Route::getRoutes()->getRoutes();
        foreach ($routes as $route) {
            $action = $route->getAction();
            if (
                array_key_exists('as', $action) &&
                !str_starts_with($action['as'], 'debugbar')
            ) {
                if (Permission::findOrCreate($action['as'], 'api')->wasRecentlyCreated) {
                    $this->output->text('Created permission: ' . $action['as']);
                }
            }

            if (isset($action['as'])) {
                $check_out_of_date_permission[] = $action['as'];
            }
        }
        $this->output->success('Total permission: ' . count($routes));

        $this->output->comment('Generate roles success');
    }
}
