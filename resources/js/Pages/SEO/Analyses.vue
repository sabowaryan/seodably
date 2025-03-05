<template>
  <div class="seo-analyses">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold">Analyses SEO</h1>
      <div class="flex space-x-2">
        <button 
          @click="showAnalysisForm = true" 
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
        >
          Analyser une URL
        </button>
      </div>
    </div>
    
    <div v-if="showAnalysisForm" class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
      <h3 class="text-lg font-semibold mb-3">Analyser une nouvelle URL</h3>
      <form @submit.prevent="analyzeUrl" class="flex flex-col md:flex-row gap-3">
        <input 
          v-model="newUrl" 
          type="url" 
          placeholder="https://exemple.com/page" 
          required
          class="flex-grow px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button 
          type="submit" 
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
          :disabled="isAnalyzing"
        >
          {{ isAnalyzing ? 'Analyse en cours...' : 'Analyser' }}
        </button>
        <button 
          type="button" 
          @click="showAnalysisForm = false" 
          class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition"
        >
          Annuler
        </button>
      </form>
    </div>
    
    <!-- Filtres -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
      <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="flex-grow">
          <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Rechercher
          </label>
          <input 
            id="search" 
            v-model="filters.search" 
            type="text" 
            placeholder="Rechercher par URL..." 
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          />
        </div>
        
        <div class="w-full md:w-48">
          <label for="score_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Score
          </label>
          <select 
            id="score_filter" 
            v-model="filters.score" 
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option value="all">Tous les scores</option>
            <option value="good">Bon (80-100)</option>
            <option value="average">Moyen (50-79)</option>
            <option value="bad">Mauvais (0-49)</option>
          </select>
        </div>
        
        <div class="w-full md:w-48">
          <label for="date_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Date
          </label>
          <select 
            id="date_filter" 
            v-model="filters.date" 
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option value="all">Toutes les dates</option>
            <option value="today">Aujourd'hui</option>
            <option value="week">Cette semaine</option>
            <option value="month">Ce mois</option>
          </select>
        </div>
        
        <div class="flex items-end">
          <button 
            @click="resetFilters" 
            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition"
          >
            Réinitialiser
          </button>
        </div>
      </div>
    </div>
    
    <!-- Tableau des analyses -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">URL</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Score</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date d'analyse</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Problèmes</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="analysis in filteredAnalyses" :key="analysis.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <a :href="analysis.url" target="_blank" class="text-blue-600 hover:underline truncate block max-w-xs">
                {{ analysis.url }}
              </a>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <div class="flex items-center">
                <span 
                  class="inline-block w-3 h-3 rounded-full mr-2" 
                  :class="getScoreColorClass(analysis.score, true)"
                ></span>
                <span :class="getScoreColorClass(analysis.score)">{{ analysis.score }}/100</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ formatDate(analysis.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <div class="flex items-center space-x-2">
                <span v-if="analysis.critical_issues_count > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                  {{ analysis.critical_issues_count }} critique{{ analysis.critical_issues_count > 1 ? 's' : '' }}
                </span>
                <span v-if="analysis.important_issues_count > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                  {{ analysis.important_issues_count }} important{{ analysis.important_issues_count > 1 ? 's' : '' }}
                </span>
                <span v-if="analysis.minor_issues_count > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                  {{ analysis.minor_issues_count }} mineur{{ analysis.minor_issues_count > 1 ? 's' : '' }}
                </span>
                <span v-if="!analysis.critical_issues_count && !analysis.important_issues_count && !analysis.minor_issues_count" class="text-gray-500 dark:text-gray-400">
                  Aucun problème
                </span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <div class="flex space-x-2">
                <a :href="`/admin/seo/analyses/${analysis.id}`" class="text-blue-600 hover:text-blue-800">
                  Détails
                </a>
                <a v-if="analysis.seo_id" :href="`/admin/seo/meta/${analysis.seo_id}`" class="text-green-600 hover:text-green-800">
                  Métadonnées
                </a>
                <a v-if="analysis.seo_id" :href="`/admin/seo/suggestions/${analysis.seo_id}`" class="text-purple-600 hover:text-purple-800">
                  Suggestions
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="filteredAnalyses.length === 0">
            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
              Aucune analyse trouvée. Commencez par analyser une URL.
            </td>
          </tr>
        </tbody>
      </table>
      
      <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t dark:border-gray-700">
        <!-- Pagination -->
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Affichage de {{ analyses.from || 0 }} à {{ analyses.to || 0 }} sur {{ analyses.total }} résultats
          </div>
          <div class="flex space-x-1">
            <button 
              v-for="link in analyses.links" 
              :key="link.label"
              @click="goToPage(link.url)"
              :disabled="!link.url || link.active"
              :class="[
                'px-3 py-1 rounded',
                link.active ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                !link.url && 'opacity-50 cursor-not-allowed'
              ]"
              v-html="link.label"
            ></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
  analyses: {
    type: Object,
    required: true
  }
});

const showAnalysisForm = ref(false);
const newUrl = ref('');
const isAnalyzing = ref(false);
const filters = ref({
  search: '',
  score: 'all',
  date: 'all'
});

const filteredAnalyses = computed(() => {
  if (!props.analyses.data) return [];
  
  return props.analyses.data.filter(analysis => {
    // Filtre par recherche
    if (filters.value.search && !analysis.url.toLowerCase().includes(filters.value.search.toLowerCase())) {
      return false;
    }
    
    // Filtre par score
    if (filters.value.score !== 'all') {
      if (filters.value.score === 'good' && analysis.score < 80) return false;
      if (filters.value.score === 'average' && (analysis.score < 50 || analysis.score >= 80)) return false;
      if (filters.value.score === 'bad' && analysis.score >= 50) return false;
    }
    
    // Filtre par date
    if (filters.value.date !== 'all') {
      const analysisDate = new Date(analysis.created_at);
      const today = new Date();
      
      if (filters.value.date === 'today') {
        if (analysisDate.toDateString() !== today.toDateString()) return false;
      } else if (filters.value.date === 'week') {
        const weekStart = new Date(today);
        weekStart.setDate(today.getDate() - today.getDay());
        if (analysisDate < weekStart) return false;
      } else if (filters.value.date === 'month') {
        const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
        if (analysisDate < monthStart) return false;
      }
    }
    
    return true;
  });
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

const getScoreColorClass = (score, isBackground = false) => {
  if (score >= 80) {
    return isBackground ? 'bg-green-500' : 'text-green-600 dark:text-green-400';
  } else if (score >= 50) {
    return isBackground ? 'bg-yellow-500' : 'text-yellow-600 dark:text-yellow-400';
  } else {
    return isBackground ? 'bg-red-500' : 'text-red-600 dark:text-red-400';
  }
};

const analyzeUrl = async () => {
  if (!newUrl.value) return;
  
  isAnalyzing.value = true;
  
  try {
    await $inertia.post('/admin/seo/analyses', {
      url: newUrl.value
    });
    
    newUrl.value = '';
    showAnalysisForm.value = false;
  } catch (error) {
    console.error('Erreur lors de l\'analyse:', error);
  } finally {
    isAnalyzing.value = false;
  }
};

const resetFilters = () => {
  filters.value = {
    search: '',
    score: 'all',
    date: 'all'
  };
};

const goToPage = (url) => {
  if (!url) return;
  $inertia.visit(url);
};
</script>

<style scoped>
/* Styles spécifiques au composant */
</style> 