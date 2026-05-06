<?php

namespace Tests\Feature;

use App\Models\Languages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

class TranslationFilesApiTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    protected $sourceDirectory;
    protected $sourceFile;
    protected $overrideFile;

    protected function setUp(): void
    {
        parent::setUp();

        Languages::insert([
            ['name' => 'English', 'shortcut' => 'GB'],
            ['name' => 'Arabic', 'shortcut' => 'AR'],
        ]);

        $this->sourceDirectory = resource_path('langMain/en/pages/tests');
        $this->sourceFile = $this->sourceDirectory . '/security.php';
        $this->overrideFile = storage_path('app/translations/GB/pages/tests/security.json');

        if (!File::isDirectory($this->sourceDirectory)) {
            File::makeDirectory($this->sourceDirectory, 0755, true);
        }

        File::put($this->sourceFile, "<?php return ['greeting' => 'Hello', 'nested' => ['title' => 'Welcome']];\n");
    }

    protected function tearDown(): void
    {
        File::delete($this->sourceFile);
        if (File::isDirectory($this->sourceDirectory)) {
            @rmdir($this->sourceDirectory);
        }

        File::delete($this->overrideFile);
        if (File::isDirectory(dirname($this->overrideFile))) {
            @rmdir(dirname($this->overrideFile));
        }
        if (File::isDirectory(dirname(dirname($this->overrideFile)))) {
            @rmdir(dirname(dirname($this->overrideFile)));
        }
        if (File::isDirectory(dirname(dirname(dirname($this->overrideFile))))) {
            @rmdir(dirname(dirname(dirname($this->overrideFile))));
        }

        parent::tearDown();
    }

    public function test_translation_save_writes_json_override_without_executable_php()
    {
        $this->actingAsAdminWithPermissions(['translate-GB']);

        $response = $this->postJson('/api/translation', [
            'language' => 'GB',
            'file' => 'tests/security.php',
            'data' => [
                'greeting' => "<?php echo 'owned'; ?>",
                'nested' => ['title' => 'Updated title'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('greeting', "<?php echo 'owned'; ?>")
            ->assertJsonPath('nested.title', 'Updated title');

        $this->assertFileExists($this->overrideFile);
        $this->assertStringNotContainsString('<?php', File::get($this->overrideFile));
        $this->assertSame(
            "<?php return ['greeting' => 'Hello', 'nested' => ['title' => 'Welcome']];\n",
            File::get($this->sourceFile)
        );
    }

    public function test_translation_get_merges_saved_override_with_base_content()
    {
        $this->actingAsAdminWithPermissions(['translate-GB']);

        $this->postJson('/api/translation', [
            'language' => 'GB',
            'file' => 'tests/security.php',
            'data' => [
                'greeting' => 'Hola',
                'nested' => ['title' => 'Bienvenido'],
            ],
        ])->assertOk();

        $response = $this->getJson('/api/translation?language=GB&file=tests/security.php');

        $response->assertOk()
            ->assertJsonPath('greeting', 'Hola')
            ->assertJsonPath('nested.title', 'Bienvenido');
    }
}
