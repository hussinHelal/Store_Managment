<?php

namespace Tests\Unit;

use App\Services\PictureStorageService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PictureStorageServiceTest extends TestCase
{
    public function test_media_urls_use_the_configured_disk(): void
    {
        config([
            'filesystems.media_disk' => 'deployment_media',
            'filesystems.disks.deployment_media' => [
                'driver' => 'local',
                'root' => storage_path('framework/testing/deployment-media-unused'),
                'url' => 'https://media.example.test',
                'visibility' => 'public',
            ],
        ]);

        $url = app(PictureStorageService::class)->url('products/device.png', 'products');

        $this->assertSame('https://media.example.test/products/device.png', $url);
    }

    public function test_delete_removes_files_from_the_configured_disk(): void
    {
        Storage::fake('deployment_media');
        config(['filesystems.media_disk' => 'deployment_media']);
        Storage::disk('deployment_media')->put('products/device.png', 'image');
        Storage::disk('deployment_media')->put('products/thumbnails/device.png', 'thumbnail');

        app(PictureStorageService::class)->delete('products/device.png', 'products');

        Storage::disk('deployment_media')->assertMissing('products/device.png');
        Storage::disk('deployment_media')->assertMissing('products/thumbnails/device.png');
    }
}
