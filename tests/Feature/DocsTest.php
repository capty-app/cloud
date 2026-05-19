<?php

use App\Models\User;

beforeEach(function () {
    User::factory()->admin()->create();
});

it('redirects anonymous visitors to /login', function () {
    $this->get('/docs')->assertRedirect('/login');
});

it('renders the docs index for signed-in users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/docs')->assertOk();
});

it('renders an individual docs page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/docs/configuration')->assertOk();
});

it('404s on an unknown docs slug', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/docs/no-such-page')->assertNotFound();
});
