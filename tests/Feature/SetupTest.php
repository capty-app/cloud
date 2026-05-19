<?php

use App\Models\User;

it('redirects to /setup when no admin exists', function () {
    $this->get('/')->assertRedirect('/setup');
});

it('renders the setup page when no admin exists', function () {
    $this->get('/setup')->assertOk();
});

it('redirects setup to /login when an admin already exists', function () {
    User::factory()->admin()->create();

    $this->get('/setup')->assertRedirect('/login');
});

it('creates the first admin and logs them in', function () {
    $this->post('/setup', [
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ])->assertRedirect('/admin');

    $admin = User::firstWhere('email', 'admin@example.com');
    expect($admin)->not->toBeNull();
    expect($admin->role)->toBe(User::ROLE_ADMIN);
    $this->assertAuthenticatedAs($admin);
});

it('rejects setup post if validation fails', function () {
    $this->post('/setup', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ])->assertSessionHasErrors(['name', 'email', 'password']);
});

it('rejects setup post once an admin exists', function () {
    User::factory()->admin()->create();

    $this->post('/setup', [
        'name' => 'Intruder',
        'email' => 'evil@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ])->assertRedirect('/login');

    expect(User::where('email', 'evil@example.com')->exists())->toBeFalse();
});
