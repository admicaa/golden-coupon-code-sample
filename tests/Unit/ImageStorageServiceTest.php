<?php

namespace Tests\Unit;

use App\Services\Media\ImageStorageService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageStorageServiceTest extends TestCase
{
    public function test_store_uploaded_image_returns_public_payload_and_delete_removes_the_file()
    {
        $service = $this->app->make(ImageStorageService::class);
        $payload = $service->storeUploadedImage(
            UploadedFile::fake()->image('service-logo.jpg'),
            'tests/images'
        );

        $this->assertArrayHasKey('storage_path', $payload);
        $this->assertArrayHasKey('path', $payload);
        $this->assertArrayHasKey('image_path', $payload);
        $this->assertStringStartsWith('/storage/tests/images/', $payload['path']);
        $this->assertFileExists(storage_path('app/' . $payload['storage_path']));

        $service->deleteStoredPath($payload['storage_path']);

        $this->assertFileDoesNotExist(storage_path('app/' . $payload['storage_path']));
    }
}
