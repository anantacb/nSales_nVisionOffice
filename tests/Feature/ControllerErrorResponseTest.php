<?php

namespace Tests\Feature;

use App\Contracts\ServiceDto;
use App\Transformer\ApiResponseTransformer;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ControllerErrorResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // POST routes (GET {any} SPA catch-all in web.php would otherwise shadow them).
        Route::post('/_test/respond/{status}', function (int $status) {
            $dto = new ServiceDto('msg-' . $status, $status, ['k' => 'v']);
            return ApiResponseTransformer::respond($dto);
        });
    }

    public function test_error_status_dto_returns_error_envelope(): void
    {
        $this->postJson('/_test/respond/422')
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'msg-422',
            ]);
    }

    public function test_server_error_status_dto_returns_error_envelope(): void
    {
        $this->postJson('/_test/respond/500')
            ->assertStatus(500)
            ->assertJson(['success' => false, 'message' => 'msg-500']);
    }

    public function test_ok_status_dto_returns_success_envelope_with_data(): void
    {
        $this->postJson('/_test/respond/200')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'msg-200',
                'data' => ['k' => 'v'],
            ]);
    }

    public function test_created_status_dto_stays_success(): void
    {
        $this->postJson('/_test/respond/201')
            ->assertStatus(201)
            ->assertJson(['success' => true]);
    }
}
