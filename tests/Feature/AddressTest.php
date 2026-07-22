<?php

use App\Models\Address;
use App\Models\User;

test('guests are sent to login', function () {
    $this->get(route('addresses.index'))->assertRedirect(route('login'));
});

test('the first address a customer saves automatically becomes the default', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('addresses.store'), [
            'recipient_name' => 'Dara Sok',
            'phone' => '012345678',
            'street_line' => 'St 271',
            'city' => 'Phnom Penh',
        ])
        ->assertCreated()
        ->assertJsonPath('data.is_default', true);
});

test('marking a new address as default un-defaults the others', function () {
    $user = User::factory()->create();
    $first = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

    $this->actingAs($user)
        ->postJson(route('addresses.store'), [
            'recipient_name' => 'Lina Chan',
            'phone' => '012999888',
            'street_line' => 'St 63',
            'city' => 'Phnom Penh',
            'is_default' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.is_default', true);

    expect($first->fresh()->is_default)->toBeFalse();
});

test('the default action flips is_default onto exactly one address', function () {
    $user = User::factory()->create();
    $first = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);
    $second = Address::factory()->create(['user_id' => $user->id, 'is_default' => false]);

    $this->actingAs($user)->postJson(route('addresses.default', $second))
        ->assertOk()
        ->assertJsonPath('data.is_default', true);

    expect($first->fresh()->is_default)->toBeFalse()
        ->and($second->fresh()->is_default)->toBeTrue();
});

test('an owner can update and delete their own address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->putJson(route('addresses.update', $address), ['city' => 'Siem Reap'])
        ->assertOk()
        ->assertJsonPath('data.city', 'Siem Reap');

    $this->actingAs($user)->deleteJson(route('addresses.destroy', $address))->assertNoContent();
    expect(Address::find($address->id))->toBeNull();
});

test('another customer cannot see, update, or delete someone else\'s address', function () {
    $owner = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $owner->id]);
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->getJson(route('addresses.index'))
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->actingAs($stranger)->putJson(route('addresses.update', $address), ['city' => 'Hacked'])->assertNotFound();
    $this->actingAs($stranger)->deleteJson(route('addresses.destroy', $address))->assertNotFound();
});
