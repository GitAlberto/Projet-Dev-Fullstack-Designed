<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiAnalysisService;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    protected $aiService;

    public function __construct(AiAnalysisService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Analyze a text (manual analysis endpoint)
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:10',
        ]);

        $analysis = $this->aiService->analyzeReview($request->text);

        return response()->json($analysis);
    }
}
