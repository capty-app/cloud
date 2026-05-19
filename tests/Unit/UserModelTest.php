<?php

use App\Models\User;

it('exposes role constants', function () {
    expect(User::ROLE_ADMIN)->toBe('admin');
    expect(User::ROLE_USER)->toBe('user');
});

it('isAdmin reflects the role attribute', function () {
    expect((new User(['role' => 'admin']))->isAdmin())->toBeTrue();
    expect((new User(['role' => 'user']))->isAdmin())->toBeFalse();
});
