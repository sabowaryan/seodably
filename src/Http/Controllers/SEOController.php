<?php

namespace Seodably\SEO\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Seodably\SEO\Models\SEO;
use Seodably\SEO\Models\Analysis;
use Seodably\SEO\Services\SEOService;
use Seodably\SEO\Services\AIService;
use Seodably\SEO\Http\Requests\SEORequest;

class SEOController extends Controller
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
    }

    /**
     * Affiche le tableau de bord SEO.
     *
     * @return \Inertia\Response
     */
    public function dashboard()
    {
        $seoEntries = SEO::with('latestAnalysis')
            ->orderBy('last_analyzed_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_pages' => SEO::count(),
            'analyzed_pages' => SEO::whereNotNull('last_analyzed_at')->count(),
            'good_score' => Analysis::where('score', '>=', 80)->count(),
            'average_score' => Analysis::where('score', '>=', 50)->where('score', '<', 80)->count(),
            'bad_score' => Analysis::where('score', '<', 50)->count(),
        ];

        return Inertia::render('SEO/Dashboard', [
            'seoEntries' => $seoEntries,
            'stats' => $stats,
            'config' => [
                'ai_enabled' => config('seo.ai.enabled', true),
                'theme' => config('seo.ui.theme', 'tailwind'),
                'dark_mode' => config('seo.ui.enable_dark_mode', true),
            ],
        ]);
    }

    /**
     * Affiche le formulaire d'édition des métadonnées SEO.
     *
     * @param SEO $seo
     * @return \Inertia\Response
     */
    public function edit(SEO $seo)
    {
        $seo->load('latestAnalysis');

        $aiSuggestions = [];
        if (config('seo.ai.enabled', true) && $this->aiService->isEnabled()) {
            // Récupérer le contenu de la page (simulé ici)
            $content = $this->fetchPageContent($seo->url);
            
            if ($content) {
                $aiSuggestions = [
                    'title' => $this->aiService->generateTitleSuggestions($content, $seo->title, 3),
                    'description' => $this->aiService->generateDescriptionSuggestions($content, $seo->description, 3),
                    'keywords' => $this->aiService->generateKeywordsSuggestions($content, $seo->keywords, 3),
                ];
            }
        }

        return Inertia::render('SEO/MetaEditor', [
            'seo' => $seo,
            'aiSuggestions' => $aiSuggestions,
            'config' => [
                'ai_enabled' => config('seo.ai.enabled', true) && $this->aiService->isEnabled(),
                'theme' => config('seo.ui.theme', 'tailwind'),
                'dark_mode' => config('seo.ui.enable_dark_mode', true),
            ],
        ]);
    }

    /**
     * Met à jour les métadonnées SEO.
     *
     * @param SEORequest $request
     * @param SEO $seo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SEORequest $request, SEO $seo)
    {
        $seo->update($request->validated());

        return Redirect::route('seo.edit', $seo)
            ->with('success', 'Les métadonnées SEO ont été mises à jour avec succès.');
    }

    /**
     * Affiche la page de performance SEO.
     *
     * @param SEO $seo
     * @return \Inertia\Response
     */
    public function performance(SEO $seo)
    {
        $seo->load('analyses');

        $analyses = $seo->analyses()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $performanceData = [
            'labels' => $analyses->pluck('created_at')->map(function ($date) {
                return $date->format('d/m/Y H:i');
            })->toArray(),
            'scores' => $analyses->pluck('score')->toArray(),
        ];

        return Inertia::render('SEO/Performance', [
            'seo' => $seo,
            'performanceData' => $performanceData,
            'latestAnalysis' => $seo->latestAnalysis,
            'config' => [
                'theme' => config('seo.ui.theme', 'tailwind'),
                'dark_mode' => config('seo.ui.enable_dark_mode', true),
            ],
        ]);
    }

    /**
     * Affiche la page de suggestions SEO.
     *
     * @param SEO $seo
     * @return \Inertia\Response
     */
    public function suggestions(SEO $seo)
    {
        $seo->load('latestAnalysis');

        $aiSuggestions = [];
        if (config('seo.ai.enabled', true) && $this->aiService->isEnabled()) {
            // Récupérer le contenu de la page (simulé ici)
            $content = $this->fetchPageContent($seo->url);
            
            if ($content) {
                $analysis = $this->aiService->analyzeContent($content, $seo->url, $seo);
                $prediction = $this->aiService->predictSEOScore($content, $seo->toArray());
                
                $aiSuggestions = [
                    'analysis' => $analysis,
                    'prediction' => $prediction,
                    'title' => $this->aiService->generateTitleSuggestions($content, $seo->title, 5),
                    'description' => $this->aiService->generateDescriptionSuggestions($content, $seo->description, 5),
                    'keywords' => $this->aiService->generateKeywordsSuggestions($content, $seo->keywords, 5),
                ];
            }
        }

        return Inertia::render('SEO/Suggestions', [
            'seo' => $seo,
            'aiSuggestions' => $aiSuggestions,
            'config' => [
                'ai_enabled' => config('seo.ai.enabled', true) && $this->aiService->isEnabled(),
                'theme' => config('seo.ui.theme', 'tailwind'),
                'dark_mode' => config('seo.ui.enable_dark_mode', true),
            ],
        ]);
    }

    /**
     * Analyse une page et enregistre les résultats.
     *
     * @param Request $request
     * @param SEO $seo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function analyze(Request $request, SEO $seo)
    {
        // Récupérer le contenu de la page (simulé ici)
        $content = $this->fetchPageContent($seo->url);
        
        if (!$content) {
            return Redirect::back()
                ->with('error', 'Impossible de récupérer le contenu de la page.');
        }
        
        // Analyser le contenu
        $analysisData = $this->analyzePageContent($content, $seo);
        
        // Créer une nouvelle analyse
        $analysis = new Analysis($analysisData);
        $seo->analyses()->save($analysis);
        
        // Mettre à jour la date de dernière analyse
        $seo->update(['last_analyzed_at' => now()]);
        
        return Redirect::route('seo.performance', $seo)
            ->with('success', 'La page a été analysée avec succès.');
    }

    /**
     * Récupère le contenu d'une page.
     *
     * @param string $url
     * @return string|null
     */
    protected function fetchPageContent(string $url): ?string
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url);
            return (string) $response->getBody();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Analyse le contenu d'une page.
     *
     * @param string $content
     * @param SEO $seo
     * @return array
     */
    protected function analyzePageContent(string $content, SEO $seo): array
    {
        // Analyse de base
        $contentLength = strlen(strip_tags($content));
        $hasTitle = strpos($content, '<title>') !== false;
        $hasMetaDescription = preg_match('/<meta[^>]*name=["\']description["\'][^>]*>/i', $content);
        $hasMetaKeywords = preg_match('/<meta[^>]*name=["\']keywords["\'][^>]*>/i', $content);
        $hasCanonical = preg_match('/<link[^>]*rel=["\']canonical["\'][^>]*>/i', $content);
        $hasH1 = strpos($content, '<h1') !== false;
        
        // Vérifier les attributs alt des images
        preg_match_all('/<img[^>]*>/i', $content, $images);
        $totalImages = count($images[0]);
        $imagesWithAlt = 0;
        
        foreach ($images[0] as $img) {
            if (preg_match('/alt=["\'][^"\']*["\']/', $img)) {
                $imagesWithAlt++;
            }
        }
        
        $hasImagesAlt = $totalImages > 0 ? ($imagesWithAlt / $totalImages >= 0.8) : true;
        
        // Calculer le score
        $score = 0;
        
        if ($hasTitle) $score += 15;
        if ($hasMetaDescription) $score += 15;
        if ($hasMetaKeywords) $score += 10;
        if ($hasCanonical) $score += 10;
        if ($hasH1) $score += 10;
        if ($hasImagesAlt) $score += 10;
        if ($contentLength >= config('seo.analysis.min_content_length', 300)) $score += 15;
        
        // Identifier les problèmes
        $issues = [];
        
        if (!$hasTitle) {
            $issues[] = [
                'type' => 'missing_title',
                'message' => 'La page n\'a pas de balise title.',
                'severity' => 'critical',
            ];
        }
        
        if (!$hasMetaDescription) {
            $issues[] = [
                'type' => 'missing_description',
                'message' => 'La page n\'a pas de méta-description.',
                'severity' => 'critical',
            ];
        }
        
        if (!$hasMetaKeywords) {
            $issues[] = [
                'type' => 'missing_keywords',
                'message' => 'La page n\'a pas de méta-keywords.',
                'severity' => 'important',
            ];
        }
        
        if (!$hasCanonical) {
            $issues[] = [
                'type' => 'missing_canonical',
                'message' => 'La page n\'a pas de lien canonique.',
                'severity' => 'important',
            ];
        }
        
        if (!$hasH1) {
            $issues[] = [
                'type' => 'missing_h1',
                'message' => 'La page n\'a pas de balise H1.',
                'severity' => 'important',
            ];
        }
        
        if (!$hasImagesAlt) {
            $issues[] = [
                'type' => 'missing_alt',
                'message' => 'Certaines images n\'ont pas d\'attribut alt.',
                'severity' => 'important',
            ];
        }
        
        if ($contentLength < config('seo.analysis.min_content_length', 300)) {
            $issues[] = [
                'type' => 'short_content',
                'message' => 'Le contenu de la page est trop court.',
                'severity' => 'important',
            ];
        }
        
        // Générer des suggestions
        $suggestions = [];
        
        foreach ($issues as $issue) {
            switch ($issue['type']) {
                case 'missing_title':
                    $suggestions[] = 'Ajoutez une balise title pertinente à votre page.';
                    break;
                case 'missing_description':
                    $suggestions[] = 'Ajoutez une méta-description informative de 120-155 caractères.';
                    break;
                case 'missing_keywords':
                    $suggestions[] = 'Ajoutez des méta-keywords pertinents pour votre contenu.';
                    break;
                case 'missing_canonical':
                    $suggestions[] = 'Ajoutez un lien canonique pour éviter le contenu dupliqué.';
                    break;
                case 'missing_h1':
                    $suggestions[] = 'Ajoutez une balise H1 contenant votre mot-clé principal.';
                    break;
                case 'missing_alt':
                    $suggestions[] = 'Ajoutez des attributs alt descriptifs à toutes vos images.';
                    break;
                case 'short_content':
                    $suggestions[] = 'Augmentez la longueur du contenu à au moins ' . config('seo.analysis.min_content_length', 300) . ' caractères.';
                    break;
            }
        }
        
        // Ajouter des suggestions d'IA si disponible
        $aiSuggestions = [];
        if (config('seo.ai.enabled', true) && $this->aiService->isEnabled()) {
            $aiAnalysis = $this->aiService->analyzeContent($content, $seo->url, $seo);
            $aiSuggestions = $aiAnalysis['suggestions'] ?? [];
        }
        
        return [
            'url' => $seo->url,
            'score' => $score,
            'content_length' => $contentLength,
            'has_meta_title' => $hasTitle,
            'has_meta_description' => $hasMetaDescription,
            'has_meta_keywords' => $hasMetaKeywords,
            'has_canonical' => $hasCanonical,
            'has_h1' => $hasH1,
            'has_images_alt' => $hasImagesAlt,
            'has_broken_links' => false, // Nécessiterait une analyse plus poussée
            'issues' => $issues,
            'suggestions' => $suggestions,
            'ai_suggestions' => $aiSuggestions,
        ];
    }
} 