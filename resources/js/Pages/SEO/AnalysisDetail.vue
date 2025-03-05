<template>
  <div class="analysis-detail">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold">Détails de l'analyse SEO</h1>
      <div class="flex space-x-2">
        <a 
          :href="`/admin/seo/analyses`" 
          class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition"
        >
          Retour aux analyses
        </a>
        <button 
          @click="analyzeAgain" 
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
          :disabled="isAnalyzing"
        >
          {{ isAnalyzing ? 'Analyse en cours...' : 'Analyser à nouveau' }}
        </button>
      </div>
    </div>
    
    <div class="mb-6">
      <div class="flex items-center space-x-2">
        <h2 class="text-lg font-semibold">URL:</h2>
        <a :href="analysis.url" target="_blank" class="text-blue-600 hover:underline">
          {{ analysis.url }}
        </a>
      </div>
      <div class="mt-2 flex items-center">
        <span class="mr-2">Date d'analyse:</span>
        <span class="text-gray-500">{{ formatDate(analysis.created_at) }}</span>
      </div>
    </div>
    
    <!-- Score global -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
      <div class="flex flex-col md:flex-row items-center justify-between">
        <div class="mb-4 md:mb-0">
          <h2 class="text-xl font-semibold mb-2">Score SEO global</h2>
          <p class="text-gray-600 dark:text-gray-400">
            Basé sur {{ analysis.factors ? analysis.factors.length : 0 }} facteurs analysés
          </p>
        </div>
        
        <div class="flex items-center">
          <div class="relative w-32 h-32">
            <svg class="w-full h-full" viewBox="0 0 36 36">
              <path
                class="stroke-current text-gray-200 dark:text-gray-700"
                fill="none"
                stroke-width="3"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path
                :class="getScoreColorClass(analysis.score, false, true)"
                fill="none"
                stroke-width="3"
                stroke-linecap="round"
                :stroke-dasharray="`${analysis.score}, 100`"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <text x="18" y="20.5" class="text-3xl font-bold" text-anchor="middle" :fill="getScoreTextColor(analysis.score)">
                {{ analysis.score }}
              </text>
            </svg>
          </div>
          <div class="ml-4">
            <div class="text-lg font-semibold" :class="getScoreColorClass(analysis.score)">
              {{ getScoreLabel(analysis.score) }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              {{ getScoreDescription(analysis.score) }}
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Métriques clés -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold mb-2">Contenu</h3>
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Longueur du contenu:</span>
            <span class="font-medium">{{ analysis.content_length || 0 }} caractères</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Densité de mots-clés:</span>
            <span class="font-medium">{{ (analysis.keyword_density || 0).toFixed(2) }}%</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Score de lisibilité:</span>
            <span class="font-medium">{{ (analysis.readability_score || 0).toFixed(1) }}/10</span>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold mb-2">Performance</h3>
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Temps de chargement:</span>
            <span class="font-medium">{{ (analysis.load_time || 0).toFixed(2) }}s</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Taille de la page:</span>
            <span class="font-medium">{{ formatFileSize(analysis.page_size || 0) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Requêtes:</span>
            <span class="font-medium">{{ analysis.requests_count || 0 }}</span>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold mb-2">Liens</h3>
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Liens internes:</span>
            <span class="font-medium">{{ analysis.internal_links_count || 0 }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Liens externes:</span>
            <span class="font-medium">{{ analysis.external_links_count || 0 }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Liens brisés:</span>
            <span class="font-medium">{{ analysis.broken_links_count || 0 }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Problèmes détectés -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
      <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-xl font-semibold">Problèmes détectés</h2>
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <span class="inline-block w-3 h-3 rounded-full bg-red-500 mr-1"></span>
            <span class="text-sm">Critiques: {{ criticalIssuesCount }}</span>
          </div>
          <div class="flex items-center">
            <span class="inline-block w-3 h-3 rounded-full bg-yellow-500 mr-1"></span>
            <span class="text-sm">Importants: {{ importantIssuesCount }}</span>
          </div>
          <div class="flex items-center">
            <span class="inline-block w-3 h-3 rounded-full bg-blue-500 mr-1"></span>
            <span class="text-sm">Mineurs: {{ minorIssuesCount }}</span>
          </div>
        </div>
      </div>
      
      <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div class="mb-3 md:mb-0">
            <label for="category-filter" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Filtrer par catégorie:
            </label>
            <select 
              id="category-filter" 
              v-model="selectedCategory"
              class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">Toutes les catégories</option>
              <option v-for="category in categories" :key="category">{{ category }}</option>
            </select>
          </div>
          
          <div>
            <label for="severity-filter" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Filtrer par sévérité:
            </label>
            <select 
              id="severity-filter" 
              v-model="selectedSeverity"
              class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">Toutes les sévérités</option>
              <option value="critical">Critique</option>
              <option value="important">Important</option>
              <option value="minor">Mineur</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="divide-y divide-gray-200 dark:divide-gray-700">
        <div 
          v-for="(issue, index) in filteredIssues" 
          :key="index"
          class="p-4 hover:bg-gray-50 dark:hover:bg-gray-900 transition"
        >
          <div class="flex items-start">
            <div 
              class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-0.5"
              :class="getSeverityBgClass(issue.severity)"
            >
              <svg v-if="issue.severity === 'minor'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <svg v-else-if="issue.severity === 'important'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
              <svg v-else class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            
            <div class="flex-grow">
              <div class="flex items-center justify-between">
                <h3 class="text-base font-medium" :class="getSeverityTextClass(issue.severity)">
                  {{ issue.title }}
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ issue.category }}</span>
              </div>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ issue.description }}</p>
              
              <div v-if="issue.details" class="mt-2 p-3 bg-gray-50 dark:bg-gray-900 rounded text-sm">
                <pre class="whitespace-pre-wrap font-mono text-xs">{{ issue.details }}</pre>
              </div>
              
              <div v-if="issue.recommendation" class="mt-2">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Recommandation:</div>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ issue.recommendation }}</p>
              </div>
              
              <div v-if="issue.impact" class="mt-2 flex items-center">
                <span class="text-sm text-gray-700 dark:text-gray-300 mr-2">Impact sur le score:</span>
                <div class="flex">
                  <span 
                    v-for="i in 5" 
                    :key="i"
                    class="w-4 h-1 mx-0.5 rounded-sm"
                    :class="i <= issue.impact ? 'bg-red-500' : 'bg-gray-200 dark:bg-gray-700'"
                  ></span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="filteredIssues.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
          Aucun problème ne correspond aux filtres sélectionnés.
        </div>
      </div>
    </div>
    
    <!-- Métadonnées -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
      <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-semibold">Métadonnées détectées</h2>
      </div>
      
      <div class="p-6 space-y-4">
        <div v-if="analysis.meta_title" class="space-y-1">
          <div class="flex items-center">
            <h3 class="text-base font-medium">Titre</h3>
            <span 
              v-if="analysis.meta_title_length > config.max_title_length" 
              class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
            >
              Trop long
            </span>
          </div>
          <p class="text-gray-600 dark:text-gray-400">{{ analysis.meta_title }}</p>
          <div class="text-xs text-gray-500">
            {{ analysis.meta_title_length }} caractères (recommandé: {{ config.max_title_length }})
          </div>
        </div>
        
        <div v-if="analysis.meta_description" class="space-y-1">
          <div class="flex items-center">
            <h3 class="text-base font-medium">Description</h3>
            <span 
              v-if="analysis.meta_description_length > config.max_description_length" 
              class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
            >
              Trop longue
            </span>
          </div>
          <p class="text-gray-600 dark:text-gray-400">{{ analysis.meta_description }}</p>
          <div class="text-xs text-gray-500">
            {{ analysis.meta_description_length }} caractères (recommandé: {{ config.max_description_length }})
          </div>
        </div>
        
        <div v-if="analysis.meta_keywords" class="space-y-1">
          <h3 class="text-base font-medium">Mots-clés</h3>
          <p class="text-gray-600 dark:text-gray-400">{{ analysis.meta_keywords }}</p>
        </div>
        
        <div v-if="analysis.canonical_url" class="space-y-1">
          <h3 class="text-base font-medium">URL canonique</h3>
          <p class="text-gray-600 dark:text-gray-400">{{ analysis.canonical_url }}</p>
        </div>
        
        <div v-if="analysis.robots" class="space-y-1">
          <h3 class="text-base font-medium">Robots</h3>
          <p class="text-gray-600 dark:text-gray-400">{{ analysis.robots }}</p>
        </div>
        
        <div v-if="analysis.h1_tags && analysis.h1_tags.length > 0" class="space-y-1">
          <h3 class="text-base font-medium">Balises H1 ({{ analysis.h1_tags.length }})</h3>
          <ul class="list-disc list-inside text-gray-600 dark:text-gray-400">
            <li v-for="(tag, index) in analysis.h1_tags" :key="index">{{ tag }}</li>
          </ul>
        </div>
        
        <div v-if="analysis.images && analysis.images.length > 0" class="space-y-1">
          <h3 class="text-base font-medium">Images sans attribut alt ({{ analysis.images.length }})</h3>
          <ul class="list-disc list-inside text-gray-600 dark:text-gray-400">
            <li v-for="(image, index) in analysis.images" :key="index" class="truncate">{{ image }}</li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Actions -->
    <div class="flex justify-end space-x-3">
      <a 
        v-if="analysis.seo_id"
        :href="`/admin/seo/meta/${analysis.seo_id}`" 
        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
      >
        Éditer les métadonnées
      </a>
      <a 
        v-if="analysis.seo_id"
        :href="`/admin/seo/suggestions/${analysis.seo_id}`" 
        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition"
      >
        Voir les suggestions
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
  analysis: {
    type: Object,
    required: true
  },
  config: {
    type: Object,
    required: true
  }
});

const isAnalyzing = ref(false);
const selectedCategory = ref('all');
const selectedSeverity = ref('all');

const issues = computed(() => {
  return props.analysis.factors || [];
});

const categories = computed(() => {
  const categoriesSet = new Set();
  issues.value.forEach(issue => {
    if (issue.category) {
      categoriesSet.add(issue.category);
    }
  });
  return Array.from(categoriesSet);
});

const filteredIssues = computed(() => {
  return issues.value.filter(issue => {
    const categoryMatch = selectedCategory.value === 'all' || issue.category === selectedCategory.value;
    const severityMatch = selectedSeverity.value === 'all' || issue.severity === selectedSeverity.value;
    return categoryMatch && severityMatch;
  });
});

const criticalIssuesCount = computed(() => {
  return issues.value.filter(issue => issue.severity === 'critical').length;
});

const importantIssuesCount = computed(() => {
  return issues.value.filter(issue => issue.severity === 'important').length;
});

const minorIssuesCount = computed(() => {
  return issues.value.filter(issue => issue.severity === 'minor').length;
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getScoreColorClass = (score, isBackground = false, isStroke = false) => {
  if (score >= 80) {
    return isBackground 
      ? 'bg-green-500' 
      : isStroke 
        ? 'stroke-green-500' 
        : 'text-green-600 dark:text-green-400';
  } else if (score >= 50) {
    return isBackground 
      ? 'bg-yellow-500' 
      : isStroke 
        ? 'stroke-yellow-500' 
        : 'text-yellow-600 dark:text-yellow-400';
  } else {
    return isBackground 
      ? 'bg-red-500' 
      : isStroke 
        ? 'stroke-red-500' 
        : 'text-red-600 dark:text-red-400';
  }
};

const getScoreTextColor = (score) => {
  if (score >= 80) {
    return '#10B981'; // green-500
  } else if (score >= 50) {
    return '#F59E0B'; // yellow-500
  } else {
    return '#EF4444'; // red-500
  }
};

const getScoreLabel = (score) => {
  if (score >= 80) return 'Excellent';
  if (score >= 70) return 'Très bon';
  if (score >= 50) return 'Moyen';
  if (score >= 30) return 'Faible';
  return 'Critique';
};

const getScoreDescription = (score) => {
  if (score >= 80) {
    return 'Votre page est bien optimisée pour le SEO';
  } else if (score >= 50) {
    return 'Des améliorations sont possibles';
  } else {
    return 'Des problèmes importants doivent être résolus';
  }
};

const getSeverityBgClass = (severity) => {
  switch (severity) {
    case 'critical': return 'bg-red-500';
    case 'important': return 'bg-yellow-500';
    case 'minor': return 'bg-blue-500';
    default: return 'bg-gray-500';
  }
};

const getSeverityTextClass = (severity) => {
  switch (severity) {
    case 'critical': return 'text-red-600 dark:text-red-400';
    case 'important': return 'text-yellow-600 dark:text-yellow-400';
    case 'minor': return 'text-blue-600 dark:text-blue-400';
    default: return 'text-gray-600 dark:text-gray-400';
  }
};

const analyzeAgain = async () => {
  isAnalyzing.value = true;
  
  try {
    if (props.analysis.seo_id) {
      await $inertia.post(`/admin/seo/analyze/${props.analysis.seo_id}`);
    } else {
      await $inertia.post('/admin/seo/analyses', {
        url: props.analysis.url
      });
    }
  } catch (error) {
    console.error('Erreur lors de l\'analyse:', error);
  } finally {
    isAnalyzing.value = false;
  }
};
</script>

<style scoped>
/* Styles spécifiques au composant */
</style> 