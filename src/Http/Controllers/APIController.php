<?php

namespace Seodably\SEO\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Seodably\SEO\Models\SEO;
use Seodably\SEO\Models\Analysis;
use Seodably\SEO\Services\SEOService;
use Seodably\SEO\Services\AIService;

class APIController extends Controller
{
    /**
     * Le service SEO.
     *
     * @var SEOService
     */
    protected $seoService;

    /**
     * Le service d'IA.
     *
     * @var AIService
     */
    protected $aiService;

    /**
     * Crée une nouvelle instance du contrôleur.
     *
     * @param SEOService $seoService
     * @param AIService $aiService
     */
    public function __construct(SEOService $seoService, AIService $aiService)
    {
        $this->seoService = $seoService;
        $this->aiService = $aiService;

        // Appliquer le middleware d'authentification si requis
        if (config('seo.api.require_auth', true)) {
            $this->middleware('auth:sanctum');
        }

        // Appliquer le middleware de limitation de taux
        $this->middleware('throttle:' . config('seo.api.rate_limit', 60));
    }

    /**
     * Récupère les métadonnées SEO pour une URL.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetadata(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $url = $request->input('url');
        $seo = SEO::findByUrl($url);

        if (!$seo) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune métadonnée SEO trouvée pour cette URL.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $seo,
        ]);
    }

    /**
     * Met à jour les métadonnées SEO pour une URL.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMetadata(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'title' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:160',
            'keywords' => 'nullable|string',
            'canonical' => 'nullable|url',
            'robots' => 'nullable|string',
            'og_title' => 'nullable|string|max:60',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|url',
            'og_type' => 'nullable|string',
            'twitter_card' => 'nullable|string',
            'twitter_title' => 'nullable|string|max:60',
            'twitter_description' => 'nullable|string|max:160',
            'twitter_image' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $url = $request->input('url');
        $seo = SEO::findByUrl($url);

        if (!$seo) {
            // Créer une nouvelle entrée
            $seo = new SEO(['url' => $url]);
        }

        // Mettre à jour les champs
        $fields = [
            'title', 'description', 'keywords', 'canonical', 'robots',
            'og_title', 'og_description', 'og_image', 'og_type',
            'twitter_card', 'twitter_title', 'twitter_description', 'twitter_image',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $seo->$field = $request->input($field);
            }
        }

        $seo->save();

        return response()->json([
            'success' => true,
            'data' => $seo,
            'message' => 'Métadonnées SEO mises à jour avec succès.',
        ]);
    }

    /**
     * Récupère les analyses SEO pour une URL.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnalysis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $url = $request->input('url');
        $analysis = Analysis::getLatestByUrl($url);

        if (!$analysis) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune analyse SEO trouvée pour cette URL.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }

    /**
     * Récupère des suggestions d'IA pour une URL.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSuggestions(Request $request)
    {
        if (!config('seo.ai.enabled', true) || !$this->aiService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Le service d\'IA n\'est pas activé.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $url = $request->input('url');
        $content = $request->input('content');
        $seo = SEO::findByUrl($url);

        // Générer des suggestions
        $suggestions = [
            'title' => $this->aiService->generateTitleSuggestions($content, $seo ? $seo->title : null),
            'description' => $this->aiService->generateDescriptionSuggestions($content, $seo ? $seo->description : null),
            'keywords' => $this->aiService->generateKeywordsSuggestions($content, $seo ? $seo->keywords : null),
        ];

        // Analyser le contenu
        $analysis = $this->aiService->analyzeContent($content, $url, $seo);

        return response()->json([
            'success' => true,
            'data' => [
                'suggestions' => $suggestions,
                'analysis' => $analysis,
            ],
        ]);
    }

    /**
     * Prédit le score SEO pour un contenu.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function predictScore(Request $request)
    {
        if (!config('seo.ai.enabled', true) || !$this->aiService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Le service d\'IA n\'est pas activé.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $content = $request->input('content');
        $meta = $request->only(['title', 'description', 'keywords']);

        $prediction = $this->aiService->predictSEOScore($content, $meta);

        return response()->json([
            'success' => true,
            'data' => $prediction,
        ]);
    }
} 