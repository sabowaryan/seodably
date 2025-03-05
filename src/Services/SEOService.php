<?php

namespace Seodably\SEO\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Seodably\SEO\Models\SEO;

class SEOService
{
    /**
     * Les métadonnées SEO actuelles.
     *
     * @var array
     */
    protected $meta = [];

    /**
     * L'URL actuelle.
     *
     * @var string|null
     */
    protected $currentUrl = null;

    /**
     * Le nom de la route actuelle.
     *
     * @var string|null
     */
    protected $currentRouteName = null;

    /**
     * Initialise le service SEO.
     */
    public function __construct()
    {
        $this->currentUrl = URL::current();
        $this->currentRouteName = Route::currentRouteName();
        $this->loadDefaultMeta();
    }

    /**
     * Charge les métadonnées par défaut depuis la configuration.
     *
     * @return $this
     */
    public function loadDefaultMeta()
    {
        $this->meta = [
            'title' => config('seo.defaults.title'),
            'description' => config('seo.defaults.description'),
            'keywords' => config('seo.defaults.keywords'),
            'robots' => config('seo.defaults.robots'),
            'author' => config('seo.defaults.author'),
            'og_type' => config('seo.defaults.og_type'),
            'twitter_card' => config('seo.defaults.twitter_card'),
        ];

        return $this;
    }

    /**
     * Charge les métadonnées depuis la base de données pour l'URL actuelle.
     *
     * @return $this
     */
    public function loadMetaFromDatabase()
    {
        if (!config('seo.enabled', true)) {
            return $this;
        }

        $cacheKey = 'seo_meta_' . md5($this->currentUrl);
        $cacheDuration = 60; // minutes

        $seoData = Cache::remember($cacheKey, $cacheDuration, function () {
            // Essayer de trouver par URL
            $seo = SEO::findByUrl($this->currentUrl);

            // Si non trouvé, essayer par nom de route
            if (!$seo && $this->currentRouteName) {
                $seo = SEO::findByRouteName($this->currentRouteName);
            }

            return $seo;
        });

        if ($seoData) {
            $this->setTitle($seoData->title)
                ->setDescription($seoData->description)
                ->setKeywords($seoData->keywords)
                ->setCanonical($seoData->canonical)
                ->setRobots($seoData->robots)
                ->setAuthor($seoData->author)
                ->setOgTitle($seoData->og_title)
                ->setOgDescription($seoData->og_description)
                ->setOgImage($seoData->og_image)
                ->setOgType($seoData->og_type)
                ->setTwitterCard($seoData->twitter_card)
                ->setTwitterTitle($seoData->twitter_title)
                ->setTwitterDescription($seoData->twitter_description)
                ->setTwitterImage($seoData->twitter_image)
                ->setJsonLd($seoData->json_ld);
        }

        return $this;
    }

    /**
     * Définit le titre de la page.
     *
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title)
    {
        if ($title) {
            $this->meta['title'] = $title;
            $this->meta['og_title'] = $this->meta['og_title'] ?? $title;
            $this->meta['twitter_title'] = $this->meta['twitter_title'] ?? $title;
        }

        return $this;
    }

    /**
     * Définit la description de la page.
     *
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description)
    {
        if ($description) {
            $this->meta['description'] = $description;
            $this->meta['og_description'] = $this->meta['og_description'] ?? $description;
            $this->meta['twitter_description'] = $this->meta['twitter_description'] ?? $description;
        }

        return $this;
    }

    /**
     * Définit les mots-clés de la page.
     *
     * @param string|null $keywords
     * @return $this
     */
    public function setKeywords(?string $keywords)
    {
        if ($keywords) {
            $this->meta['keywords'] = $keywords;
        }

        return $this;
    }

    /**
     * Définit l'URL canonique de la page.
     *
     * @param string|null $canonical
     * @return $this
     */
    public function setCanonical(?string $canonical)
    {
        if ($canonical) {
            $this->meta['canonical'] = $canonical;
        }

        return $this;
    }

    /**
     * Définit les règles robots de la page.
     *
     * @param string|null $robots
     * @return $this
     */
    public function setRobots(?string $robots)
    {
        if ($robots) {
            $this->meta['robots'] = $robots;
        }

        return $this;
    }

    /**
     * Définit l'auteur de la page.
     *
     * @param string|null $author
     * @return $this
     */
    public function setAuthor(?string $author)
    {
        if ($author) {
            $this->meta['author'] = $author;
        }

        return $this;
    }

    /**
     * Définit le titre Open Graph de la page.
     *
     * @param string|null $ogTitle
     * @return $this
     */
    public function setOgTitle(?string $ogTitle)
    {
        if ($ogTitle) {
            $this->meta['og_title'] = $ogTitle;
        }

        return $this;
    }

    /**
     * Définit la description Open Graph de la page.
     *
     * @param string|null $ogDescription
     * @return $this
     */
    public function setOgDescription(?string $ogDescription)
    {
        if ($ogDescription) {
            $this->meta['og_description'] = $ogDescription;
        }

        return $this;
    }

    /**
     * Définit l'image Open Graph de la page.
     *
     * @param string|null $ogImage
     * @return $this
     */
    public function setOgImage(?string $ogImage)
    {
        if ($ogImage) {
            $this->meta['og_image'] = $ogImage;
            $this->meta['twitter_image'] = $this->meta['twitter_image'] ?? $ogImage;
        }

        return $this;
    }

    /**
     * Définit le type Open Graph de la page.
     *
     * @param string|null $ogType
     * @return $this
     */
    public function setOgType(?string $ogType)
    {
        if ($ogType) {
            $this->meta['og_type'] = $ogType;
        }

        return $this;
    }

    /**
     * Définit le type de carte Twitter de la page.
     *
     * @param string|null $twitterCard
     * @return $this
     */
    public function setTwitterCard(?string $twitterCard)
    {
        if ($twitterCard) {
            $this->meta['twitter_card'] = $twitterCard;
        }

        return $this;
    }

    /**
     * Définit le titre Twitter de la page.
     *
     * @param string|null $twitterTitle
     * @return $this
     */
    public function setTwitterTitle(?string $twitterTitle)
    {
        if ($twitterTitle) {
            $this->meta['twitter_title'] = $twitterTitle;
        }

        return $this;
    }

    /**
     * Définit la description Twitter de la page.
     *
     * @param string|null $twitterDescription
     * @return $this
     */
    public function setTwitterDescription(?string $twitterDescription)
    {
        if ($twitterDescription) {
            $this->meta['twitter_description'] = $twitterDescription;
        }

        return $this;
    }

    /**
     * Définit l'image Twitter de la page.
     *
     * @param string|null $twitterImage
     * @return $this
     */
    public function setTwitterImage(?string $twitterImage)
    {
        if ($twitterImage) {
            $this->meta['twitter_image'] = $twitterImage;
        }

        return $this;
    }

    /**
     * Définit les données JSON-LD de la page.
     *
     * @param array|null $jsonLd
     * @return $this
     */
    public function setJsonLd(?array $jsonLd)
    {
        if ($jsonLd) {
            $this->meta['json_ld'] = $jsonLd;
        }

        return $this;
    }

    /**
     * Définit une image pour toutes les balises sociales.
     *
     * @param string $image
     * @return $this
     */
    public function setImage(string $image)
    {
        return $this->setOgImage($image)->setTwitterImage($image);
    }

    /**
     * Récupère toutes les métadonnées.
     *
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Récupère une métadonnée spécifique.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->meta[$key] ?? $default;
    }

    /**
     * Génère les balises HTML pour les métadonnées.
     *
     * @return string
     */
    public function generateHtml(): string
    {
        $html = '';

        // Titre
        if (isset($this->meta['title'])) {
            $html .= '<title>' . e($this->meta['title']) . '</title>' . PHP_EOL;
        }

        // Meta description
        if (isset($this->meta['description'])) {
            $html .= '<meta name="description" content="' . e($this->meta['description']) . '">' . PHP_EOL;
        }

        // Meta keywords
        if (isset($this->meta['keywords'])) {
            $html .= '<meta name="keywords" content="' . e($this->meta['keywords']) . '">' . PHP_EOL;
        }

        // Meta robots
        if (isset($this->meta['robots'])) {
            $html .= '<meta name="robots" content="' . e($this->meta['robots']) . '">' . PHP_EOL;
        }

        // Meta author
        if (isset($this->meta['author'])) {
            $html .= '<meta name="author" content="' . e($this->meta['author']) . '">' . PHP_EOL;
        }

        // Canonical
        if (isset($this->meta['canonical'])) {
            $html .= '<link rel="canonical" href="' . e($this->meta['canonical']) . '">' . PHP_EOL;
        }

        // Open Graph
        if (isset($this->meta['og_title'])) {
            $html .= '<meta property="og:title" content="' . e($this->meta['og_title']) . '">' . PHP_EOL;
        }

        if (isset($this->meta['og_description'])) {
            $html .= '<meta property="og:description" content="' . e($this->meta['og_description']) . '">' . PHP_EOL;
        }

        if (isset($this->meta['og_image'])) {
            $html .= '<meta property="og:image" content="' . e($this->meta['og_image']) . '">' . PHP_EOL;
        }

        if (isset($this->meta['og_type'])) {
            $html .= '<meta property="og:type" content="' . e($this->meta['og_type']) . '">' . PHP_EOL;
        }

        $html .= '<meta property="og:url" content="' . e($this->currentUrl) . '">' . PHP_EOL;

        // Twitter
        if (isset($this->meta['twitter_card'])) {
            $html .= '<meta name="twitter:card" content="' . e($this->meta['twitter_card']) . '">' . PHP_EOL;
        }

        if (isset($this->meta['twitter_title'])) {
            $html .= '<meta name="twitter:title" content="' . e($this->meta['twitter_title']) . '">' . PHP_EOL;
        }

        if (isset($this->meta['twitter_description'])) {
            $html .= '<meta name="twitter:description" content="' . e($this->meta['twitter_description']) . '">' . PHP_EOL;
        }

        if (isset($this->meta['twitter_image'])) {
            $html .= '<meta name="twitter:image" content="' . e($this->meta['twitter_image']) . '">' . PHP_EOL;
        }

        // JSON-LD
        if (isset($this->meta['json_ld']) && !empty($this->meta['json_ld'])) {
            $html .= '<script type="application/ld+json">' . json_encode($this->meta['json_ld']) . '</script>' . PHP_EOL;
        }

        return $html;
    }

    /**
     * Enregistre les métadonnées dans la base de données.
     *
     * @param string|null $url
     * @param string|null $routeName
     * @return SEO
     */
    public function save(?string $url = null, ?string $routeName = null): SEO
    {
        $url = $url ?? $this->currentUrl;
        $routeName = $routeName ?? $this->currentRouteName;

        $seo = SEO::updateOrCreate(
            ['url' => $url],
            [
                'route_name' => $routeName,
                'title' => $this->meta['title'] ?? null,
                'description' => $this->meta['description'] ?? null,
                'keywords' => $this->meta['keywords'] ?? null,
                'canonical' => $this->meta['canonical'] ?? null,
                'robots' => $this->meta['robots'] ?? null,
                'author' => $this->meta['author'] ?? null,
                'og_title' => $this->meta['og_title'] ?? null,
                'og_description' => $this->meta['og_description'] ?? null,
                'og_image' => $this->meta['og_image'] ?? null,
                'og_type' => $this->meta['og_type'] ?? null,
                'twitter_card' => $this->meta['twitter_card'] ?? null,
                'twitter_title' => $this->meta['twitter_title'] ?? null,
                'twitter_description' => $this->meta['twitter_description'] ?? null,
                'twitter_image' => $this->meta['twitter_image'] ?? null,
                'json_ld' => $this->meta['json_ld'] ?? null,
                'is_active' => true,
            ]
        );

        // Invalider le cache
        $cacheKey = 'seo_meta_' . md5($url);
        Cache::forget($cacheKey);

        return $seo;
    }
} 