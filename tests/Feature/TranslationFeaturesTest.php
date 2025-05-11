<?php

namespace Tests\Feature;

use App\Models\Translation;
use App\Models\User;
use App\Models\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class TranslationFeaturesTest extends TestCase
{
    use WithFaker;
    protected $token;


    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for testing
        $user = User::factory()->create();

        // Generate a token for the user
        $this->token = $user->createToken('Test App')->plainTextToken;
    }

    public function test_user_can_create_translation()
    {
        $locale = Locale::first();

        $translationData = [
            'key' => 'hello',
            'translations' => [
                [
                    'locale' => $locale->code,
                    'content' => 'Hola',
                    'tags' => [1, 2], // Optional tags, assuming they exist
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/translations/create', $translationData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Translations created successfully.',
            ]);

        // Check if translation is added to the database
        $this->assertDatabaseHas('translations', [
            'key' => 'hello',
            'content' => 'Hola',
        ]);
    }


    public function test_user_can_view_translation()
    {
        $translation = Translation::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/' . $translation->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $translation->id,
                'key' => $translation->key,
                'content' => $translation->content,
            ]);
    }

    public function test_user_can_update_translation()
    {
        $translation = Translation::factory()->create();

        $updateData = [
            'content' => 'Updated Content',
            'tags' => [1],  // Assuming tag ID 1 exists
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/translations/' . $translation->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Translation updated successfully.',
            ]);

        $this->assertDatabaseHas('translations', [
            'id' => $translation->id,
            'content' => 'Updated Content',
        ]);
    }

    public function test_user_can_search_translations()
    {
        $locale = Locale::first();
        $searchQuery = 'Hello';

        // Create a translation to be searched
        Translation::factory()->create([
            'key' => 'hello',
            'content' => 'Hello!',
            'locale_id' => $locale->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/search?query=' . $searchQuery . '&locale=' . $locale->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'key' => 'hello',
                'content' => 'Hello!',  // Adjusted to match actual content
            ]);
    }

    public function test_user_can_export_translations()
    {
        $locale = Locale::first(); // Use a valid locale for the test

        // Create a translation for testing
        Translation::factory()->create([
            'locale_id' => $locale->id,
            'content' => 'Hola',
            'key' => 'hello',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/export?locale=' . $locale->code);

        $response->assertStatus(200);

        // Ensure the response is an array
        $responseData = $response->json();
        $this->assertIsArray($responseData); // Check if the response is an array
        $this->assertNotEmpty($responseData); // Ensure the response is not empty

        // Assert the structure: check if the key exists
        $this->assertArrayHasKey('hello', $responseData); // Check if 'hello' key exists
        $this->assertEquals('Hola', $responseData['hello']); // Check if the content is 'Hola'
    }



    public function test_create_translation_performance()
    {
        $locale = Locale::first();

        $translationData = [
            'key' => 'performance_test_key',
            'translations' => [
                [
                    'locale' => $locale->code,
                    'content' => 'Performance test',
                    'tags' => [1],
                ]
            ]
        ];

        $startTime = microtime(true);

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/translations/create', $translationData);

        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        $this->assertLessThan(0.5, $executionTime, 'Create translation took too long!');
    }

    public function test_search_translation_performance()
    {
        $locale = Locale::first();
        $searchQuery = 'Hello';

        Translation::factory()->create([
            'key' => 'hello',
            'content' => 'Hello',
            'locale_id' => $locale->id,
        ]);

        $startTime = microtime(true);

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/search?query=' . $searchQuery . '&locale=' . $locale->id);

        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        $this->assertLessThan(0.5, $executionTime, 'Search translation took too long!');
    }

    public function test_export_translation_performance()
    {
        $locale = Locale::first();

        Translation::factory()->create([
            'locale_id' => $locale->id,
            'content' => 'Hola',
            'key' => 'hello',
        ]);

        $startTime = microtime(true);

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/export?locale=' . $locale->code);

        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        $this->assertLessThan(0.5, $executionTime, 'Export translations took too long!');
    }
}
