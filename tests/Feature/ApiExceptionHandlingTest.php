<?php

namespace Tests\Feature;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ApiExceptionHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // POST routes: the SPA catch-all in routes/web.php is GET-only, so
        // POST avoids being shadowed by it.
        Route::post('/_test/validation', function () {
            throw ValidationException::withMessages([
                'Name' => ['The Name field is required.'],
            ]);
        });

        Route::post('/_test/auth', function () {
            throw new AuthenticationException();
        });

        Route::post('/_test/boom', function () {
            throw new RuntimeException('Low level failure detail');
        });

        Route::post('/_test/not-found', function () {
            abort(404);
        });
    }

    public function test_validation_exception_returns_error_envelope_with_field_errors(): void
    {
        $this->postJson('/_test/validation')
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonPath('errors.Name.0', 'The Name field is required.');
    }

    public function test_authentication_exception_returns_401_envelope(): void
    {
        $this->postJson('/_test/auth')
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_unexpected_exception_is_generic_when_debug_off(): void
    {
        config(['app.debug' => false]);

        $response = $this->postJson('/_test/boom')->assertStatus(500);

        $response->assertJson([
            'success' => false,
            'message' => 'Server Error.',
        ]);
        $response->assertJsonMissing(['message' => 'Low level failure detail']);
        $this->assertArrayNotHasKey('debug', $response->json());
    }

    public function test_unexpected_exception_exposes_detail_when_debug_on(): void
    {
        config(['app.debug' => true]);

        $response = $this->postJson('/_test/boom')->assertStatus(500);

        $response->assertJson([
            'success' => false,
            'message' => 'Low level failure detail',
        ]);
        $this->assertSame(RuntimeException::class, $response->json('debug.exception'));
    }

    public function test_non_json_request_falls_through_to_default_handler(): void
    {
        $response = $this->post('/_test/not-found');

        $response->assertStatus(404);
        $this->assertStringNotContainsString(
            'application/json',
            (string)$response->headers->get('Content-Type')
        );
    }
}
