<?php

namespace Seodably\SEO\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Seodably\SEO\Models\SEO;
use Seodably\SEO\Models\Analysis;
use Seodably\SEO\Services\SEOService;
use Seodably\SEO\Services\AIService;
use Illuminate\Support\Facades\Validator;

class AnalysisController extends Controller
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
     * Affiche la liste des analyses SEO.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $analyses = Analysis::with('seo')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_analyses' => Analysis::count(),
            'good_score' => Analysis::where('score', '>=', 80)->count(),
            'average_score' => Analysis::where('score', '>=', 50)->where('score', '<', 80)->count(),
            'bad_score' => Analysis::where('score', '<', 50)->count(),
        ];

        return Inertia::render('SEO/Analyses', [
            'analyses' => $analyses,
            'stats' => $stats,
            'config' => [
                'theme' => config('seo.ui.theme', 'tailwind'),
                'dark_mode' => config('seo.ui.enable_dark_mode', true),
            ],
        ]);
    }

    /**
     * Affiche les détails d'une analyse SEO.
     *
     * @param Analysis $analysis
     * @return \Inertia\Response
     */
    public function show(Analysis $analysis)
    {
        $analysis->load('seo');

        return Inertia::render('SEO/AnalysisDetails', [
            'analysis' => $analysis,
            'config' => [
                'theme' => config('seo.ui.theme', 'tailwind'),
                'dark_mode' => config('seo.ui.enable_dark_mode', true),
            ],
        ]);
    }

    /**
     * Analyse une URL et enregistre les résultats.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');

        // Récupérer ou créer l'entrée SEO
        $seo = SEO::firstOrCreate(
            ['url' => $url],
            ['is_active' => true]
        );

        // Récupérer le contenu de la page
        $content = $this->fetchPageContent($url);

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

        return Redirect::route('seo.analysis.show', $analysis)
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
        
        // Calculer la densité de mots-clés (simulé ici)
        $keywordDensity = 1.5; // Valeur par défaut
        
        // Calculer le score de lisibilité (simulé ici)
        $readabilityScore = 70; // Valeur par défaut
        
        return [
            'url' => $seo->url,
            'score' => $score,
            'content_length' => $contentLength,
            'keyword_density' => $keywordDensity,
            'readability_score' => $readabilityScore,
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

    /**
     * Analyse en temps réel des métadonnées et du contenu.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeRealtime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string',
            'content' => 'nullable|string',
            'url' => 'nullable|string|url'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $keywords = $request->input('keywords');
        $content = $request->input('content');
        $url = $request->input('url');

        // Analyse des métadonnées
        $metaAnalysis = $this->analyzeMetadataInternal($title, $description, $keywords);
        
        // Analyse du contenu
        $contentAnalysis = [];
        if ($content) {
            $contentAnalysis = $this->analyzeContentInternal($content, $keywords);
        }
        
        // Calcul du score global
        $score = $this->calculateOverallScore($metaAnalysis, $contentAnalysis);
        
        // Suggestions d'amélioration
        $suggestions = $this->generateSuggestions($metaAnalysis, $contentAnalysis);
        
        return response()->json([
            'score' => $score,
            'meta_analysis' => $metaAnalysis,
            'content_analysis' => $contentAnalysis,
            'suggestions' => $suggestions,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Analyse en temps réel des métadonnées.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeMetadata(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string',
            'canonical' => 'nullable|string|url',
            'robots' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $keywords = $request->input('keywords');
        $canonical = $request->input('canonical');
        $robots = $request->input('robots');

        $analysis = $this->analyzeMetadataInternal($title, $description, $keywords, $canonical, $robots);
        
        return response()->json([
            'analysis' => $analysis,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Analyse en temps réel du contenu.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'keywords' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $content = $request->input('content');
        $keywords = $request->input('keywords');

        $analysis = $this->analyzeContentInternal($content, $keywords);
        
        return response()->json([
            'analysis' => $analysis,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Analyse en temps réel des mots-clés.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeKeywords(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'keywords' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $content = $request->input('content');
        $keywords = $request->input('keywords');

        $keywordsArray = array_map('trim', explode(',', $keywords));
        $analysis = [];
        
        foreach ($keywordsArray as $keyword) {
            if (empty($keyword)) continue;
            
            $count = substr_count(strtolower($content), strtolower($keyword));
            $wordCount = str_word_count($content);
            $density = $wordCount > 0 ? ($count / $wordCount) * 100 : 0;
            
            $status = 'optimal';
            if ($density < 0.5) {
                $status = 'low';
            } elseif ($density > 3) {
                $status = 'high';
            }
            
            $analysis[] = [
                'keyword' => $keyword,
                'count' => $count,
                'density' => round($density, 2),
                'status' => $status
            ];
        }
        
        return response()->json([
            'analysis' => $analysis,
            'word_count' => str_word_count($content),
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Analyse en temps réel de la structure du contenu.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeStructure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $content = $request->input('content');
        
        // Analyse des titres (H1, H2, H3, etc.)
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/i', $content, $h1Matches);
        preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $content, $h2Matches);
        preg_match_all('/<h3[^>]*>(.*?)<\/h3>/i', $content, $h3Matches);
        
        // Analyse des paragraphes
        preg_match_all('/<p[^>]*>(.*?)<\/p>/i', $content, $pMatches);
        
        // Analyse des listes
        preg_match_all('/<ul[^>]*>(.*?)<\/ul>/i', $content, $ulMatches);
        preg_match_all('/<ol[^>]*>(.*?)<\/ol>/i', $content, $olMatches);
        
        // Analyse des images
        preg_match_all('/<img[^>]*>/i', $content, $imgMatches);
        preg_match_all('/<img[^>]*alt=["\']([^"\']*)["\'][^>]*>/i', $content, $imgAltMatches);
        
        $h1Count = count($h1Matches[0]);
        $h2Count = count($h2Matches[0]);
        $h3Count = count($h3Matches[0]);
        $pCount = count($pMatches[0]);
        $listCount = count($ulMatches[0]) + count($olMatches[0]);
        $imgCount = count($imgMatches[0]);
        $imgWithAltCount = count($imgAltMatches[0]);
        
        $issues = [];
        $suggestions = [];
        
        // Vérification de la structure
        if ($h1Count === 0) {
            $issues[] = [
                'type' => 'error',
                'message' => 'Aucun titre H1 trouvé. Chaque page devrait avoir un titre H1 unique.'
            ];
            $suggestions[] = 'Ajoutez un titre H1 qui résume le contenu principal de la page.';
        } elseif ($h1Count > 1) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Plusieurs titres H1 trouvés. Il est recommandé d\'avoir un seul titre H1 par page.'
            ];
            $suggestions[] = 'Conservez un seul titre H1 et transformez les autres en H2.';
        }
        
        if ($h2Count === 0 && strlen($content) > 300) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Aucun titre H2 trouvé. Les titres H2 aident à structurer votre contenu.'
            ];
            $suggestions[] = 'Ajoutez des titres H2 pour diviser votre contenu en sections logiques.';
        }
        
        if ($pCount < 3 && strlen($content) > 300) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Peu de paragraphes détectés. Un bon contenu est généralement divisé en plusieurs paragraphes.'
            ];
            $suggestions[] = 'Divisez votre contenu en paragraphes plus courts pour améliorer la lisibilité.';
        }
        
        if ($imgCount > 0 && $imgWithAltCount < $imgCount) {
            $issues[] = [
                'type' => 'error',
                'message' => 'Certaines images n\'ont pas d\'attribut alt. Les attributs alt sont importants pour l\'accessibilité et le SEO.'
            ];
            $suggestions[] = 'Ajoutez des attributs alt descriptifs à toutes vos images.';
        }
        
        return response()->json([
            'structure' => [
                'h1_count' => $h1Count,
                'h2_count' => $h2Count,
                'h3_count' => $h3Count,
                'paragraph_count' => $pCount,
                'list_count' => $listCount,
                'image_count' => $imgCount,
                'images_with_alt' => $imgWithAltCount
            ],
            'issues' => $issues,
            'suggestions' => $suggestions,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Analyse en temps réel de la lisibilité du contenu.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeReadability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $content = $request->input('content');
        
        // Nettoyage du contenu HTML
        $plainText = strip_tags($content);
        
        // Calcul des statistiques de base
        $wordCount = str_word_count($plainText);
        $sentenceCount = preg_match_all('/[.!?]+/', $plainText, $matches);
        $sentenceCount = max(1, $sentenceCount); // Éviter la division par zéro
        
        $paragraphCount = substr_count($content, '</p>');
        $paragraphCount = max(1, $paragraphCount); // Éviter la division par zéro
        
        // Calcul des moyennes
        $wordsPerSentence = $wordCount / $sentenceCount;
        $sentencesPerParagraph = $sentenceCount / $paragraphCount;
        
        // Calcul du score de lisibilité (formule simplifiée de Flesch-Kincaid)
        $readabilityScore = 206.835 - (1.015 * $wordsPerSentence) - (84.6 * ($wordCount / $sentenceCount));
        $readabilityScore = max(0, min(100, $readabilityScore)); // Limiter entre 0 et 100
        
        // Interprétation du score
        $readabilityLevel = 'Difficile';
        if ($readabilityScore >= 90) {
            $readabilityLevel = 'Très facile';
        } elseif ($readabilityScore >= 80) {
            $readabilityLevel = 'Facile';
        } elseif ($readabilityScore >= 70) {
            $readabilityLevel = 'Assez facile';
        } elseif ($readabilityScore >= 60) {
            $readabilityLevel = 'Standard';
        } elseif ($readabilityScore >= 50) {
            $readabilityLevel = 'Assez difficile';
        } elseif ($readabilityScore >= 30) {
            $readabilityLevel = 'Difficile';
        }
        
        // Suggestions d'amélioration
        $suggestions = [];
        
        if ($wordsPerSentence > 20) {
            $suggestions[] = 'Vos phrases sont trop longues (moyenne de ' . round($wordsPerSentence, 1) . ' mots par phrase). Essayez de les raccourcir pour améliorer la lisibilité.';
        }
        
        if ($sentencesPerParagraph > 5) {
            $suggestions[] = 'Vos paragraphes contiennent beaucoup de phrases (moyenne de ' . round($sentencesPerParagraph, 1) . ' phrases par paragraphe). Envisagez de diviser vos paragraphes pour améliorer la lisibilité.';
        }
        
        if ($readabilityScore < 60) {
            $suggestions[] = 'Votre texte est difficile à lire. Essayez d\'utiliser des mots plus simples et des phrases plus courtes.';
        }
        
        return response()->json([
            'readability' => [
                'score' => round($readabilityScore, 1),
                'level' => $readabilityLevel,
                'word_count' => $wordCount,
                'sentence_count' => $sentenceCount,
                'paragraph_count' => $paragraphCount,
                'words_per_sentence' => round($wordsPerSentence, 1),
                'sentences_per_paragraph' => round($sentencesPerParagraph, 1)
            ],
            'suggestions' => $suggestions,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Analyse interne des métadonnées.
     *
     * @param string|null $title
     * @param string|null $description
     * @param string|null $keywords
     * @param string|null $canonical
     * @param string|null $robots
     * @return array
     */
    protected function analyzeMetadataInternal(?string $title, ?string $description, ?string $keywords, ?string $canonical = null, ?string $robots = null): array
    {
        $issues = [];
        $suggestions = [];
        $score = 100; // Score initial parfait
        
        // Configuration
        $titleMaxLength = config('seo.meta.max_title_length', 60);
        $descriptionMaxLength = config('seo.meta.max_description_length', 160);
        
        // Analyse du titre
        if (empty($title)) {
            $issues[] = [
                'type' => 'error',
                'field' => 'title',
                'message' => 'Le titre est manquant.'
            ];
            $suggestions[] = 'Ajoutez un titre qui décrit clairement le contenu de la page.';
            $score -= 20;
        } elseif (strlen($title) > $titleMaxLength) {
            $issues[] = [
                'type' => 'warning',
                'field' => 'title',
                'message' => "Le titre dépasse la longueur recommandée de {$titleMaxLength} caractères."
            ];
            $suggestions[] = "Raccourcissez le titre à {$titleMaxLength} caractères ou moins.";
            $score -= 5;
        } elseif (strlen($title) < 20) {
            $issues[] = [
                'type' => 'warning',
                'field' => 'title',
                'message' => 'Le titre est trop court.'
            ];
            $suggestions[] = 'Allongez le titre pour qu\'il soit plus descriptif.';
            $score -= 3;
        }
        
        // Analyse de la description
        if (empty($description)) {
            $issues[] = [
                'type' => 'error',
                'field' => 'description',
                'message' => 'La description est manquante.'
            ];
            $suggestions[] = 'Ajoutez une description qui résume le contenu de la page.';
            $score -= 15;
        } elseif (strlen($description) > $descriptionMaxLength) {
            $issues[] = [
                'type' => 'warning',
                'field' => 'description',
                'message' => "La description dépasse la longueur recommandée de {$descriptionMaxLength} caractères."
            ];
            $suggestions[] = "Raccourcissez la description à {$descriptionMaxLength} caractères ou moins.";
            $score -= 5;
        } elseif (strlen($description) < 50) {
            $issues[] = [
                'type' => 'warning',
                'field' => 'description',
                'message' => 'La description est trop courte.'
            ];
            $suggestions[] = 'Allongez la description pour qu\'elle soit plus informative.';
            $score -= 3;
        }
        
        // Analyse des mots-clés
        if (empty($keywords)) {
            $issues[] = [
                'type' => 'info',
                'field' => 'keywords',
                'message' => 'Les mots-clés sont manquants.'
            ];
            $suggestions[] = 'Bien que moins importants qu\'avant, les mots-clés peuvent aider à définir le sujet de la page.';
            $score -= 2;
        } elseif (str_word_count($keywords) > 10) {
            $issues[] = [
                'type' => 'info',
                'field' => 'keywords',
                'message' => 'Trop de mots-clés peuvent diluer leur efficacité.'
            ];
            $suggestions[] = 'Limitez-vous à 5-7 mots-clés les plus pertinents.';
            $score -= 1;
        }
        
        // Analyse de l'URL canonique
        if (empty($canonical)) {
            $issues[] = [
                'type' => 'info',
                'field' => 'canonical',
                'message' => 'L\'URL canonique est manquante.'
            ];
            $suggestions[] = 'Ajoutez une URL canonique pour éviter les problèmes de contenu dupliqué.';
            $score -= 2;
        }
        
        // Analyse des directives robots
        if (!empty($robots) && strpos($robots, 'noindex') !== false) {
            $issues[] = [
                'type' => 'warning',
                'field' => 'robots',
                'message' => 'La page est configurée pour ne pas être indexée par les moteurs de recherche.'
            ];
            $suggestions[] = 'Assurez-vous que c\'est intentionnel, sinon retirez la directive "noindex".';
            $score -= 10;
        }
        
        // Limiter le score entre 0 et 100
        $score = max(0, min(100, $score));
        
        return [
            'score' => $score,
            'title' => [
                'content' => $title,
                'length' => $title ? strlen($title) : 0,
                'max_length' => $titleMaxLength,
                'is_empty' => empty($title),
                'is_too_long' => $title && strlen($title) > $titleMaxLength
            ],
            'description' => [
                'content' => $description,
                'length' => $description ? strlen($description) : 0,
                'max_length' => $descriptionMaxLength,
                'is_empty' => empty($description),
                'is_too_long' => $description && strlen($description) > $descriptionMaxLength
            ],
            'keywords' => [
                'content' => $keywords,
                'count' => $keywords ? str_word_count($keywords) : 0,
                'is_empty' => empty($keywords)
            ],
            'canonical' => [
                'content' => $canonical,
                'is_empty' => empty($canonical)
            ],
            'robots' => [
                'content' => $robots,
                'is_noindex' => $robots && strpos($robots, 'noindex') !== false
            ],
            'issues' => $issues,
            'suggestions' => $suggestions
        ];
    }

    /**
     * Analyse interne du contenu.
     *
     * @param string $content
     * @param string|null $keywords
     * @return array
     */
    protected function analyzeContentInternal(string $content, ?string $keywords = null): array
    {
        $issues = [];
        $suggestions = [];
        $score = 100; // Score initial parfait
        
        // Nettoyage du contenu HTML
        $plainText = strip_tags($content);
        
        // Statistiques de base
        $contentLength = strlen($plainText);
        $wordCount = str_word_count($plainText);
        
        // Analyse de la longueur du contenu
        if ($wordCount < 300) {
            $issues[] = [
                'type' => 'warning',
                'field' => 'content',
                'message' => 'Le contenu est trop court.'
            ];
            $suggestions[] = 'Ajoutez plus de contenu pour améliorer la pertinence de la page.';
            $score -= 15;
        }
        
        // Analyse des titres (H1, H2, H3, etc.)
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/i', $content, $h1Matches);
        preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $content, $h2Matches);
        
        $h1Count = count($h1Matches[0]);
        $h2Count = count($h2Matches[0]);
        
        if ($h1Count === 0) {
            $issues[] = [
                'type' => 'error',
                'field' => 'headings',
                'message' => 'Aucun titre H1 trouvé.'
            ];
            $suggestions[] = 'Ajoutez un titre H1 qui résume le contenu principal de la page.';
            $score -= 10;
        } elseif ($h1Count > 1) {
            $issues[] = [
                'type' => 'warning',
                'field' => 'headings',
                'message' => 'Plusieurs titres H1 trouvés.'
            ];
            $suggestions[] = 'Conservez un seul titre H1 et transformez les autres en H2.';
            $score -= 5;
        }
        
        if ($wordCount > 300 && $h2Count === 0) {
            $issues[] = [
                'type' => 'warning',
                'field' => 'headings',
                'message' => 'Aucun titre H2 trouvé.'
            ];
            $suggestions[] = 'Ajoutez des titres H2 pour diviser votre contenu en sections logiques.';
            $score -= 5;
        }
        
        // Analyse des images
        preg_match_all('/<img[^>]*>/i', $content, $imgMatches);
        preg_match_all('/<img[^>]*alt=["\']([^"\']*)["\'][^>]*>/i', $content, $imgAltMatches);
        
        $imgCount = count($imgMatches[0]);
        $imgWithAltCount = count($imgAltMatches[0]);
        
        if ($imgCount > 0 && $imgWithAltCount < $imgCount) {
            $issues[] = [
                'type' => 'error',
                'field' => 'images',
                'message' => 'Certaines images n\'ont pas d\'attribut alt.'
            ];
            $suggestions[] = 'Ajoutez des attributs alt descriptifs à toutes vos images.';
            $score -= 5;
        }
        
        // Analyse des liens
        preg_match_all('/<a[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/i', $content, $linkMatches);
        $linkCount = count($linkMatches[0]);
        $internalLinkCount = 0;
        $externalLinkCount = 0;
        
        foreach ($linkMatches[1] as $link) {
            if (strpos($link, 'http') === 0 && strpos($link, $_SERVER['HTTP_HOST']) === false) {
                $externalLinkCount++;
            } else {
                $internalLinkCount++;
            }
        }
        
        // Analyse des mots-clés
        $keywordDensity = 0;
        if (!empty($keywords) && $wordCount > 0) {
            $keywordsArray = array_map('trim', explode(',', $keywords));
            $keywordCount = 0;
            
            foreach ($keywordsArray as $keyword) {
                if (empty($keyword)) continue;
                $keywordCount += substr_count(strtolower($plainText), strtolower($keyword));
            }
            
            $keywordDensity = ($keywordCount / $wordCount) * 100;
            
            if ($keywordDensity < 0.5) {
                $issues[] = [
                    'type' => 'warning',
                    'field' => 'keywords',
                    'message' => 'La densité des mots-clés est trop faible.'
                ];
                $suggestions[] = 'Intégrez vos mots-clés plus naturellement dans le contenu.';
                $score -= 5;
            } elseif ($keywordDensity > 3) {
                $issues[] = [
                    'type' => 'warning',
                    'field' => 'keywords',
                    'message' => 'La densité des mots-clés est trop élevée.'
                ];
                $suggestions[] = 'Réduisez la fréquence des mots-clés pour éviter le bourrage de mots-clés.';
                $score -= 10;
            }
        }
        
        // Limiter le score entre 0 et 100
        $score = max(0, min(100, $score));
        
        return [
            'score' => $score,
            'content_length' => $contentLength,
            'word_count' => $wordCount,
            'keyword_density' => round($keywordDensity, 2),
            'headings' => [
                'h1_count' => $h1Count,
                'h2_count' => $h2Count
            ],
            'images' => [
                'total_count' => $imgCount,
                'with_alt_count' => $imgWithAltCount
            ],
            'links' => [
                'total_count' => $linkCount,
                'internal_count' => $internalLinkCount,
                'external_count' => $externalLinkCount
            ],
            'issues' => $issues,
            'suggestions' => $suggestions
        ];
    }

    /**
     * Calcule le score global à partir des analyses de métadonnées et de contenu.
     *
     * @param array $metaAnalysis
     * @param array $contentAnalysis
     * @return int
     */
    protected function calculateOverallScore(array $metaAnalysis, array $contentAnalysis): int
    {
        // Pondération : 40% pour les métadonnées, 60% pour le contenu
        $metaWeight = 0.4;
        $contentWeight = 0.6;
        
        $metaScore = $metaAnalysis['score'] ?? 0;
        $contentScore = $contentAnalysis['score'] ?? 0;
        
        // Si l'analyse de contenu est vide, on utilise uniquement le score des métadonnées
        if (empty($contentAnalysis)) {
            return $metaScore;
        }
        
        $overallScore = ($metaScore * $metaWeight) + ($contentScore * $contentWeight);
        
        return (int) round($overallScore);
    }

    /**
     * Génère des suggestions d'amélioration basées sur les analyses.
     *
     * @param array $metaAnalysis
     * @param array $contentAnalysis
     * @return array
     */
    protected function generateSuggestions(array $metaAnalysis, array $contentAnalysis): array
    {
        $suggestions = [];
        
        // Ajouter les suggestions des métadonnées
        if (isset($metaAnalysis['suggestions'])) {
            foreach ($metaAnalysis['suggestions'] as $suggestion) {
                $suggestions[] = [
                    'type' => 'meta',
                    'message' => $suggestion
                ];
            }
        }
        
        // Ajouter les suggestions du contenu
        if (isset($contentAnalysis['suggestions'])) {
            foreach ($contentAnalysis['suggestions'] as $suggestion) {
                $suggestions[] = [
                    'type' => 'content',
                    'message' => $suggestion
                ];
            }
        }
        
        // Ajouter des suggestions générales si nécessaire
        if (empty($suggestions)) {
            $suggestions[] = [
                'type' => 'general',
                'message' => 'Votre page semble bien optimisée pour le SEO. Continuez à surveiller ses performances.'
            ];
        }
        
        return $suggestions;
    }
} 