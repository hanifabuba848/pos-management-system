<?php
use App\Models\User;

$user = User::first();
echo "Currently checking user: " . $user->email . "\n";
echo "Their roles are: " . $user->roles->pluck('name')->join(', ') . "\n";
