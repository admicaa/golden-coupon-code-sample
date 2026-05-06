<?php

namespace Tests\Feature;

use App\Models\Link;
use Tests\Concerns\RefreshMySqlDatabase;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

class LinksApiTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshMySqlDatabase;

    public function test_admin_can_save_a_nested_link_tree()
    {
        $this->actingAsAdminWithPermissions(['view-main-page', 'edit-main-page']);

        $response = $this->postJson('/api/links', [
            'links' => [
                [
                    'id' => 0,
                    'url' => '/parent',
                    'pages' => [
                        'GB' => ['name' => 'Parent'],
                        'AR' => ['name' => 'الرئيسية'],
                    ],
                    'links' => [
                        [
                            'id' => 0,
                            'url' => '/parent/child',
                            'pages' => [
                                'GB' => ['name' => 'Child'],
                                'AR' => ['name' => 'الطفل'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('0.link', '/parent')
            ->assertJsonPath('0.links.0.link', '/parent/child');

        $parent = Link::where('link', '/parent')->firstOrFail();

        $this->assertDatabaseHas('links', [
            'id' => $parent->id,
            'name__GB' => 'Parent',
            'name__AR' => 'الرئيسية',
            'link_id' => null,
        ]);
        $this->assertDatabaseHas('links', [
            'link' => '/parent/child',
            'name__GB' => 'Child',
            'name__AR' => 'الطفل',
            'link_id' => $parent->id,
        ]);
    }
}
