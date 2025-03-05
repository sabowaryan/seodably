<?php

namespace Seodably\SEO\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * Le nom et la signature de la commande console.
     *
     * @var string
     */
    protected $signature = 'seo:install {--force : Écraser les fichiers existants}';

    /**
     * La description de la commande console.
     *
     * @var string
     */
    protected $description = 'Installe et configure le package SEO';

    /**
     * Exécute la commande console.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Installation du package SEO...');

        // Publier la configuration
        $this->publishConfig();

        // Publier les migrations
        $this->publishMigrations();

        // Publier les assets Vue.js
        $this->publishAssets();

        // Exécuter les migrations
        if ($this->confirm('Voulez-vous exécuter les migrations maintenant?', true)) {
            $this->call('migrate');
        }

        $this->info('Le package SEO a été installé avec succès!');
        $this->info('Vous pouvez maintenant accéder au tableau de bord SEO via l\'URL configurée dans config/seo.php.');

        return Command::SUCCESS;
    }

    /**
     * Publie le fichier de configuration.
     *
     * @return void
     */
    protected function publishConfig()
    {
        $this->info('Publication de la configuration...');

        $params = ['--provider' => 'Seodably\SEO\SEOServiceProvider', '--tag' => 'seo-config'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Publie les migrations.
     *
     * @return void
     */
    protected function publishMigrations()
    {
        $this->info('Publication des migrations...');

        $params = ['--provider' => 'Seodably\SEO\SEOServiceProvider', '--tag' => 'seo-migrations'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Publie les assets Vue.js.
     *
     * @return void
     */
    protected function publishAssets()
    {
        $this->info('Publication des assets Vue.js...');

        $params = ['--provider' => 'Seodably\SEO\SEOServiceProvider', '--tag' => 'seo-assets'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);

        // Vérifier si Inertia.js est installé
        if (!File::exists(resource_path('js/app.js'))) {
            $this->warn('Inertia.js ne semble pas être installé. Veuillez installer Inertia.js pour utiliser l\'interface Vue.js.');
            $this->warn('Consultez la documentation : https://inertiajs.com/');
        }
    }
} 