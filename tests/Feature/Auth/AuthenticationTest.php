<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('login screen can be rendered', function () {
    $response = $this->get('/auth/login'); // Assuming Folio route renders this page
    $response
        ->assertOk()
        ->assertSee('<form', false)                       // Check if a form is present
        ->assertSee('wire:model="form.email"', false)     // Check the email field has wire:model
        ->assertSee('wire:model="form.password"', false); // Corrected from wire:name to wire:model

});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $component = Volt::test('login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $component = Volt::test('login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get('/logout');

    $this->assertGuest();
});
