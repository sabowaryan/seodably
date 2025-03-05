<?php

namespace Seodably\SEO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SEO extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'seo_metadata';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'url',
        'route_name',
        'title',
        'description',
        'keywords',
        'canonical',
        'robots',
        'author',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'json_ld',
        'is_active',
        'last_analyzed_at',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'json_ld' => 'array',
        'is_active' => 'boolean',
        'last_analyzed_at' => 'datetime',
    ];

    /**
     * Relation avec les analyses SEO.
     */
    public function analyses()
    {
        return $this->hasMany(Analysis::class, 'seo_id');
    }

    /**
     * Récupère la dernière analyse SEO.
     */
    public function latestAnalysis()
    {
        return $this->hasOne(Analysis::class, 'seo_id')->latest();
    }

    /**
     * Récupère les métadonnées SEO pour une URL spécifique.
     *
     * @param string $url
     * @return self|null
     */
    public static function findByUrl(string $url)
    {
        return static::where('url', $url)->where('is_active', true)->first();
    }

    /**
     * Récupère les métadonnées SEO pour un nom de route spécifique.
     *
     * @param string $routeName
     * @return self|null
     */
    public static function findByRouteName(string $routeName)
    {
        return static::where('route_name', $routeName)->where('is_active', true)->first();
    }

    /**
     * Génère un tableau de balises méta HTML.
     *
     * @return array
     */
    public function generateMetaTags(): array
    {
        $tags = [];

        // Balise title
        if ($this->title) {
            $tags['title'] = $this->title;
        }

        // Balises meta
        if ($this->description) {
            $tags['meta_description'] = [
                'name' => 'description',
                'content' => $this->description,
            ];
        }

        if ($this->keywords) {
            $tags['meta_keywords'] = [
                'name' => 'keywords',
                'content' => $this->keywords,
            ];
        }

        if ($this->robots) {
            $tags['meta_robots'] = [
                'name' => 'robots',
                'content' => $this->robots,
            ];
        }

        if ($this->author) {
            $tags['meta_author'] = [
                'name' => 'author',
                'content' => $this->author,
            ];
        }

        // Balises Open Graph
        if ($this->og_title) {
            $tags['meta_og_title'] = [
                'property' => 'og:title',
                'content' => $this->og_title,
            ];
        }

        if ($this->og_description) {
            $tags['meta_og_description'] = [
                'property' => 'og:description',
                'content' => $this->og_description,
            ];
        }

        if ($this->og_image) {
            $tags['meta_og_image'] = [
                'property' => 'og:image',
                'content' => $this->og_image,
            ];
        }

        if ($this->og_type) {
            $tags['meta_og_type'] = [
                'property' => 'og:type',
                'content' => $this->og_type,
            ];
        }

        // Balises Twitter
        if ($this->twitter_card) {
            $tags['meta_twitter_card'] = [
                'name' => 'twitter:card',
                'content' => $this->twitter_card,
            ];
        }

        if ($this->twitter_title) {
            $tags['meta_twitter_title'] = [
                'name' => 'twitter:title',
                'content' => $this->twitter_title,
            ];
        }

        if ($this->twitter_description) {
            $tags['meta_twitter_description'] = [
                'name' => 'twitter:description',
                'content' => $this->twitter_description,
            ];
        }

        if ($this->twitter_image) {
            $tags['meta_twitter_image'] = [
                'name' => 'twitter:image',
                'content' => $this->twitter_image,
            ];
        }

        // Balise canonique
        if ($this->canonical) {
            $tags['link_canonical'] = [
                'rel' => 'canonical',
                'href' => $this->canonical,
            ];
        }

        // JSON-LD
        if ($this->json_ld) {
            $tags['json_ld'] = $this->json_ld;
        }

        return $tags;
    }
} 