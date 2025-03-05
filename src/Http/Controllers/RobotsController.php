<?php

namespace Seodably\SEO\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

class RobotsController extends Controller
{
    /**
     * Génère et affiche le fichier robots.txt.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Vérifier si la génération automatique est activée
        if (!config('seo.robots.auto_generate', true)) {
            abort(404);
        }

        // Récupérer le robots.txt depuis le cache ou le générer
        $cacheDuration = 60 * 24; // 24 heures
        $robots = Cache::remember('seo_robots', $cacheDuration, function () {
            return $this->generateRobots();
        });

        return Response::make($robots, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Génère le contenu du fichier robots.txt.
     *
     * @return string
     */
    protected function generateRobots(): string
    {
        $content = '';

        // Ajouter les règles par défaut
        $defaultRules = config('seo.robots.default_rules', [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
        ]);

        foreach ($defaultRules as $rule) {
            $content .= $rule . PHP_EOL;
        }

        // Ajouter le lien vers le sitemap
        if (config('seo.sitemap.auto_generate', true)) {
            $content .= PHP_EOL . 'Sitemap: ' . URL::to('/sitemap.xml') . PHP_EOL;
        }

        return $content;
    }
} 