<?php

namespace Seodably\SEO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Analysis extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'seo_analyses';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'seo_id',
        'url',
        'score',
        'content_length',
        'keyword_density',
        'readability_score',
        'has_meta_title',
        'has_meta_description',
        'has_meta_keywords',
        'has_canonical',
        'has_h1',
        'has_images_alt',
        'has_broken_links',
        'load_time',
        'issues',
        'suggestions',
        'ai_suggestions',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'float',
        'keyword_density' => 'float',
        'readability_score' => 'float',
        'has_meta_title' => 'boolean',
        'has_meta_description' => 'boolean',
        'has_meta_keywords' => 'boolean',
        'has_canonical' => 'boolean',
        'has_h1' => 'boolean',
        'has_images_alt' => 'boolean',
        'has_broken_links' => 'boolean',
        'load_time' => 'float',
        'issues' => 'array',
        'suggestions' => 'array',
        'ai_suggestions' => 'array',
    ];

    /**
     * Relation avec les métadonnées SEO.
     */
    public function seo()
    {
        return $this->belongsTo(SEO::class, 'seo_id');
    }

    /**
     * Récupère les analyses pour une URL spécifique.
     *
     * @param string $url
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findByUrl(string $url)
    {
        return static::where('url', $url)->latest()->get();
    }

    /**
     * Récupère la dernière analyse pour une URL spécifique.
     *
     * @param string $url
     * @return self|null
     */
    public static function getLatestByUrl(string $url)
    {
        return static::where('url', $url)->latest()->first();
    }

    /**
     * Détermine si l'analyse est considérée comme bonne.
     *
     * @return bool
     */
    public function isGood(): bool
    {
        return $this->score >= 80;
    }

    /**
     * Détermine si l'analyse est considérée comme moyenne.
     *
     * @return bool
     */
    public function isAverage(): bool
    {
        return $this->score >= 50 && $this->score < 80;
    }

    /**
     * Détermine si l'analyse est considérée comme mauvaise.
     *
     * @return bool
     */
    public function isBad(): bool
    {
        return $this->score < 50;
    }

    /**
     * Récupère les problèmes critiques de l'analyse.
     *
     * @return array
     */
    public function getCriticalIssues(): array
    {
        return array_filter($this->issues ?? [], function ($issue) {
            return $issue['severity'] === 'critical';
        });
    }

    /**
     * Récupère les problèmes importants de l'analyse.
     *
     * @return array
     */
    public function getImportantIssues(): array
    {
        return array_filter($this->issues ?? [], function ($issue) {
            return $issue['severity'] === 'important';
        });
    }

    /**
     * Récupère les problèmes mineurs de l'analyse.
     *
     * @return array
     */
    public function getMinorIssues(): array
    {
        return array_filter($this->issues ?? [], function ($issue) {
            return $issue['severity'] === 'minor';
        });
    }
} 