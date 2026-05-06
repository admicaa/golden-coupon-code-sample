<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Article;
use App\Models\Languages;
use App\Models\Permission;
use Tests\Concerns\RefreshMySqlDatabase;
use Laravel\Passport\Passport;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshMySqlDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Languages::insert([
            ['name' => 'English', 'shortcut' => 'GB'],
            ['name' => 'Arabic', 'shortcut' => 'AR'],
        ]);
    }

    public function test_authorized_admin_can_list_articles()
    {
        $this->actingAsAdminWithPermissions(['view-articles']);
        $article = $this->createArticle();

        $response = $this->getJson('/api/articles?body=1');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $article->id)
            ->assertJsonPath('data.0.page.name', 'Original Article')
            ->assertJsonPath('data.0.page.description', 'Original description');
    }

    public function test_authorized_admin_can_show_article()
    {
        $this->actingAsAdminWithPermissions(['view-articles']);
        $article = $this->createArticle();

        $response = $this->getJson('/api/articles/' . $article->id . '?body=1');

        $response->assertOk()
            ->assertJsonPath('id', $article->id)
            ->assertJsonPath('page.name', 'Original Article')
            ->assertJsonPath('page.description', 'Original description');
    }

    public function test_authorized_admin_can_create_article_via_rest_route()
    {
        $this->actingAsAdminWithPermissions(['create-articles']);

        $response = $this->postJson('/api/articles', [
            'name' => 'Spring Offers',
            'body' => 'Fresh discounts',
        ]);

        $articleId = $response->json('id');

        $response->assertOk()
            ->assertJsonPath('page.name', 'Spring Offers')
            ->assertJsonPath('page.title', 'Spring Offers')
            ->assertJsonPath('page.slug', 'spring-offers')
            ->assertJsonPath('page.description', 'Fresh discounts');

        $this->assertDatabaseHas('articles', ['id' => $articleId]);
        $this->assertDatabaseHas('article_pages', [
            'article_id' => $articleId,
            'language' => 'GB',
            'name' => 'Spring Offers',
            'title' => 'Spring Offers',
            'slug' => 'spring-offers',
            'description' => 'Fresh discounts',
        ]);
    }

    public function test_authorized_admin_can_update_article_via_rest_route()
    {
        $this->actingAsAdminWithPermissions(['edit-articles']);
        $article = $this->createArticle();

        $response = $this->putJson('/api/articles/' . $article->id, [
            'name' => 'Renamed Article',
            'body' => 'Updated description',
        ]);

        $response->assertOk()
            ->assertJsonPath('page.name', 'Renamed Article')
            ->assertJsonPath('page.title', 'Renamed Article')
            ->assertJsonPath('page.slug', 'original-article')
            ->assertJsonPath('page.description', 'Updated description');

        $this->assertDatabaseHas('article_pages', [
            'article_id' => $article->id,
            'language' => 'GB',
            'name' => 'Renamed Article',
            'title' => 'Renamed Article',
            'slug' => 'original-article',
            'description' => 'Updated description',
        ]);
    }

    public function test_authorized_admin_can_delete_article_via_rest_route()
    {
        $this->actingAsAdminWithPermissions(['delete-articles']);
        $article = $this->createArticle();

        $response = $this->deleteJson('/api/articles/' . $article->id);

        $response->assertOk();
        $this->assertSame((string) $article->id, $response->getContent());

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_unauthorized_admin_cannot_create_article()
    {
        $this->actingAsAdminWithPermissions([]);

        $response = $this->postJson('/api/articles', [
            'name' => 'Blocked Article',
            'body' => 'No permission',
        ]);

        $response->assertStatus(403);
    }

    public function test_invalid_article_payload_returns_validation_errors()
    {
        $this->actingAsAdminWithPermissions(['create-articles']);

        $response = $this->postJson('/api/articles', [
            'body' => 'Missing article name',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    protected function actingAsAdminWithPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }

        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin-' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
        ]);

        if (!empty($permissions)) {
            $admin->givePermissionTo($permissions);
        }

        Passport::actingAs($admin, [], 'admin');

        return $admin;
    }

    protected function createArticle()
    {
        $article = Article::create();

        $article->pages()->create([
            'language' => 'GB',
            'name' => 'Original Article',
            'title' => 'Original Article',
            'slug' => 'original-article',
            'description' => 'Original description',
        ]);

        $article->pages()->create([
            'language' => 'AR',
            'name' => 'Original Article',
            'title' => 'Original Article',
            'slug' => 'original-article-AR',
            'description' => 'Original description',
        ]);

        return $article;
    }
}
