<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\AiAnalysisService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected $aiService;

    public function __construct(AiAnalysisService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display a listing of reviews
     */
    public function index(Request $request)
    {
        $query = Review::with('user:id,name');

        // If user is not admin, only show their own reviews
        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        // Filter by sentiment
        if ($request->has('sentiment') && $request->sentiment !== 'all') {
            $query->where('sentiment', $request->sentiment);
        }

        // Search in content
        if ($request->has('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        $reviews = $query->latest()->paginate(10);

        return response()->json($reviews);
    }

    /**
     * Store a newly created review
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|min:10',
        ]);

        // Analyze the review with AI
        $analysis = $this->aiService->analyzeReview($request->content);

        // Create review with AI analysis
        $review = Review::create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
            'sentiment' => $analysis['sentiment'],
            'score' => $analysis['score'],
            'topics' => $analysis['topics'],
        ]);

        $review->load('user:id,name');

        return response()->json([
            'message' => 'Review created and analyzed successfully',
            'review' => $review
        ], 201);
    }

    /**
     * Display a specific review
     */
    public function show(Request $request, Review $review)
    {
        // Check if user can view this review
        if ($request->user()->role !== 'admin' && $review->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $review->load('user:id,name,email');
        return response()->json($review);
    }

    /**
     * Update a review
     */
    public function update(Request $request, Review $review)
    {
        // Check if user owns the review or is admin
        if ($review->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'content' => 'required|string|min:10',
        ]);

        // Re-analyze if content changed
        if ($request->content !== $review->content) {
            $analysis = $this->aiService->analyzeReview($request->content);
            
            $review->update([
                'content' => $request->content,
                'sentiment' => $analysis['sentiment'],
                'score' => $analysis['score'],
                'topics' => $analysis['topics'],
            ]);
        }

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review
        ]);
    }

    /**
     * Remove a review
     */
    public function destroy(Request $request, Review $review)
    {
        // Check if user owns the review or is admin
        if ($review->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully'
        ]);
    }
}
