<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can access dashboard stats
     */
    public function test_admin_can_access_dashboard_stats(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Create some test data
        Review::factory()->count(5)->create(['sentiment' => 'positive', 'score' => 90]);
        Review::factory()->count(3)->create(['sentiment' => 'neutral', 'score' => 50]);
        Review::factory()->count(2)->create(['sentiment' => 'negative', 'score' => 20]);

        $response = $this->actingAs($admin, 'sanctum')
                         ->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'stats' => [
                         'total_reviews',
                         'avg_score',
                         'sentiment_distribution',
                         'top_topics',
                         'recent_reviews'
                     ]
                 ]);
    }

    /**
     * Test regular user cannot access dashboard
     */
    public function test_regular_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/dashboard/stats');

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Unauthorized']);
    }

    /**
     * Test unauthenticated user cannot access dashboard
     */
    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(401);
    }

    /**
     * Test dashboard returns correct statistics
     */
    public function test_dashboard_returns_correct_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Create reviews with known data
        Review::factory()->create([
            'sentiment' => 'positive',
            'score' => 90,
            'topics' => ['qualité', 'livraison']
        ]);
        Review::factory()->create([
            'sentiment' => 'positive',
            'score' => 80,
            'topics' => ['qualité', 'prix']
        ]);
        Review::factory()->create([
            'sentiment' => 'negative',
            'score' => 30,
            'topics' => ['service']
        ]);

        $response = $this->actingAs($admin, 'sanctum')
                         ->getJson('/api/dashboard/stats');

        $response->assertStatus(200);
        
        $stats = $response->json('stats');
        
        // Verify total reviews
        $this->assertEquals(3, $stats['total_reviews']);
        
        // Verify sentiment distribution
        $this->assertEquals(2, $stats['sentiment_distribution']['positive']);
        $this->assertEquals(1, $stats['sentiment_distribution']['negative']);
        
        // Verify average score (90 + 80 + 30) / 3 = 66.67
        $this->assertEqualsWithDelta(66.67, $stats['avg_score'], 0.5);
    }
}
