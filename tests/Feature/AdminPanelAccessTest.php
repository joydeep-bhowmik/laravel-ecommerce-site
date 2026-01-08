<?php

use App\Models\User;

test('only admin can access admin panel', function () {

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertStatus(200);
});

test('non admin can\'t access admin panel', function () {

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertStatus(403);
});
