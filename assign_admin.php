<?php
use App\Models\User;
use Spatie\Permission\Models\Role;

Role::firstOrCreate(['name' => 'Admin']);
$user = User::first();
if($user) {
    $user->assignRole('Admin');
    echo 'Role assigned successfully to: ' . $user->email . "\n";
} else {
    echo "No users found in database.\n";
}
