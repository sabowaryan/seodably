<?php

namespace Seodably\SEO\Services;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use Seodably\SEO\Services\SEOService;
use Symfony\Component\HttpFoundation\Response;

class SEOMiddleware
{
    /**
     * Le service SEO.
     *
     * @var SEOService
     */
    protected $seoService;

    /**
     * Crée une nouvelle instance du middleware.
     *
     * @param SEOService $seoService
     */
    public function __construct(SEOService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Traite la requête entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si le SEO est activé
        if (!config('seo.enabled', true)) {
            return $next($request);
        }

        // Charger les métadonnées depuis la base de données
        $this->seoService->loadMetaFromDatabase();

        // Partager les métadonnées avec Inertia
        if (class_exists(Inertia::class)) {
            Inertia::share('seo', $this->seoService->getMeta());
        }

        // Continuer le traitement de la requête
        $response = $next($request);

        // Injecter les balises méta dans la réponse HTML
        if ($response instanceof Response && $this->isHtmlResponse($response)) {
            $this->injectMetaTags($response);
        }

        return $response;
    }

    /**
     * Vérifie si la réponse est de type HTML.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return bool
     */
    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type');
        return $contentType && (
            str_contains($contentType, 'text/html') ||
            str_contains($contentType, 'application/xhtml+xml')
        );
    }

    /**
     * Injecte les balises méta dans la réponse HTML.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    protected function injectMetaTags(Response $response): void
    {
        $content = $response->getContent();
        
        if (!$content || !str_contains($content, '</head>')) {
            return;
        }
        
        $metaTags = $this->seoService->generateHtml();
        
        // Injecter les balises méta avant la fermeture de la balise head
        $content = str_replace('</head>', $metaTags . '</head>', $content);
        
        $response->setContent($content);
    }
} 