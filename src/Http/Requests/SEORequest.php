<?php

namespace Seodably\SEO\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SEORequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Récupère les règles de validation qui s'appliquent à la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules()
    {
        return [
            'title' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:160',
            'keywords' => 'nullable|string',
            'canonical' => 'nullable|url',
            'robots' => 'nullable|string',
            'author' => 'nullable|string',
            'og_title' => 'nullable|string|max:60',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|url',
            'og_type' => 'nullable|string',
            'twitter_card' => 'nullable|string',
            'twitter_title' => 'nullable|string|max:60',
            'twitter_description' => 'nullable|string|max:160',
            'twitter_image' => 'nullable|url',
            'json_ld' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Récupère les messages d'erreur personnalisés pour les règles de validation.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'title.max' => 'Le titre ne doit pas dépasser 60 caractères.',
            'description.max' => 'La description ne doit pas dépasser 160 caractères.',
            'canonical.url' => 'L\'URL canonique doit être une URL valide.',
            'og_title.max' => 'Le titre Open Graph ne doit pas dépasser 60 caractères.',
            'og_description.max' => 'La description Open Graph ne doit pas dépasser 160 caractères.',
            'og_image.url' => 'L\'image Open Graph doit être une URL valide.',
            'twitter_title.max' => 'Le titre Twitter ne doit pas dépasser 60 caractères.',
            'twitter_description.max' => 'La description Twitter ne doit pas dépasser 160 caractères.',
            'twitter_image.url' => 'L\'image Twitter doit être une URL valide.',
        ];
    }
} 