<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration générale
    |--------------------------------------------------------------------------
    |
    | Paramètres généraux pour le package SEO
    |
    */
    'enabled' => env('SEO_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Configuration de l'IA
    |--------------------------------------------------------------------------
    |
    | Paramètres pour le service d'intelligence artificielle
    |
    */
    'ai' => [
        'enabled' => env('SEO_AI_ENABLED', true),
        'suggestions_count' => env('SEO_AI_SUGGESTIONS_COUNT', 5),
        'api_key' => env('SEO_AI_API_KEY', null),
        'model' => env('SEO_AI_MODEL', 'gpt-4'),
        'temperature' => env('SEO_AI_TEMPERATURE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration de l'interface
    |--------------------------------------------------------------------------
    |
    | Paramètres pour l'interface utilisateur
    |
    */
    'ui' => [
        'theme' => env('SEO_UI_THEME', 'tailwind'), // 'tailwind' ou 'bootstrap'
        'dashboard_route' => env('SEO_UI_DASHBOARD_ROUTE', 'admin/seo'),
        'enable_dark_mode' => env('SEO_UI_DARK_MODE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration du sitemap
    |--------------------------------------------------------------------------
    |
    | Paramètres pour la génération du sitemap
    |
    */
    'sitemap' => [
        'auto_generate' => env('SEO_SITEMAP_AUTO_GENERATE', true),
        'cache_duration' => env('SEO_SITEMAP_CACHE_DURATION', 60 * 24), // en minutes
        'excluded_routes' => [
            'admin/*',
            'login',
            'logout',
            'register',
            'password/*',
        ],
        'custom_urls' => [
            // Exemple: ['url' => 'https://example.com/custom-page', 'priority' => '0.8', 'changefreq' => 'monthly']
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration des robots.txt
    |--------------------------------------------------------------------------
    |
    | Paramètres pour la génération du fichier robots.txt
    |
    */
    'robots' => [
        'auto_generate' => env('SEO_ROBOTS_AUTO_GENERATE', true),
        'default_rules' => [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /register',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration des balises méta par défaut
    |--------------------------------------------------------------------------
    |
    | Balises méta par défaut pour les pages sans configuration spécifique
    |
    */
    'defaults' => [
        'title' => env('APP_NAME', 'Laravel') . ' | Site Web',
        'description' => 'Description par défaut du site web',
        'keywords' => 'laravel, web, application',
        'robots' => 'index, follow',
        'author' => env('APP_NAME', 'Laravel'),
        'og_type' => 'website',
        'twitter_card' => 'summary_large_image',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration de l'analyse SEO
    |--------------------------------------------------------------------------
    |
    | Paramètres pour l'analyse SEO des pages
    |
    */
    'analysis' => [
        'min_content_length' => 300,
        'keyword_density_min' => 1,
        'keyword_density_max' => 3,
        'readability_check' => true,
        'broken_links_check' => true,
        'image_alt_check' => true,
        'heading_structure_check' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration de l'API
    |--------------------------------------------------------------------------
    |
    | Paramètres pour l'API REST
    |
    */
    'api' => [
        'rate_limit' => env('SEO_API_RATE_LIMIT', 60),
        'require_auth' => env('SEO_API_REQUIRE_AUTH', true),
    ],
]; 