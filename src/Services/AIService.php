<?php

namespace Seodably\SEO\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Seodably\SEO\Models\SEO;
use Seodably\SEO\Models\Analysis;

class AIService
{
    /**
     * La clé API pour le service d'IA.
     *
     * @var string|null
     */
    protected $apiKey;

    /**
     * Le modèle d'IA à utiliser.
     *
     * @var string
     */
    protected $model;

    /**
     * La température pour les réponses de l'IA.
     *
     * @var float
     */
    protected $temperature;

    /**
     * Le nombre de suggestions à générer.
     *
     * @var int
     */
    protected $suggestionsCount;

    /**
     * Initialise le service d'IA.
     */
    public function __construct()
    {
        $this->apiKey = config('seo.ai.api_key');
        $this->model = config('seo.ai.model', 'gpt-4');
        $this->temperature = config('seo.ai.temperature', 0.7);
        $this->suggestionsCount = config('seo.ai.suggestions_count', 5);
    }

    /**
     * Vérifie si le service d'IA est activé et configuré.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('seo.ai.enabled', true) && !empty($this->apiKey);
    }

    /**
     * Génère des suggestions de titre pour une page.
     *
     * @param string $content Le contenu de la page
     * @param string $currentTitle Le titre actuel (optionnel)
     * @param int $count Le nombre de suggestions à générer
     * @return array
     */
    public function generateTitleSuggestions(string $content, ?string $currentTitle = null, int $count = null): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $count = $count ?? $this->suggestionsCount;
        $cacheKey = 'seo_ai_title_' . md5($content . $currentTitle . $count);
        $cacheDuration = 60 * 24; // 24 heures

        return Cache::remember($cacheKey, $cacheDuration, function () use ($content, $currentTitle, $count) {
            $prompt = $this->buildTitlePrompt($content, $currentTitle, $count);
            $response = $this->callAI($prompt);

            if (empty($response)) {
                return [];
            }

            // Traiter la réponse pour extraire les suggestions
            return $this->parseSuggestions($response);
        });
    }

    /**
     * Génère des suggestions de description pour une page.
     *
     * @param string $content Le contenu de la page
     * @param string $currentDescription La description actuelle (optionnel)
     * @param int $count Le nombre de suggestions à générer
     * @return array
     */
    public function generateDescriptionSuggestions(string $content, ?string $currentDescription = null, int $count = null): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $count = $count ?? $this->suggestionsCount;
        $cacheKey = 'seo_ai_description_' . md5($content . $currentDescription . $count);
        $cacheDuration = 60 * 24; // 24 heures

        return Cache::remember($cacheKey, $cacheDuration, function () use ($content, $currentDescription, $count) {
            $prompt = $this->buildDescriptionPrompt($content, $currentDescription, $count);
            $response = $this->callAI($prompt);

            if (empty($response)) {
                return [];
            }

            // Traiter la réponse pour extraire les suggestions
            return $this->parseSuggestions($response);
        });
    }

    /**
     * Génère des suggestions de mots-clés pour une page.
     *
     * @param string $content Le contenu de la page
     * @param string $currentKeywords Les mots-clés actuels (optionnel)
     * @param int $count Le nombre de suggestions à générer
     * @return array
     */
    public function generateKeywordsSuggestions(string $content, ?string $currentKeywords = null, int $count = null): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $count = $count ?? $this->suggestionsCount;
        $cacheKey = 'seo_ai_keywords_' . md5($content . $currentKeywords . $count);
        $cacheDuration = 60 * 24; // 24 heures

        return Cache::remember($cacheKey, $cacheDuration, function () use ($content, $currentKeywords, $count) {
            $prompt = $this->buildKeywordsPrompt($content, $currentKeywords, $count);
            $response = $this->callAI($prompt);

            if (empty($response)) {
                return [];
            }

            // Traiter la réponse pour extraire les suggestions
            return $this->parseSuggestions($response);
        });
    }

    /**
     * Analyse le contenu d'une page et génère des suggestions d'amélioration.
     *
     * @param string $content Le contenu de la page
     * @param string $url L'URL de la page
     * @param SEO|null $seo Les métadonnées SEO actuelles
     * @return array
     */
    public function analyzeContent(string $content, string $url, ?SEO $seo = null): array
    {
        if (!$this->isEnabled()) {
            return [
                'score' => 0,
                'issues' => [],
                'suggestions' => [],
            ];
        }

        $cacheKey = 'seo_ai_analysis_' . md5($content . $url);
        $cacheDuration = 60 * 24; // 24 heures

        return Cache::remember($cacheKey, $cacheDuration, function () use ($content, $url, $seo) {
            $prompt = $this->buildAnalysisPrompt($content, $url, $seo);
            $response = $this->callAI($prompt);

            if (empty($response)) {
                return [
                    'score' => 0,
                    'issues' => [],
                    'suggestions' => [],
                ];
            }

            // Traiter la réponse pour extraire l'analyse
            return $this->parseAnalysis($response);
        });
    }

    /**
     * Prédit le score SEO d'une page avant sa publication.
     *
     * @param string $content Le contenu de la page
     * @param array $meta Les métadonnées de la page
     * @return array
     */
    public function predictSEOScore(string $content, array $meta): array
    {
        if (!$this->isEnabled()) {
            return [
                'score' => 0,
                'prediction' => 'Le service d\'IA n\'est pas activé.',
            ];
        }

        $cacheKey = 'seo_ai_prediction_' . md5($content . json_encode($meta));
        $cacheDuration = 60 * 24; // 24 heures

        return Cache::remember($cacheKey, $cacheDuration, function () use ($content, $meta) {
            $prompt = $this->buildPredictionPrompt($content, $meta);
            $response = $this->callAI($prompt);

            if (empty($response)) {
                return [
                    'score' => 0,
                    'prediction' => 'Impossible de générer une prédiction.',
                ];
            }

            // Traiter la réponse pour extraire la prédiction
            return $this->parsePrediction($response);
        });
    }

    /**
     * Construit le prompt pour générer des suggestions de titre.
     *
     * @param string $content Le contenu de la page
     * @param string|null $currentTitle Le titre actuel
     * @param int $count Le nombre de suggestions à générer
     * @return string
     */
    protected function buildTitlePrompt(string $content, ?string $currentTitle, int $count): string
    {
        $contentExcerpt = substr($content, 0, 1500) . (strlen($content) > 1500 ? '...' : '');
        
        $prompt = "En tant qu'expert SEO, génère {$count} suggestions de titres optimisés pour le contenu suivant. ";
        
        if ($currentTitle) {
            $prompt .= "Le titre actuel est : \"{$currentTitle}\". ";
        }
        
        $prompt .= "Les titres doivent être accrocheurs, contenir des mots-clés pertinents, et faire moins de 60 caractères. ";
        $prompt .= "Réponds uniquement avec une liste numérotée de titres, sans commentaires supplémentaires.\n\n";
        $prompt .= "Contenu : {$contentExcerpt}";
        
        return $prompt;
    }

    /**
     * Construit le prompt pour générer des suggestions de description.
     *
     * @param string $content Le contenu de la page
     * @param string|null $currentDescription La description actuelle
     * @param int $count Le nombre de suggestions à générer
     * @return string
     */
    protected function buildDescriptionPrompt(string $content, ?string $currentDescription, int $count): string
    {
        $contentExcerpt = substr($content, 0, 1500) . (strlen($content) > 1500 ? '...' : '');
        
        $prompt = "En tant qu'expert SEO, génère {$count} suggestions de méta-descriptions optimisées pour le contenu suivant. ";
        
        if ($currentDescription) {
            $prompt .= "La description actuelle est : \"{$currentDescription}\". ";
        }
        
        $prompt .= "Les descriptions doivent être informatives, contenir des mots-clés pertinents, et faire entre 120 et 155 caractères. ";
        $prompt .= "Réponds uniquement avec une liste numérotée de descriptions, sans commentaires supplémentaires.\n\n";
        $prompt .= "Contenu : {$contentExcerpt}";
        
        return $prompt;
    }

    /**
     * Construit le prompt pour générer des suggestions de mots-clés.
     *
     * @param string $content Le contenu de la page
     * @param string|null $currentKeywords Les mots-clés actuels
     * @param int $count Le nombre de suggestions à générer
     * @return string
     */
    protected function buildKeywordsPrompt(string $content, ?string $currentKeywords, int $count): string
    {
        $contentExcerpt = substr($content, 0, 1500) . (strlen($content) > 1500 ? '...' : '');
        
        $prompt = "En tant qu'expert SEO, génère {$count} ensembles de mots-clés pertinents pour le contenu suivant. ";
        
        if ($currentKeywords) {
            $prompt .= "Les mots-clés actuels sont : \"{$currentKeywords}\". ";
        }
        
        $prompt .= "Chaque ensemble doit contenir 3 à 5 mots-clés séparés par des virgules. ";
        $prompt .= "Réponds uniquement avec une liste numérotée d'ensembles de mots-clés, sans commentaires supplémentaires.\n\n";
        $prompt .= "Contenu : {$contentExcerpt}";
        
        return $prompt;
    }

    /**
     * Construit le prompt pour analyser le contenu d'une page.
     *
     * @param string $content Le contenu de la page
     * @param string $url L'URL de la page
     * @param SEO|null $seo Les métadonnées SEO actuelles
     * @return string
     */
    protected function buildAnalysisPrompt(string $content, string $url, ?SEO $seo): string
    {
        $contentExcerpt = substr($content, 0, 2000) . (strlen($content) > 2000 ? '...' : '');
        
        $prompt = "En tant qu'expert SEO, analyse le contenu suivant et fournis une évaluation détaillée au format JSON. ";
        $prompt .= "L'URL de la page est : {$url}. ";
        
        if ($seo) {
            $prompt .= "Les métadonnées actuelles sont : ";
            $prompt .= "Titre: \"{$seo->title}\", ";
            $prompt .= "Description: \"{$seo->description}\", ";
            $prompt .= "Mots-clés: \"{$seo->keywords}\". ";
        }
        
        $prompt .= "Ton analyse doit inclure : ";
        $prompt .= "1. Un score global sur 100 ";
        $prompt .= "2. Une liste des problèmes identifiés avec leur sévérité (critique, important, mineur) ";
        $prompt .= "3. Des suggestions d'amélioration spécifiques ";
        $prompt .= "Réponds uniquement avec un objet JSON valide contenant les clés 'score', 'issues' et 'suggestions'.\n\n";
        $prompt .= "Contenu : {$contentExcerpt}";
        
        return $prompt;
    }

    /**
     * Construit le prompt pour prédire le score SEO d'une page.
     *
     * @param string $content Le contenu de la page
     * @param array $meta Les métadonnées de la page
     * @return string
     */
    protected function buildPredictionPrompt(string $content, array $meta): string
    {
        $contentExcerpt = substr($content, 0, 1500) . (strlen($content) > 1500 ? '...' : '');
        
        $prompt = "En tant qu'expert SEO, prédit le score SEO potentiel (sur 100) pour le contenu et les métadonnées suivants. ";
        $prompt .= "Métadonnées : ";
        $prompt .= "Titre: \"" . ($meta['title'] ?? '') . "\", ";
        $prompt .= "Description: \"" . ($meta['description'] ?? '') . "\", ";
        $prompt .= "Mots-clés: \"" . ($meta['keywords'] ?? '') . "\". ";
        
        $prompt .= "Fournis une prédiction détaillée expliquant les forces et faiblesses potentielles. ";
        $prompt .= "Réponds uniquement avec un objet JSON valide contenant les clés 'score' et 'prediction'.\n\n";
        $prompt .= "Contenu : {$contentExcerpt}";
        
        return $prompt;
    }

    /**
     * Appelle l'API d'IA avec un prompt donné.
     *
     * @param string $prompt Le prompt à envoyer à l'IA
     * @return string|null La réponse de l'IA
     */
    protected function callAI(string $prompt): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu es un expert SEO qui fournit des analyses et des suggestions précises et utiles.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $this->temperature,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }

            Log::error('Erreur lors de l\'appel à l\'API d\'IA', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'appel à l\'API d\'IA', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Parse les suggestions de l'IA.
     *
     * @param string $response La réponse de l'IA
     * @return array Les suggestions extraites
     */
    protected function parseSuggestions(string $response): array
    {
        $suggestions = [];
        
        // Essayer de parser comme JSON d'abord
        $jsonData = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            return $jsonData;
        }
        
        // Sinon, parser comme une liste numérotée
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\d+\.\s+(.+)$/', $line, $matches)) {
                $suggestions[] = $matches[1];
            }
        }
        
        return $suggestions;
    }

    /**
     * Parse l'analyse de l'IA.
     *
     * @param string $response La réponse de l'IA
     * @return array L'analyse extraite
     */
    protected function parseAnalysis(string $response): array
    {
        $defaultAnalysis = [
            'score' => 0,
            'issues' => [],
            'suggestions' => [],
        ];
        
        // Essayer de parser comme JSON
        $jsonData = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            return array_merge($defaultAnalysis, $jsonData);
        }
        
        return $defaultAnalysis;
    }

    /**
     * Parse la prédiction de l'IA.
     *
     * @param string $response La réponse de l'IA
     * @return array La prédiction extraite
     */
    protected function parsePrediction(string $response): array
    {
        $defaultPrediction = [
            'score' => 0,
            'prediction' => 'Impossible de générer une prédiction.',
        ];
        
        // Essayer de parser comme JSON
        $jsonData = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            return array_merge($defaultPrediction, $jsonData);
        }
        
        return $defaultPrediction;
    }
} 