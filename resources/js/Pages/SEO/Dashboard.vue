<template>
  <div class="seo-dashboard">
    <h1 class="text-2xl font-bold mb-6">Tableau de bord SEO</h1>
    
    <div class="stats-grid grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
      <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Pages totales</h3>
        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ stats.total_pages }}</p>
      </div>
      
      <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Pages analysées</h3>
        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ stats.analyzed_pages }}</p>
      </div>
      
      <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Score moyen</h3>
        <p class="text-3xl font-bold" :class="getScoreColorClass(averageScore)">{{ averageScore }}/100</p>
      </div>
      
      <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Distribution</h3>
        <div class="flex items-center mt-2">
          <div class="h-4 rounded-l bg-green-500" :style="{ width: goodPercentage + '%' }"></div>
          <div class="h-4 bg-yellow-500" :style="{ width: averagePercentage + '%' }"></div>
          <div class="h-4 rounded-r bg-red-500" :style="{ width: badPercentage + '%' }"></div>
        </div>
        <div class="flex justify-between text-xs mt-1">
          <span>Bon: {{ stats.good_score }}</span>
          <span>Moyen: {{ stats.average_score }}</span>
          <span>Mauvais: {{ stats.bad_score }}</span>
        </div>
      </div>
    </div>
    
    <div class="mb-6 flex justify-between items-center">
      <h2 class="text-xl font-semibold">Pages récentes</h2>
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
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">URL</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Score</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dernière analyse</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="entry in seoEntries.data" :key="entry.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <a :href="entry.url" target="_blank" class="text-blue-600 hover:underline truncate block max-w-xs">
                {{ entry.url }}
              </a>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <div v-if="entry.latest_analysis" class="flex items-center">
                <span 
                  class="inline-block w-3 h-3 rounded-full mr-2" 
                  :class="getScoreColorClass(entry.latest_analysis.score, true)"
                ></span>
                <span>{{ entry.latest_analysis.score }}/100</span>
              </div>
              <span v-else class="text-gray-400">Non analysé</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ entry.last_analyzed_at ? formatDate(entry.last_analyzed_at) : 'Jamais' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <div class="flex space-x-2">
                <a :href="`/admin/seo/meta/${entry.id}`" class="text-blue-600 hover:text-blue-800">
                  Éditer
                </a>
                <a :href="`/admin/seo/performance/${entry.id}`" class="text-green-600 hover:text-green-800">
                  Performance
                </a>
                <a :href="`/admin/seo/suggestions/${entry.id}`" class="text-purple-600 hover:text-purple-800">
                  Suggestions
                </a>
                <button 
                  @click="analyzeEntry(entry)" 
                  class="text-orange-600 hover:text-orange-800"
                  :disabled="isAnalyzing"
                >
                  Analyser
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="seoEntries.data.length === 0">
            <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
              Aucune page SEO trouvée. Commencez par analyser une URL.
            </td>
          </tr>
        </tbody>
      </table>
      
      <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t dark:border-gray-700">
        <!-- Pagination -->
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Affichage de {{ seoEntries.from || 0 }} à {{ seoEntries.to || 0 }} sur {{ seoEntries.total }} résultats
          </div>
          <div class="flex space-x-1">
            <button 
              v-for="link in seoEntries.links" 
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
  seoEntries: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    required: true
  },
  config: {
    type: Object,
    required: true
  }
});

const showAnalysisForm = ref(false);
const newUrl = ref('');
const isAnalyzing = ref(false);

const totalScoreEntries = computed(() => {
  return props.stats.good_score + props.stats.average_score + props.stats.bad_score;
});

const goodPercentage = computed(() => {
  return totalScoreEntries.value ? (props.stats.good_score / totalScoreEntries.value) * 100 : 0;
});

const averagePercentage = computed(() => {
  return totalScoreEntries.value ? (props.stats.average_score / totalScoreEntries.value) * 100 : 0;
});

const badPercentage = computed(() => {
  return totalScoreEntries.value ? (props.stats.bad_score / totalScoreEntries.value) * 100 : 0;
});

const averageScore = computed(() => {
  if (totalScoreEntries.value === 0) return 0;
  
  // Calculer un score moyen pondéré
  const totalScore = (props.stats.good_score * 90) + (props.stats.average_score * 65) + (props.stats.bad_score * 35);
  return Math.round(totalScore / totalScoreEntries.value);
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

const analyzeEntry = async (entry) => {
  isAnalyzing.value = true;
  
  try {
    await $inertia.post(`/admin/seo/analyze/${entry.id}`);
  } catch (error) {
    console.error('Erreur lors de l\'analyse:', error);
  } finally {
    isAnalyzing.value = false;
  }
};

const goToPage = (url) => {
  if (!url) return;
  $inertia.visit(url);
};
</script>

<style scoped>
/* Styles spécifiques au composant */
</style> 