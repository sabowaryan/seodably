<template>
  <div class="seo-suggestions">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold">Suggestions SEO</h1>
      <div class="flex space-x-2">
        <a 
          :href="`/admin/seo/meta/${meta.id}`" 
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
        >
          Éditer les métadonnées
        </a>
        <a 
          :href="`/admin/seo/performance/${meta.id}`" 
          class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition"
        >
          Performance
        </a>
        <button 
          @click="generateSuggestions" 
          class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition"
          :disabled="isGenerating"
        >
          {{ isGenerating ? 'Génération en cours...' : 'Générer des suggestions' }}
        </button>
      </div>
    </div>
    
    <div class="mb-6">
      <div class="flex items-center space-x-2">
        <h2 class="text-lg font-semibold">URL:</h2>
        <a :href="meta.url" target="_blank" class="text-blue-600 hover:underline">
          {{ meta.url }}
        </a>
      </div>
      <div v-if="meta.latest_analysis" class="mt-2 flex items-center">
        <span class="mr-2">Score SEO actuel:</span>
        <span 
          class="inline-block w-3 h-3 rounded-full mr-1" 
          :class="getScoreColorClass(meta.latest_analysis.score, true)"
        ></span>
        <span :class="getScoreColorClass(meta.latest_analysis.score)">
          {{ meta.latest_analysis.score }}/100
        </span>
      </div>
    </div>
    
    <!-- Résumé des suggestions -->
    <div v-if="suggestions" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Résumé des améliorations</h2>
      <div class="prose dark:prose-invert max-w-none" v-html="suggestions.summary"></div>
      
      <div v-if="suggestions.predicted_score" class="mt-6 flex items-center">
        <div class="mr-4">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Score actuel</div>
          <div class="flex items-center">
            <span 
              class="inline-block w-3 h-3 rounded-full mr-1" 
              :class="getScoreColorClass(meta.latest_analysis.score, true)"
            ></span>
            <span class="text-lg font-semibold" :class="getScoreColorClass(meta.latest_analysis.score)">
              {{ meta.latest_analysis.score }}
            </span>
          </div>
        </div>
        
        <div class="flex-shrink-0">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
          </svg>
        </div>
        
        <div class="ml-4">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Score prévu après améliorations</div>
          <div class="flex items-center">
            <span 
              class="inline-block w-3 h-3 rounded-full mr-1" 
              :class="getScoreColorClass(suggestions.predicted_score, true)"
            ></span>
            <span class="text-lg font-semibold" :class="getScoreColorClass(suggestions.predicted_score)">
              {{ suggestions.predicted_score }}
            </span>
            <span class="ml-2 text-sm text-green-500">
              +{{ suggestions.predicted_score - meta.latest_analysis.score }} points
            </span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Suggestions détaillées -->
    <div v-if="suggestions && suggestions.categories" class="space-y-6">
      <div 
        v-for="(category, index) in suggestions.categories" 
        :key="index"
        class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
      >
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
          <h2 class="text-xl font-semibold">{{ category.name }}</h2>
          <div class="flex items-center">
            <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Impact:</span>
            <div class="flex">
              <span 
                v-for="i in 5" 
                :key="i"
                class="w-4 h-4 rounded-full mx-0.5"
                :class="i <= category.impact ? 'bg-purple-500' : 'bg-gray-200 dark:bg-gray-700'"
              ></span>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <p class="text-gray-600 dark:text-gray-400 mb-4">{{ category.description }}</p>
          
          <div class="space-y-4">
            <div 
              v-for="(suggestion, sIndex) in category.suggestions" 
              :key="sIndex"
              class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
            >
              <div class="flex items-start">
                <div 
                  class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-0.5"
                  :class="getPriorityBgClass(suggestion.priority)"
                >
                  <span class="text-xs text-white font-bold">{{ getPriorityLabel(suggestion.priority) }}</span>
                </div>
                
                <div class="flex-grow">
                  <h3 class="text-lg font-medium mb-2">{{ suggestion.title }}</h3>
                  <div class="prose dark:prose-invert text-sm max-w-none mb-4" v-html="suggestion.description"></div>
                  
                  <div v-if="suggestion.code_example" class="mt-3 bg-gray-50 dark:bg-gray-900 rounded p-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Exemple:</div>
                    <pre class="text-xs font-mono whitespace-pre-wrap overflow-x-auto">{{ suggestion.code_example }}</pre>
                  </div>
                  
                  <div v-if="suggestion.before_after" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-red-200 dark:border-red-900 rounded p-3 bg-red-50 dark:bg-red-900/20">
                      <div class="text-xs text-red-500 mb-1">Avant:</div>
                      <pre class="text-xs font-mono whitespace-pre-wrap overflow-x-auto">{{ suggestion.before_after.before }}</pre>
                    </div>
                    <div class="border border-green-200 dark:border-green-900 rounded p-3 bg-green-50 dark:bg-green-900/20">
                      <div class="text-xs text-green-500 mb-1">Après:</div>
                      <pre class="text-xs font-mono whitespace-pre-wrap overflow-x-auto">{{ suggestion.before_after.after }}</pre>
                    </div>
                  </div>
                  
                  <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center">
                      <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Difficulté:</span>
                      <div class="flex">
                        <span 
                          v-for="i in 5" 
                          :key="i"
                          class="w-3 h-3 rounded-full mx-0.5"
                          :class="i <= suggestion.difficulty ? 'bg-blue-500' : 'bg-gray-200 dark:bg-gray-700'"
                        ></span>
                      </div>
                    </div>
                    
                    <div>
                      <button 
                        @click="toggleImplemented(category.name, sIndex)"
                        class="px-3 py-1 rounded-lg text-sm"
                        :class="suggestion.implemented ? 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-400'"
                      >
                        <span v-if="suggestion.implemented">
                          <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                          Implémenté
                        </span>
                        <span v-else>Marquer comme implémenté</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- État de chargement -->
    <div v-if="isGenerating" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500 mx-auto mb-4"></div>
      <p class="text-gray-600 dark:text-gray-400">
        Génération des suggestions SEO en cours...
      </p>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
        Cela peut prendre jusqu'à une minute. Notre IA analyse votre page et prépare des recommandations personnalisées.
      </p>
    </div>
    
    <!-- Aucune suggestion -->
    <div v-if="!suggestions && !isGenerating" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
      </svg>
      <h2 class="text-xl font-semibold mb-2">Aucune suggestion disponible</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        Cliquez sur "Générer des suggestions" pour obtenir des recommandations d'amélioration SEO basées sur l'IA.
      </p>
      <button 
        @click="generateSuggestions" 
        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition"
      >
        Générer des suggestions
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
  meta: {
    type: Object,
    required: true
  },
  initialSuggestions: {
    type: Object,
    default: null
  }
});

const isGenerating = ref(false);
const suggestions = ref(props.initialSuggestions);

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

const getScoreColorClass = (score, isBackground = false) => {
  if (score >= 80) {
    return isBackground ? 'bg-green-500' : 'text-green-600 dark:text-green-400';
  } else if (score >= 50) {
    return isBackground ? 'bg-yellow-500' : 'text-yellow-600 dark:text-yellow-400';
  } else {
    return isBackground ? 'bg-red-500' : 'text-red-600 dark:text-red-400';
  }
};

const getPriorityBgClass = (priority) => {
  switch (priority) {
    case 'high': return 'bg-red-500';
    case 'medium': return 'bg-yellow-500';
    case 'low': return 'bg-blue-500';
    default: return 'bg-gray-500';
  }
};

const getPriorityLabel = (priority) => {
  switch (priority) {
    case 'high': return 'H';
    case 'medium': return 'M';
    case 'low': return 'L';
    default: return '-';
  }
};

const generateSuggestions = async () => {
  isGenerating.value = true;
  
  try {
    const response = await $inertia.post(`/admin/seo/suggestions/${props.meta.id}`, {}, {
      preserveState: true,
      only: ['suggestions']
    });
    
    // Inertia.js met à jour automatiquement les props, mais nous pouvons aussi mettre à jour manuellement
    if (response && response.props && response.props.suggestions) {
      suggestions.value = response.props.suggestions;
    }
  } catch (error) {
    console.error('Erreur lors de la génération des suggestions:', error);
  } finally {
    isGenerating.value = false;
  }
};

const toggleImplemented = async (categoryName, suggestionIndex) => {
  // Trouver la catégorie
  const categoryIndex = suggestions.value.categories.findIndex(c => c.name === categoryName);
  if (categoryIndex === -1) return;
  
  // Inverser l'état "implemented"
  const suggestion = suggestions.value.categories[categoryIndex].suggestions[suggestionIndex];
  suggestion.implemented = !suggestion.implemented;
  
  // Envoyer la mise à jour au serveur
  try {
    await $inertia.put(`/admin/seo/suggestions/${props.meta.id}/toggle`, {
      category_index: categoryIndex,
      suggestion_index: suggestionIndex,
      implemented: suggestion.implemented
    }, {
      preserveState: true
    });
  } catch (error) {
    // En cas d'erreur, revenir à l'état précédent
    suggestion.implemented = !suggestion.implemented;
    console.error('Erreur lors de la mise à jour du statut:', error);
  }
};
</script>

<style>
/* Styles pour le HTML rendu dans v-html */
.prose h1, .prose h2, .prose h3, .prose h4 {
  margin-top: 1.5em;
  margin-bottom: 0.5em;
  font-weight: 600;
}

.prose p {
  margin-bottom: 1em;
}

.prose ul, .prose ol {
  margin-left: 1.5em;
  margin-bottom: 1em;
}

.prose li {
  margin-bottom: 0.5em;
}

.prose a {
  color: #3b82f6;
  text-decoration: underline;
}

.dark .prose a {
  color: #60a5fa;
}

.prose code {
  background-color: rgba(0, 0, 0, 0.05);
  padding: 0.2em 0.4em;
  border-radius: 0.25em;
  font-family: monospace;
  font-size: 0.9em;
}

.dark .prose code {
  background-color: rgba(255, 255, 255, 0.1);
}
</style> 