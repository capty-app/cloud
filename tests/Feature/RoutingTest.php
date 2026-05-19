<?php

use App\Models\User;

beforeEach(function () {
    User::factory()->admin()->create();
});

it('redirects / to /login when unauthenticated', function () {
    $this->get('/')->assertRedirect('/login');
});

it('redirects / to /dashboard when authenticated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')->assertRedirect('/dashboard');
});

it('redirects admins from /dashboard to /admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/dashboard')->assertRedirect('/admin');
});

it('renders the user dashboard for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

it('blocks users from admin routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
    $this->actingAs($user)->get('/admin/galleries')->assertForbidden();
    $this->actingAs($user)->get('/admin/users')->assertForbidden();
});

it('allows admins into admin routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($admin)->get('/admin/galleries')->assertOk();
    $this->actingAs($admin)->get('/admin/users')->assertOk();
});
