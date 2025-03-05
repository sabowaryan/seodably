<template>
  <div class="seo-settings">
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Paramètres SEO</h1>
      <p class="text-gray-600 dark:text-gray-400 mt-2">
        Configurez les paramètres généraux du système SEO pour votre site.
      </p>
    </div>
    
    <form @submit.prevent="saveSettings" class="space-y-8">
      <!-- Paramètres généraux -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-semibold">Paramètres généraux</h2>
        </div>
        
        <div class="p-6 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label for="enabled" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Activer le SEO
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Active ou désactive toutes les fonctionnalités SEO sur le site.
              </p>
            </div>
            <div class="ml-4">
              <button 
                type="button" 
                @click="formData.enabled = !formData.enabled"
                :class="[
                  'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                  formData.enabled ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'
                ]"
              >
                <span 
                  :class="[
                    'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
                    formData.enabled ? 'translate-x-5' : 'translate-x-0'
                  ]"
                ></span>
              </button>
            </div>
          </div>
          
          <div>
            <label for="dashboard_route" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Route du tableau de bord
            </label>
            <div class="flex">
              <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-gray-500 dark:text-gray-400 sm:text-sm">
                /
              </span>
              <input 
                id="dashboard_route" 
                v-model="formData.dashboard_route" 
                type="text" 
                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="admin/seo"
              />
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Chemin d'accès au tableau de bord SEO (sans le "/" initial).
            </p>
          </div>
          
          <div>
            <label for="cache_duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Durée du cache (minutes)
            </label>
            <input 
              id="cache_duration" 
              v-model.number="formData.cache_duration" 
              type="number" 
              min="0"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Durée de mise en cache des métadonnées SEO (0 pour désactiver).
            </p>
          </div>
        </div>
      </div>
      
      <!-- Paramètres de l'IA -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-semibold">Intelligence Artificielle</h2>
        </div>
        
        <div class="p-6 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label for="ai_enabled" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Activer l'IA
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Active ou désactive les fonctionnalités d'IA pour l'analyse et les suggestions.
              </p>
            </div>
            <div class="ml-4">
              <button 
                type="button" 
                @click="formData.ai.enabled = !formData.ai.enabled"
                :class="[
                  'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                  formData.ai.enabled ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'
                ]"
              >
                <span 
                  :class="[
                    'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
                    formData.ai.enabled ? 'translate-x-5' : 'translate-x-0'
                  ]"
                ></span>
              </button>
            </div>
          </div>
          
          <div>
            <label for="ai_suggestions_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Nombre de suggestions
            </label>
            <input 
              id="ai_suggestions_count" 
              v-model.number="formData.ai.suggestions_count" 
              type="number" 
              min="1"
              max="10"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Nombre de suggestions générées par l'IA pour chaque type d'amélioration.
            </p>
          </div>
          
          <div>
            <label for="ai_model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Modèle d'IA
            </label>
            <select 
              id="ai_model" 
              v-model="formData.ai.model" 
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
              <option value="gpt-3.5-turbo">GPT-3.5 Turbo (Rapide)</option>
              <option value="gpt-4">GPT-4 (Précis)</option>
            </select>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Modèle d'IA à utiliser pour les analyses et suggestions.
            </p>
          </div>
        </div>
      </div>
      
      <!-- Paramètres du sitemap -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-semibold">Sitemap</h2>
        </div>
        
        <div class="p-6 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label for="sitemap_auto_generate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Génération automatique
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Génère automatiquement le sitemap.xml à partir des routes et des pages indexées.
              </p>
            </div>
            <div class="ml-4">
              <button 
                type="button" 
                @click="formData.sitemap.auto_generate = !formData.sitemap.auto_generate"
                :class="[
                  'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                  formData.sitemap.auto_generate ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'
                ]"
              >
                <span 
                  :class="[
                    'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
                    formData.sitemap.auto_generate ? 'translate-x-5' : 'translate-x-0'
                  ]"
                ></span>
              </button>
            </div>
          </div>
          
          <div>
            <label for="sitemap_cache_duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Durée du cache (heures)
            </label>
            <input 
              id="sitemap_cache_duration" 
              v-model.number="formData.sitemap.cache_duration" 
              type="number" 
              min="1"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Durée de mise en cache du sitemap en heures.
            </p>
          </div>
          
          <div>
            <label for="sitemap_excluded_routes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Routes exclues
            </label>
            <textarea 
              id="sitemap_excluded_routes" 
              v-model="excludedRoutesText" 
              rows="4"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="admin/*&#10;login&#10;register"
            ></textarea>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Routes à exclure du sitemap (une par ligne, les jokers * sont acceptés).
            </p>
          </div>
        </div>
      </div>
      
      <!-- Paramètres des robots.txt -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-semibold">Robots.txt</h2>
        </div>
        
        <div class="p-6 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label for="robots_auto_generate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Génération automatique
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Génère automatiquement le fichier robots.txt.
              </p>
            </div>
            <div class="ml-4">
              <button 
                type="button" 
                @click="formData.robots.auto_generate = !formData.robots.auto_generate"
                :class="[
                  'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                  formData.robots.auto_generate ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'
                ]"
              >
                <span 
                  :class="[
                    'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
                    formData.robots.auto_generate ? 'translate-x-5' : 'translate-x-0'
                  ]"
                ></span>
              </button>
            </div>
          </div>
          
          <div>
            <label for="robots_default_rules" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Règles par défaut
            </label>
            <textarea 
              id="robots_default_rules" 
              v-model="defaultRulesText" 
              rows="6"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-mono"
              placeholder="User-agent: *&#10;Allow: /&#10;Disallow: /admin"
            ></textarea>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Règles par défaut pour le fichier robots.txt (une par ligne).
            </p>
          </div>
          
          <div class="flex items-center justify-between">
            <div>
              <label for="robots_add_sitemap" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Ajouter le sitemap
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Ajoute automatiquement l'URL du sitemap au fichier robots.txt.
              </p>
            </div>
            <div class="ml-4">
              <button 
                type="button" 
                @click="formData.robots.add_sitemap = !formData.robots.add_sitemap"
                :class="[
                  'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                  formData.robots.add_sitemap ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'
                ]"
              >
                <span 
                  :class="[
                    'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
                    formData.robots.add_sitemap ? 'translate-x-5' : 'translate-x-0'
                  ]"
                ></span>
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Paramètres des métadonnées -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-semibold">Métadonnées</h2>
        </div>
        
        <div class="p-6 space-y-4">
          <div>
            <label for="meta_title_max_length" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Longueur maximale du titre
            </label>
            <input 
              id="meta_title_max_length" 
              v-model.number="formData.meta.max_title_length" 
              type="number" 
              min="10"
              max="100"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Longueur maximale recommandée pour les titres (en caractères).
            </p>
          </div>
          
          <div>
            <label for="meta_description_max_length" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Longueur maximale de la description
            </label>
            <input 
              id="meta_description_max_length" 
              v-model.number="formData.meta.max_description_length" 
              type="number" 
              min="50"
              max="300"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Longueur maximale recommandée pour les descriptions (en caractères).
            </p>
          </div>
          
          <div>
            <label for="meta_default_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Image par défaut
            </label>
            <input 
              id="meta_default_image" 
              v-model="formData.meta.default_image" 
              type="url" 
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="https://exemple.com/images/default.jpg"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              URL de l'image par défaut à utiliser pour les balises Open Graph et Twitter.
            </p>
          </div>
        </div>
      </div>
      
      <!-- Boutons d'action -->
      <div class="flex justify-end space-x-3">
        <button 
          type="button" 
          @click="resetForm" 
          class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition"
        >
          Réinitialiser
        </button>
        <button 
          type="submit" 
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
          :disabled="isSaving"
        >
          {{ isSaving ? 'Enregistrement...' : 'Enregistrer les modifications' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
  config: {
    type: Object,
    required: true
  }
});

const isSaving = ref(false);
const formData = ref({
  enabled: props.config.enabled,
  dashboard_route: props.config.ui?.dashboard_route || 'admin/seo',
  cache_duration: props.config.cache_duration || 60,
  ai: {
    enabled: props.config.ai?.enabled || true,
    suggestions_count: props.config.ai?.suggestions_count || 5,
    model: props.config.ai?.model || 'gpt-3.5-turbo'
  },
  sitemap: {
    auto_generate: props.config.sitemap?.auto_generate || true,
    cache_duration: props.config.sitemap?.cache_duration || 24,
    excluded_routes: props.config.sitemap?.excluded_routes || ['admin/*']
  },
  robots: {
    auto_generate: props.config.robots?.auto_generate || true,
    default_rules: props.config.robots?.default_rules || [
      'User-agent: *',
      'Allow: /',
      'Disallow: /admin'
    ],
    add_sitemap: props.config.robots?.add_sitemap || true
  },
  meta: {
    max_title_length: props.config.meta?.max_title_length || 60,
    max_description_length: props.config.meta?.max_description_length || 160,
    default_image: props.config.meta?.default_image || ''
  }
});

const excludedRoutesText = computed({
  get: () => formData.value.sitemap.excluded_routes.join('\n'),
  set: (value) => {
    formData.value.sitemap.excluded_routes = value
      .split('\n')
      .map(route => route.trim())
      .filter(route => route.length > 0);
  }
});

const defaultRulesText = computed({
  get: () => formData.value.robots.default_rules.join('\n'),
  set: (value) => {
    formData.value.robots.default_rules = value
      .split('\n')
      .map(rule => rule.trim())
      .filter(rule => rule.length > 0);
  }
});

const saveSettings = async () => {
  isSaving.value = true;
  
  try {
    await $inertia.post('/admin/seo/settings', formData.value);
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement des paramètres:', error);
  } finally {
    isSaving.value = false;
  }
};

const resetForm = () => {
  formData.value = {
    enabled: props.config.enabled,
    dashboard_route: props.config.ui?.dashboard_route || 'admin/seo',
    cache_duration: props.config.cache_duration || 60,
    ai: {
      enabled: props.config.ai?.enabled || true,
      suggestions_count: props.config.ai?.suggestions_count || 5,
      model: props.config.ai?.model || 'gpt-3.5-turbo'
    },
    sitemap: {
      auto_generate: props.config.sitemap?.auto_generate || true,
      cache_duration: props.config.sitemap?.cache_duration || 24,
      excluded_routes: props.config.sitemap?.excluded_routes || ['admin/*']
    },
    robots: {
      auto_generate: props.config.robots?.auto_generate || true,
      default_rules: props.config.robots?.default_rules || [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin'
      ],
      add_sitemap: props.config.robots?.add_sitemap || true
    },
    meta: {
      max_title_length: props.config.meta?.max_title_length || 60,
      max_description_length: props.config.meta?.max_description_length || 160,
      default_image: props.config.meta?.default_image || ''
    }
  };
};
</script>

<style scoped>
/* Styles spécifiques au composant */
</style> 