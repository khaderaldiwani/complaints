<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ThrottleJsonResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_throttle_returns_json_when_route_is_api()
    {
        $this->app['router']->get('api/test-throttle', function () {
            throw new \Illuminate\Http\Exceptions\ThrottleRequestsException('Too many attempts.');
        });

        $response = $this->get('/api/test-throttle');

        $response->assertStatus(429)
                 ->assertJson(['message' => 'Too many attempts.']);
    }
}
