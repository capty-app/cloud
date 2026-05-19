<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    // Ensure RedirectToSetup middleware lets us hit /login (needs at least one admin)
    User::factory()->admin()->create();
});

it('renders the login screen', function () {
    $this->get('/login')->assertOk();
});

it('lets users authenticate via Fortify', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

it('logs the user out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect();

    $this->assertGuest();
});

it('rate-limits repeated login attempts', function () {
    $user = User::factory()->create();

    RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertTooManyRequests();
});
