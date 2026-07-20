<?php

use App\Models\Admin;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('a request with no token is rejected', function () {
    $this->getJson('/api/admin/dashboard')->assertUnauthorized();
});

test('a non-admin Sanctum user is rejected by the admin gate', function () {
    // The User model doesn't issue Sanctum tokens today, but the guard
    // itself must still reject a non-Admin principal if one ever appears.
    $this->actingAs(User::factory()->create(), 'sanctum');

    $this->getJson('/api/admin/dashboard')->assertForbidden();
});

test('an admin token is let through', function () {
    Sanctum::actingAs(Admin::factory()->create(), ['*']);

    $this->getJson('/api/admin/dashboard')->assertOk();
});
