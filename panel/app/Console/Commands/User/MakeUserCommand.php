<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'p:user:make
        {--email= : The email address for the user}
        {--username= : The username for the user}
        {--name-first= : The first name of the user}
        {--name-last= : The last name of the user}
        {--password= : The password for the user}
        {--admin= : Whether this user is an administrator (1 or 0)}
        {--no-password : Create user without a password}';

    /**
     * The console command description.
     */
    protected $description = 'Creates a user on the system via the CLI.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isAdmin = $this->option('admin');
        if (is_null($isAdmin)) {
            $isAdmin = $this->confirm('Is this user an administrator?');
        } else {
            $isAdmin = (bool) $isAdmin;
        }

        $email = $this->option('email') ?? $this->ask('Email address');
        $username = $this->option('username') ?? $this->ask('Username');
        $firstName = $this->option('name-first') ?? $this->ask('First name');
        $lastName = $this->option('name-last') ?? $this->ask('Last name');

        $password = null;
        if (!$this->option('no-password')) {
            if (is_null($this->option('password'))) {
                $this->warn('Passwords must meet the following requirements: min 8 characters.');
                $password = $this->secret('Password');
            } else {
                $password = $this->option('password');
            }
        }

        // Create the user
        $user = User::create([
            'email' => $email,
            'username' => $username,
            'name_first' => $firstName,
            'name_last' => $lastName,
            'password' => $password ? Hash::make($password) : null,
            'root_admin' => $isAdmin,
        ]);

        $this->table(['Field', 'Value'], [
            ['UUID', $user->uuid ?? $user->id],
            ['Email', $user->email],
            ['Username', $user->username],
            ['Admin', $user->root_admin ? 'Yes' : 'No'],
        ]);

        $this->info('User created successfully!');

        return Command::SUCCESS;
    }
}
