<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $totalReviews = Review::count();
        
        if ($totalReviews === 0) {
            return [
                'total_reviews' => 0,
                'positive_percentage' => 0,
                'neutral_percentage' => 0,
                'negative_percentage' => 0,
                'average_score' => 0,
                'top_topics' => [],
                'recent_reviews' => []
            ];
        }

        // Calculate sentiment percentages
        $sentimentCounts = Review::select('sentiment', DB::raw('count(*) as count'))
            ->whereNotNull('sentiment')
            ->groupBy('sentiment')
            ->pluck('count', 'sentiment');

        $positiveCount = $sentimentCounts['positive'] ?? 0;
        $neutralCount = $sentimentCounts['neutral'] ?? 0;
        $negativeCount = $sentimentCounts['negative'] ?? 0;
        $analyzedCount = $positiveCount + $neutralCount + $negativeCount;

        // Calculate average score
        $averageScore = Review::whereNotNull('score')->avg('score') ?? 0;

        // Get top 3 topics
        $topTopics = $this->getTopTopics();

        // Get 5 most recent reviews
        $recentReviews = Review::with('user:id,name')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'content' => substr($review->content, 0, 100) . (strlen($review->content) > 100 ? '...' : ''),
                    'sentiment' => $review->sentiment,
                    'score' => $review->score,
                    'user_name' => $review->user->name ?? 'Unknown',
                    'created_at' => $review->created_at->format('Y-m-d H:i')
                ];
            });

        return [
            'total_reviews' => $totalReviews,
            'positive_percentage' => $analyzedCount > 0 ? round(($positiveCount / $analyzedCount) * 100, 1) : 0,
            'neutral_percentage' => $analyzedCount > 0 ? round(($neutralCount / $analyzedCount) * 100, 1) : 0,
            'negative_percentage' => $analyzedCount > 0 ? round(($negativeCount / $analyzedCount) * 100, 1) : 0,
            'average_score' => round($averageScore, 1),
            'top_topics' => $topTopics,
            'recent_reviews' => $recentReviews
        ];
    }

    /**
     * Get the top 3 most frequent topics
     * 
     * @return array
     */
    private function getTopTopics(): array
    {
        $reviews = Review::whereNotNull('topics')->get();
        $topicCounts = [];

        foreach ($reviews as $review) {
            $topics = $review->topics;
            if (is_array($topics)) {
                foreach ($topics as $topic) {
                    if (!isset($topicCounts[$topic])) {
                        $topicCounts[$topic] = 0;
                    }
                    $topicCounts[$topic]++;
                }
            }
        }

        arsort($topicCounts);
        
        return array_map(function ($topic, $count) {
            return [
                'topic' => $topic,
                'count' => $count
            ];
        }, array_keys(array_slice($topicCounts, 0, 3, true)), array_slice($topicCounts, 0, 3, true));
    }
}
