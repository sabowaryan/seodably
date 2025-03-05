<?php

use Illuminate\Support\Facades\Route;
use Seodably\SEO\Http\Controllers\APIController;
use Seodably\SEO\Http\Controllers\SitemapController;
use Seodably\SEO\Http\Controllers\RobotsController;
use Seodably\SEO\Http\Controllers\AnalysisController;

/*
|--------------------------------------------------------------------------
| Routes API du package SEO
|--------------------------------------------------------------------------
|
| Ces routes sont chargées par le SEOServiceProvider et sont accessibles
| via le préfixe configuré dans config/seo.php (par défaut: 'api/seo').
| Elles permettent d'interagir avec les fonctionnalités SEO via une API REST.
|
*/

// Routes protégées par API token
Route::middleware(['auth:sanctum'])->group(function () {
    // Métadonnées SEO
    Route::get('/meta', [APIController::class, 'getMetadata']);
    Route::post('/meta', [APIController::class, 'updateMetadata']);
    Route::get('/meta/bulk', [APIController::class, 'getBulkMetadata']);
    Route::post('/meta/bulk', [APIController::class, 'updateBulkMetadata']);
    
    // Analyses SEO
    Route::get('/analysis', [APIController::class, 'getAnalysis']);
    Route::post('/analysis', [APIController::class, 'createAnalysis']);
    Route::get('/analysis/history', [APIController::class, 'getAnalysisHistory']);
    Route::get('/analysis/stats', [APIController::class, 'getAnalysisStats']);
    
    // Analyse en temps réel
    Route::post('/analyze', [AnalysisController::class, 'analyzeRealtime']);
    Route::post('/analyze/meta', [AnalysisController::class, 'analyzeMetadata']);
    Route::post('/analyze/content', [AnalysisController::class, 'analyzeContent']);
    Route::post('/analyze/keywords', [AnalysisController::class, 'analyzeKeywords']);
    Route::post('/analyze/structure', [AnalysisController::class, 'analyzeStructure']);
    Route::post('/analyze/readability', [AnalysisController::class, 'analyzeReadability']);
    
    // Suggestions SEO basées sur l'IA
    Route::get('/suggestions', [APIController::class, 'getSuggestions']);
    Route::post('/suggestions/generate', [APIController::class, 'generateSuggestions']);
    Route::post('/suggestions/implement', [APIController::class, 'implementSuggestion']);
    Route::post('/suggestions/realtime', [APIController::class, 'getRealtimeSuggestions']);
    
    // Prédiction de score SEO
    Route::post('/predict', [APIController::class, 'predictScore']);
    Route::post('/predict/realtime', [APIController::class, 'predictRealtimeScore']);
    
    // Gestion des mots-clés
    Route::get('/keywords', [APIController::class, 'getKeywords']);
    Route::post('/keywords/extract', [APIController::class, 'extractKeywords']);
    Route::post('/keywords/suggest', [APIController::class, 'suggestKeywords']);
    Route::post('/keywords/density', [APIController::class, 'calculateKeywordDensity']);
    
    // Monitoring et rapports
    Route::get('/reports/performance', [APIController::class, 'getPerformanceReport']);
    Route::get('/reports/issues', [APIController::class, 'getIssuesReport']);
    Route::get('/reports/progress', [APIController::class, 'getProgressReport']);
    
    // Configuration
    Route::get('/config', [APIController::class, 'getConfig']);
    Route::post('/config', [APIController::class, 'updateConfig']);
});

// Routes publiques
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/robots.txt', [RobotsController::class, 'index']);
Route::get('/health', [APIController::class, 'healthCheck']);

// Documentation de l'API (si OpenAPI est activé dans la configuration)
Route::get('/docs', function () {
    if (config('seo.api.openapi_enabled', false)) {
        return response()->json([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Seodably SEO API',
                'description' => 'API pour gérer les fonctionnalités SEO de votre site Laravel',
                'version' => '1.0.0',
            ],
            'servers' => [
                [
                    'url' => url('/api/seo'),
                    'description' => 'Serveur API SEO'
                ]
            ],
            'paths' => [
                '/meta' => [
                    'get' => [
                        'summary' => 'Récupérer les métadonnées SEO',
                        'parameters' => [
                            [
                                'name' => 'url',
                                'in' => 'query',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                                'description' => 'URL de la page'
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Métadonnées SEO récupérées avec succès'
                            ]
                        ]
                    ],
                    'post' => [
                        'summary' => 'Mettre à jour les métadonnées SEO',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'url' => ['type' => 'string'],
                                            'title' => ['type' => 'string'],
                                            'description' => ['type' => 'string'],
                                            'keywords' => ['type' => 'string'],
                                            'canonical' => ['type' => 'string'],
                                            'robots' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Métadonnées SEO mises à jour avec succès'
                            ]
                        ]
                    ]
                ],
                '/meta/bulk' => [
                    'get' => [
                        'summary' => 'Récupérer les métadonnées SEO en masse',
                        'parameters' => [
                            [
                                'name' => 'urls',
                                'in' => 'query',
                                'required' => true,
                                'schema' => ['type' => 'array', 'items' => ['type' => 'string']],
                                'description' => 'Liste des URLs à récupérer'
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Métadonnées SEO récupérées avec succès'
                            ]
                        ]
                    ],
                    'post' => [
                        'summary' => 'Mettre à jour les métadonnées SEO en masse',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'url' => ['type' => 'string'],
                                                'title' => ['type' => 'string'],
                                                'description' => ['type' => 'string'],
                                                'keywords' => ['type' => 'string'],
                                                'canonical' => ['type' => 'string'],
                                                'robots' => ['type' => 'string']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Métadonnées SEO mises à jour avec succès'
                            ]
                        ]
                    ]
                ],
                '/analyze' => [
                    'post' => [
                        'summary' => 'Analyser en temps réel les métadonnées et le contenu',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'title' => ['type' => 'string'],
                                            'description' => ['type' => 'string'],
                                            'keywords' => ['type' => 'string'],
                                            'content' => ['type' => 'string'],
                                            'url' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Analyse en temps réel effectuée avec succès'
                            ]
                        ]
                    ]
                ],
                '/analyze/meta' => [
                    'post' => [
                        'summary' => 'Analyser en temps réel les métadonnées',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'title' => ['type' => 'string'],
                                            'description' => ['type' => 'string'],
                                            'keywords' => ['type' => 'string'],
                                            'canonical' => ['type' => 'string'],
                                            'robots' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Analyse des métadonnées effectuée avec succès'
                            ]
                        ]
                    ]
                ],
                '/analyze/content' => [
                    'post' => [
                        'summary' => 'Analyser en temps réel le contenu',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'content' => ['type' => 'string'],
                                            'keywords' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Analyse du contenu effectuée avec succès'
                            ]
                        ]
                    ]
                ],
                '/analysis' => [
                    'get' => [
                        'summary' => 'Récupérer les analyses SEO',
                        'parameters' => [
                            [
                                'name' => 'url',
                                'in' => 'query',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                                'description' => 'URL de la page'
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Analyses SEO récupérées avec succès'
                            ]
                        ]
                    ],
                    'post' => [
                        'summary' => 'Créer une nouvelle analyse SEO',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'url' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Analyse SEO créée avec succès'
                            ]
                        ]
                    ]
                ],
                '/suggestions' => [
                    'get' => [
                        'summary' => 'Récupérer les suggestions SEO',
                        'parameters' => [
                            [
                                'name' => 'url',
                                'in' => 'query',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                                'description' => 'URL de la page'
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Suggestions SEO récupérées avec succès'
                            ]
                        ]
                    ]
                ],
                '/suggestions/realtime' => [
                    'post' => [
                        'summary' => 'Obtenir des suggestions SEO en temps réel',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'title' => ['type' => 'string'],
                                            'description' => ['type' => 'string'],
                                            'content' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Suggestions SEO en temps réel générées avec succès'
                            ]
                        ]
                    ]
                ],
                '/predict' => [
                    'post' => [
                        'summary' => 'Prédire le score SEO',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'url' => ['type' => 'string'],
                                            'content' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Prédiction du score SEO effectuée avec succès'
                            ]
                        ]
                    ]
                ],
                '/predict/realtime' => [
                    'post' => [
                        'summary' => 'Prédire le score SEO en temps réel',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'title' => ['type' => 'string'],
                                            'description' => ['type' => 'string'],
                                            'keywords' => ['type' => 'string'],
                                            'content' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Prédiction du score SEO en temps réel effectuée avec succès'
                            ]
                        ]
                    ]
                ],
                '/keywords/extract' => [
                    'post' => [
                        'summary' => 'Extraire les mots-clés d\'un contenu',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'content' => ['type' => 'string'],
                                            'count' => ['type' => 'integer']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Mots-clés extraits avec succès'
                            ]
                        ]
                    ]
                ],
                '/keywords/suggest' => [
                    'post' => [
                        'summary' => 'Suggérer des mots-clés liés',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'keyword' => ['type' => 'string'],
                                            'count' => ['type' => 'integer']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Suggestions de mots-clés générées avec succès'
                            ]
                        ]
                    ]
                ],
                '/keywords/density' => [
                    'post' => [
                        'summary' => 'Calculer la densité des mots-clés dans un contenu',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'content' => ['type' => 'string'],
                                            'keywords' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Densité des mots-clés calculée avec succès'
                            ]
                        ]
                    ]
                ],
                '/reports/performance' => [
                    'get' => [
                        'summary' => 'Obtenir un rapport de performance SEO',
                        'parameters' => [
                            [
                                'name' => 'period',
                                'in' => 'query',
                                'schema' => ['type' => 'string', 'enum' => ['week', 'month', 'year']],
                                'description' => 'Période du rapport'
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Rapport de performance généré avec succès'
                            ]
                        ]
                    ]
                ],
                '/sitemap.xml' => [
                    'get' => [
                        'summary' => 'Récupérer le sitemap XML',
                        'responses' => [
                            '200' => [
                                'description' => 'Sitemap XML récupéré avec succès',
                                'content' => [
                                    'application/xml' => []
                                ]
                            ]
                        ]
                    ]
                ],
                '/robots.txt' => [
                    'get' => [
                        'summary' => 'Récupérer le fichier robots.txt',
                        'responses' => [
                            '200' => [
                                'description' => 'Fichier robots.txt récupéré avec succès',
                                'content' => [
                                    'text/plain' => []
                                ]
                            ]
                        ]
                    ]
                ],
                '/health' => [
                    'get' => [
                        'summary' => 'Vérifier l\'état de santé de l\'API SEO',
                        'responses' => [
                            '200' => [
                                'description' => 'L\'API SEO fonctionne correctement'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
    
    return response()->json([
        'error' => 'La documentation OpenAPI n\'est pas activée dans la configuration.'
    ], 404);
}); 