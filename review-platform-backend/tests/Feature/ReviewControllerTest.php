<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can create a review
     */
    public function test_user_can_create_review(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/reviews', [
                             'content' => 'Excellent produit, très satisfait de mon achat !'
                         ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'review' => [
                         'id', 'user_id', 'content', 'sentiment', 
                         'score', 'topics', 'created_at', 'updated_at'
                     ]
                 ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'content' => 'Excellent produit, très satisfait de mon achat !'
        ]);
    }

    /**
     * Test review creation validates minimum content length
     */
    public function test_review_creation_validates_minimum_content_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/reviews', [
                             'content' => 'Court'
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['content']);
    }

    /**
     * Test user can list their own reviews
     */
    public function test_user_can_list_their_own_reviews(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Review::factory()->count(3)->create(['user_id' => $user->id]);
        Review::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/reviews');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /**
     * Test admin can see all reviews
     */
    public function test_admin_can_see_all_reviews(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        Review::factory()->count(3)->create(['user_id' => $user->id]);
        Review::factory()->count(2)->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
                         ->getJson('/api/reviews');

        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data');
    }

    /**
     * Test reviews can be filtered by sentiment
     */
    public function test_reviews_can_be_filtered_by_sentiment(): void
    {
        $user = User::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'sentiment' => 'positive'
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'sentiment' => 'negative'
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/reviews?sentiment=positive');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    /**
     * Test reviews can be searched by content
     */
    public function test_reviews_can_be_searched_by_content(): void
    {
        $user = User::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'content' => 'Excellent produit'
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'content' => 'Mauvais service'
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/reviews?search=Excellent');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    /**
     * Test user can view their own review
     */
    public function test_user_can_view_their_own_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $review->id,
                     'content' => $review->content
                 ]);
    }

    /**
     * Test user cannot view other users reviews
     */
    public function test_user_cannot_view_other_users_reviews(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1, 'sanctum')
                         ->getJson("/api/reviews/{$review->id}");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Unauthorized']);
    }

    /**
     * Test admin can view any review
     */
    public function test_admin_can_view_any_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin, 'sanctum')
                         ->getJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $review->id
                 ]);
    }

    /**
     * Test user can update their own review
     */
    public function test_user_can_update_their_own_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
                         ->putJson("/api/reviews/{$review->id}", [
                             'content' => 'Produit correct mais prix un peu élevé'
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Review updated successfully'
                 ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Produit correct mais prix un peu élevé'
        ]);
    }

    /**
     * Test user cannot update other users reviews
     */
    public function test_user_cannot_update_other_users_reviews(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1, 'sanctum')
                         ->putJson("/api/reviews/{$review->id}", [
                             'content' => 'Updated content'
                         ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin can update any review
     */
    public function test_admin_can_update_any_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin, 'sanctum')
                         ->putJson("/api/reviews/{$review->id}", [
                             'content' => 'Admin updated this review'
                         ]);

        $response->assertStatus(200);
    }

    /**
     * Test user can delete their own review
     */
    public function test_user_can_delete_their_own_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
                         ->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Review deleted successfully']);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id
        ]);
    }

    /**
     * Test user cannot delete other users reviews
     */
    public function test_user_cannot_delete_other_users_reviews(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1, 'sanctum')
                         ->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    /**
     * Test admin can delete any review
     */
    public function test_admin_can_delete_any_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin, 'sanctum')
                         ->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id
        ]);
    }

    /**
     * Test unauthenticated user cannot create review
     */
    public function test_unauthenticated_user_cannot_create_review(): void
    {
        $response = $this->postJson('/api/reviews', [
            'content' => 'This should fail'
        ]);

        $response->assertStatus(401);
    }
}
