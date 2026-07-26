<?php

use App\Models\Admin;
use Laravel\Sanctum\Sanctum;

test('a regular admin cannot list or create admins', function () {
    Sanctum::actingAs(Admin::factory()->create(), ['*']);

    $this->getJson('/api/admin/admins')->assertForbidden();

    $this->postJson('/api/admin/admins', [
        'name' => 'New Admin',
        'email' => 'new-admin@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertForbidden();
});

test('a superadmin can list admins', function () {
    Sanctum::actingAs(Admin::factory()->superadmin()->create(), ['*']);
    Admin::factory()->count(2)->create();

    $this->getJson('/api/admin/admins')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('a superadmin can create a new admin', function () {
    Sanctum::actingAs(Admin::factory()->superadmin()->create(), ['*']);

    $this->postJson('/api/admin/admins', [
        'name' => 'New Admin',
        'email' => 'new-admin@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'new-admin@example.com')
        ->assertJsonPath('data.role', 'admin');

    $admin = Admin::where('email', 'new-admin@example.com')->sole();
    expect($admin->role)->toBe('admin');
});

test('a superadmin can create another superadmin', function () {
    Sanctum::actingAs(Admin::factory()->superadmin()->create(), ['*']);

    $this->postJson('/api/admin/admins', [
        'name' => 'Second Superadmin',
        'email' => 'super2@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'superadmin',
    ])
        ->assertCreated()
        ->assertJsonPath('data.role', 'superadmin');
});

test('creating an admin requires a unique email', function () {
    Sanctum::actingAs(Admin::factory()->superadmin()->create(), ['*']);
    Admin::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/admin/admins', [
        'name' => 'Duplicate',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertJsonValidationErrors('email');
});
