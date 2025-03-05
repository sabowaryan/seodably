# Seodably SEO

Un package SEO avancé pour Laravel 12 avec analyse en temps réel, IA et interface Vue.js/Inertia.js.

## Caractéristiques

- ✅ Analyse SEO en temps réel grâce à l'IA pour l'optimisation, la prédiction et la suggestion
- ✅ Facilité d'intégration dans n'importe quel projet Laravel
- ✅ Personnalisation avancée du design et des règles SEO via une interface Vue.js et un fichier de configuration
- ✅ Architecture modulaire & API REST pour permettre l'extension du package
- ✅ Gestion automatique des balises méta, sitemap, robots.txt sans packages externes

## Installation

```bash
composer require seodably/seo
```

Puis exécutez la commande d'installation :

```bash
php artisan seo:install
```

Cette commande va :
- Publier les fichiers de configuration
- Exécuter les migrations nécessaires
- Publier les assets Vue.js/Inertia.js

## Configuration

Après l'installation, vous pouvez modifier le fichier de configuration dans `config/seo.php` :

```php
return [
    // Configuration de base
    'enabled' => true,
    
    // Paramètres de l'IA
    'ai' => [
        'enabled' => true,
        'suggestions_count' => 5,
    ],
    
    // Configuration de l'interface
    'ui' => [
        'theme' => 'tailwind', // ou 'bootstrap'
        'dashboard_route' => 'admin/seo',
    ],
    
    // Configuration du sitemap
    'sitemap' => [
        'auto_generate' => true,
        'excluded_routes' => [
            'admin/*',
        ],
    ],
    
    // Configuration des robots.txt
    'robots' => [
        'auto_generate' => true,
        'default_rules' => [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
        ],
    ],
];
```

## Utilisation

### Middleware

Ajoutez le middleware à votre groupe de routes ou à des routes spécifiques :

```php
Route::middleware(['web', 'seo'])->group(function () {
    // Vos routes
});
```

### Balises méta

Dans vos contrôleurs, vous pouvez définir des balises méta spécifiques :

```php
use Seodably\SEO\Facades\SEO;

public function show(Post $post)
{
    SEO::setTitle($post->title)
       ->setDescription($post->excerpt)
       ->setCanonical(route('posts.show', $post))
       ->setImage($post->featured_image);
       
    return Inertia::render('Post/Show', [
        'post' => $post
    ]);
}
```

### Dashboard SEO

Accédez au tableau de bord SEO via l'URL configurée (par défaut : `/admin/seo`).

### API REST

Exemple d'utilisation de l'API :

```php
// Récupérer les métadonnées d'une page
$response = Http::get('/api/seo/page', [
    'url' => 'https://votre-site.com/page'
]);

// Mettre à jour les métadonnées
$response = Http::post('/api/seo/update', [
    'url' => 'https://votre-site.com/page',
    'title' => 'Nouveau titre',
    'description' => 'Nouvelle description'
]);
```

## Documentation complète

Pour une documentation complète, visitez [https://docs.seodably.com](https://docs.seodably.com).

## Licence

Ce package est sous licence MIT. Voir le fichier LICENSE pour plus de détails. 