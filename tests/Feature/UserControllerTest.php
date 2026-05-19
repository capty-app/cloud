<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('creates a user', function () {
    $this->actingAs($this->admin)
        ->post('/admin/users', [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'password1234',
            'role' => 'user',
        ])
        ->assertRedirect('/admin/users');

    $user = User::firstWhere('email', 'bob@example.com');
    expect($user)->not->toBeNull();
    expect($user->role)->toBe('user');
});

it('updates a user', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($this->admin)
        ->put("/admin/users/{$user->id}", [
            'name' => 'New Name',
            'email' => $user->email,
            'role' => 'admin',
        ])
        ->assertRedirect('/admin/users');

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->role)->toBe('admin');
});

it('keeps password unchanged when not provided', function () {
    $user = User::factory()->create();
    $original = $user->password;

    $this->actingAs($this->admin)
        ->put("/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'user',
        ])->assertRedirect();

    expect($user->fresh()->password)->toBe($original);
});

it('updates password when provided', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->put("/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'user',
            'password' => 'newpassword123',
        ])->assertRedirect();

    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});

it('refuses to demote the last admin', function () {
    $this->actingAs($this->admin)
        ->put("/admin/users/{$this->admin->id}", [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'user',
        ])
        ->assertSessionHasErrors('role');

    expect($this->admin->fresh()->role)->toBe('admin');
});

it('allows demoting an admin when another admin exists', function () {
    $other = User::factory()->admin()->create();

    $this->actingAs($this->admin)
        ->put("/admin/users/{$other->id}", [
            'name' => $other->name,
            'email' => $other->email,
            'role' => 'user',
        ])->assertRedirect();

    expect($other->fresh()->role)->toBe('user');
});

it('prevents self-delete', function () {
    $this->actingAs($this->admin)
        ->delete("/admin/users/{$this->admin->id}")
        ->assertSessionHas('error');

    expect(User::find($this->admin->id))->not->toBeNull();
});

it('prevents deleting the last admin', function () {
    $other = User::factory()->admin()->create();
    // Now delete current admin while the only other admin survives
    $this->actingAs($other)
        ->delete("/admin/users/{$this->admin->id}")
        ->assertRedirect();

    // Try to delete the remaining admin from a (new) admin context that was just removed
    $this->actingAs($other)
        ->delete("/admin/users/{$other->id}")
        ->assertSessionHas('error');

    expect(User::find($other->id))->not->toBeNull();
});

it('rejects invalid input', function () {
    $this->actingAs($this->admin)
        ->post('/admin/users', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'role' => 'invalid',
        ])
        ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});

it('rejects duplicate emails on create', function () {
    User::factory()->create(['email' => 'dup@example.com']);

    $this->actingAs($this->admin)
        ->post('/admin/users', [
            'name' => 'Dup',
            'email' => 'dup@example.com',
            'password' => 'password1234',
            'role' => 'user',
        ])->assertSessionHasErrors('email');
});
