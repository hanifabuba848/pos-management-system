<?php
use App\Models\User;
use Spatie\Permission\Models\Role;

Role::firstOrCreate(['name' => 'Admin']);

$users = User::all();
foreach ($users as $user) {
    $user->assignRole('Admin');
    echo "Assigned Admin to: " . $user->email . "\n";
}
