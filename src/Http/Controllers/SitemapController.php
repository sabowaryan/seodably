<?php

namespace Seodably\SEO\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Response;
use Seodably\SEO\Models\SEO;

class SitemapController extends Controller
{
    /**
     * Génère et affiche le sitemap XML.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Vérifier si la génération automatique est activée
        if (!config('seo.sitemap.auto_generate', true)) {
            abort(404);
        }

        // Récupérer le sitemap depuis le cache ou le générer
        $cacheDuration = config('seo.sitemap.cache_duration', 60 * 24); // en minutes
        $sitemap = Cache::remember('seo_sitemap', $cacheDuration, function () {
            return $this->generateSitemap();
        });

        return Response::make($sitemap, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Génère le contenu du sitemap XML.
     *
     * @return string
     */
    protected function generateSitemap(): string
    {
        $urls = $this->collectUrls();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . e($url['loc']) . '</loc>';
            
            if (isset($url['lastmod'])) {
                $xml .= '<lastmod>' . e($url['lastmod']) . '</lastmod>';
            }
            
            if (isset($url['changefreq'])) {
                $xml .= '<changefreq>' . e($url['changefreq']) . '</changefreq>';
            }
            
            if (isset($url['priority'])) {
                $xml .= '<priority>' . e($url['priority']) . '</priority>';
            }
            
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Collecte les URLs à inclure dans le sitemap.
     *
     * @return array
     */
    protected function collectUrls(): array
    {
        $urls = [];
        $excludedPatterns = config('seo.sitemap.excluded_routes', []);

        // Ajouter les URLs depuis la base de données
        $seoEntries = SEO::where('is_active', true)->get();
        foreach ($seoEntries as $entry) {
            if ($this->shouldIncludeUrl($entry->url, $excludedPatterns)) {
                $urls[] = [
                    'loc' => $entry->url,
                    'lastmod' => $entry->updated_at->toIso8601String(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }
        }

        // Ajouter les URLs depuis les routes Laravel
        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            if ($route->methods()[0] !== 'GET') {
                continue;
            }

            $routeName = $route->getName();
            $routePath = $route->uri();

            // Exclure les routes d'API et les routes exclues
            if (str_starts_with($routePath, 'api/') || 
                $this->isExcludedRoute($routePath, $excludedPatterns) ||
                $this->isExcludedRoute($routeName, $excludedPatterns)) {
                continue;
            }

            // Exclure les routes avec des paramètres (sauf si elles sont déjà dans la base de données)
            if (str_contains($routePath, '{') && !$this->isRouteInSeoEntries($routeName, $seoEntries)) {
                continue;
            }

            try {
                $url = URL::to($routePath);
                
                // Éviter les doublons
                if (!$this->isUrlInArray($url, $urls)) {
                    $urls[] = [
                        'loc' => $url,
                        'changefreq' => 'monthly',
                        'priority' => '0.5',
                    ];
                }
            } catch (\Exception $e) {
                // Ignorer les routes qui ne peuvent pas être générées
                continue;
            }
        }

        // Ajouter les URLs personnalisées depuis la configuration
        $customUrls = config('seo.sitemap.custom_urls', []);
        foreach ($customUrls as $customUrl) {
            if (isset($customUrl['url']) && !$this->isUrlInArray($customUrl['url'], $urls)) {
                $urls[] = [
                    'loc' => $customUrl['url'],
                    'changefreq' => $customUrl['changefreq'] ?? 'monthly',
                    'priority' => $customUrl['priority'] ?? '0.5',
                ];
            }
        }

        return $urls;
    }

    /**
     * Vérifie si une URL doit être incluse dans le sitemap.
     *
     * @param string $url
     * @param array $excludedPatterns
     * @return bool
     */
    protected function shouldIncludeUrl(string $url, array $excludedPatterns): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $path = ltrim($path, '/');

        foreach ($excludedPatterns as $pattern) {
            if (fnmatch($pattern, $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifie si une route est exclue.
     *
     * @param string|null $route
     * @param array $excludedPatterns
     * @return bool
     */
    protected function isExcludedRoute(?string $route, array $excludedPatterns): bool
    {
        if (!$route) {
            return false;
        }

        foreach ($excludedPatterns as $pattern) {
            if (fnmatch($pattern, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si une route est déjà présente dans les entrées SEO.
     *
     * @param string|null $routeName
     * @param \Illuminate\Database\Eloquent\Collection $seoEntries
     * @return bool
     */
    protected function isRouteInSeoEntries(?string $routeName, $seoEntries): bool
    {
        if (!$routeName) {
            return false;
        }

        foreach ($seoEntries as $entry) {
            if ($entry->route_name === $routeName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si une URL est déjà présente dans le tableau d'URLs.
     *
     * @param string $url
     * @param array $urls
     * @return bool
     */
    protected function isUrlInArray(string $url, array $urls): bool
    {
        foreach ($urls as $existingUrl) {
            if ($existingUrl['loc'] === $url) {
                return true;
            }
        }

        return false;
    }
} 