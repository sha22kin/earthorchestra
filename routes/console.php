<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

Artisan::command('admin:create', function () {
    $data = [
        'name' => text('Name', required: true),
        'email' => text('Email', required: true),
        'password' => password('Password (min 10 characters)', required: true),
        'role' => select('Role', User::ROLES, default: 'admin'),
    ];

    $validator = Validator::make($data, [
        'email' => ['email', 'unique:users,email'],
        'password' => ['min:10'],
    ]);

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return 1;
    }

    User::create($data);
    $this->info("Created {$data['email']}.");
})->purpose('Create an admin panel account');
