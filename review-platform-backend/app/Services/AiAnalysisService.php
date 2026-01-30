<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Exception;

class AiAnalysisService
{
    /**
     * Analyze review text using OpenAI API
     * 
     * @param string $text
     * @return array
     */
    public function analyzeReview(string $text): array
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a sentiment analysis expert. You can analyze customer reviews in both French and English. Always respond in JSON format.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Analyze this customer review (in French or English) and provide a JSON response with the following structure:
{
  \"sentiment\": \"positive|neutral|negative\",
  \"score\": <number between 0-100>,
  \"topics\": [\"topic1\", \"topic2\", \"topic3\"]
}

Review: {$text}

Respond ONLY with valid JSON, no additional text. Detect topics in the same language as the review."
                    ]
                ],
                'temperature' => 0.3,
            ]);

            $content = $response->choices[0]->message->content;
            
            // Parse JSON response
            $analysis = json_decode($content, true);
            
            if (!$analysis || !isset($analysis['sentiment'])) {
                throw new Exception('Invalid AI response format');
            }

            // Validate sentiment value
            $validSentiments = ['positive', 'neutral', 'negative'];
            if (!in_array($analysis['sentiment'], $validSentiments)) {
                $analysis['sentiment'] = 'neutral';
            }

            // Ensure score is between 0-100
            $analysis['score'] = max(0, min(100, intval($analysis['score'])));

            // Ensure topics is an array
            if (!isset($analysis['topics']) || !is_array($analysis['topics'])) {
                $analysis['topics'] = [];
            }

            return $analysis;

        } catch (Exception $e) {
            // Fallback to simple rule-based analysis
            return $this->fallbackAnalysis($text);
        }
    }

    /**
     * Fallback analysis using simple keyword matching
     * 
     * @param string $text
     * @return array
     */
    private function fallbackAnalysis(string $text): array
    {
        $text = strtolower($text);
        
        // Simple keyword lists (English + French)
        $positiveWords = [
            'excellent', 'great', 'good', 'amazing', 'fantastic', 'love', 'perfect', 'best', 'wonderful', 'awesome',
            'super', 'magnifique', 'parfait', 'génial', 'formidable', 'extraordinaire', 'top', 'bien'
        ];
        $negativeWords = [
            'bad', 'terrible', 'awful', 'horrible', 'worst', 'hate', 'poor', 'disappointing', 'useless', 'slow',
            'mauvais', 'nul', 'horrible', 'catastrophique', 'décevant', 'médiocre', 'lent'
        ];
        
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach ($positiveWords as $word) {
            $positiveCount += substr_count($text, $word);
        }
        
        foreach ($negativeWords as $word) {
            $negativeCount += substr_count($text, $word);
        }
        
        // Determine sentiment
        if ($positiveCount > $negativeCount) {
            $sentiment = 'positive';
            $score = min(100, 60 + ($positiveCount * 10));
        } elseif ($negativeCount > $positiveCount) {
            $sentiment = 'negative';
            $score = max(0, 40 - ($negativeCount * 10));
        } else {
            $sentiment = 'neutral';
            $score = 50;
        }
        
        // Extract simple topics (French + English)
        $topics = [];
        if (str_contains($text, 'delivery') || str_contains($text, 'shipping') || str_contains($text, 'livraison')) {
            $topics[] = 'livraison';
        }
        if (str_contains($text, 'quality') || str_contains($text, 'product') || str_contains($text, 'qualité') || str_contains($text, 'produit')) {
            $topics[] = 'qualité';
        }
        if (str_contains($text, 'price') || str_contains($text, 'cost') || str_contains($text, 'expensive') || str_contains($text, 'prix') || str_contains($text, 'cher')) {
            $topics[] = 'prix';
        }
        if (str_contains($text, 'service') || str_contains($text, 'support')) {
            $topics[] = 'service';
        }
        
        return [
            'sentiment' => $sentiment,
            'score' => $score,
            'topics' => $topics
        ];
    }
}
