<template>
  <div class="seo-performance">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold">Performance SEO</h1>
      <div class="flex space-x-2">
        <a 
          :href="`/admin/seo/meta/${meta.id}`" 
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
        >
          Éditer les métadonnées
        </a>
        <a 
          :href="`/admin/seo/suggestions/${meta.id}`" 
          class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition"
        >
          Suggestions
        </a>
        <button 
          @click="analyzeNow" 
          class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition"
          :disabled="isAnalyzing"
        >
          {{ isAnalyzing ? 'Analyse en cours...' : 'Analyser maintenant' }}
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
        <span class="mr-2">Dernière analyse:</span>
        <span class="text-gray-500">{{ formatDate(meta.last_analyzed_at) }}</span>
      </div>
    </div>
    
    <!-- Score global -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
      <div class="flex flex-col md:flex-row items-center justify-between">
        <div class="mb-4 md:mb-0">
          <h2 class="text-xl font-semibold mb-2">Score SEO global</h2>
          <p class="text-gray-600 dark:text-gray-400">
            Basé sur {{ latestAnalysis ? latestAnalysis.factors.length : 0 }} facteurs analysés
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
                :class="getScoreColorClass(overallScore, false, true)"
                fill="none"
                stroke-width="3"
                stroke-linecap="round"
                :stroke-dasharray="`${overallScore}, 100`"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <text x="18" y="20.5" class="text-3xl font-bold" text-anchor="middle" :fill="getScoreTextColor(overallScore)">
                {{ overallScore }}
              </text>
            </svg>
          </div>
          <div class="ml-4">
            <div class="text-lg font-semibold" :class="getScoreColorClass(overallScore)">
              {{ getScoreLabel(overallScore) }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              {{ getScoreDescription(overallScore) }}
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Historique des scores -->
    <div v-if="analysisHistory.length > 1" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Évolution du score</h2>
      <div class="h-64">
        <canvas ref="scoreChart"></canvas>
      </div>
    </div>
    
    <!-- Catégories de facteurs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div 
        v-for="(category, index) in categories" 
        :key="index"
        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
      >
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-semibold">{{ category.name }}</h3>
          <div class="flex items-center">
            <span 
              class="inline-block w-3 h-3 rounded-full mr-2" 
              :class="getScoreColorClass(category.score, true)"
            ></span>
            <span :class="getScoreColorClass(category.score)">{{ category.score }}/100</span>
          </div>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-4">
          <div 
            class="h-2.5 rounded-full" 
            :class="getScoreColorClass(category.score, true)"
            :style="{ width: `${category.score}%` }"
          ></div>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ category.description }}</p>
      </div>
    </div>
    
    <!-- Facteurs détaillés -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
      <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-semibold">Facteurs analysés</h2>
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
              <option v-for="(category, index) in categories" :key="index" :value="category.name">
                {{ category.name }}
              </option>
            </select>
          </div>
          
          <div>
            <label for="status-filter" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Filtrer par statut:
            </label>
            <select 
              id="status-filter" 
              v-model="selectedStatus"
              class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">Tous les statuts</option>
              <option value="success">Réussis</option>
              <option value="warning">Avertissements</option>
              <option value="error">Erreurs</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="divide-y divide-gray-200 dark:divide-gray-700">
        <div 
          v-for="(factor, index) in filteredFactors" 
          :key="index"
          class="p-4 hover:bg-gray-50 dark:hover:bg-gray-900 transition"
        >
          <div class="flex items-start">
            <div 
              class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-0.5"
              :class="getStatusBgClass(factor.status)"
            >
              <svg v-if="factor.status === 'success'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <svg v-else-if="factor.status === 'warning'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
              <svg v-else class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
            
            <div class="flex-grow">
              <div class="flex items-center justify-between">
                <h3 class="text-base font-medium" :class="getStatusTextClass(factor.status)">
                  {{ factor.name }}
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ factor.category }}</span>
              </div>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ factor.description }}</p>
              
              <div v-if="factor.details" class="mt-2 p-3 bg-gray-50 dark:bg-gray-900 rounded text-sm">
                <pre class="whitespace-pre-wrap font-mono text-xs">{{ factor.details }}</pre>
              </div>
              
              <div v-if="factor.recommendation" class="mt-2">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Recommandation:</div>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ factor.recommendation }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="filteredFactors.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
          Aucun facteur ne correspond aux filtres sélectionnés.
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    meta: {
      type: Object,
      required: true
    },
    latestAnalysis: {
      type: Object,
      required: true
    },
    analysisHistory: {
      type: Array,
      required: true
    }
  },
  
  data() {
    return {
      isAnalyzing: false,
      selectedCategory: 'all',
      selectedStatus: 'all',
      chart: null
    }
  },
  
  computed: {
    overallScore() {
      return this.latestAnalysis ? this.latestAnalysis.score : 0;
    },
    
    categories() {
      if (!this.latestAnalysis || !this.latestAnalysis.factors) {
        return [];
      }
      
      // Regrouper les facteurs par catégorie
      const categoryMap = {};
      
      this.latestAnalysis.factors.forEach(factor => {
        if (!categoryMap[factor.category]) {
          categoryMap[factor.category] = {
            name: factor.category,
            factors: [],
            totalScore: 0,
            count: 0,
            description: this.getCategoryDescription(factor.category)
          };
        }
        
        categoryMap[factor.category].factors.push(factor);
        categoryMap[factor.category].totalScore += this.getStatusScore(factor.status);
        categoryMap[factor.category].count++;
      });
      
      // Calculer le score moyen pour chaque catégorie
      return Object.values(categoryMap).map(category => {
        return {
          ...category,
          score: Math.round(category.totalScore / category.count)
        };
      });
    },
    
    filteredFactors() {
      if (!this.latestAnalysis || !this.latestAnalysis.factors) {
        return [];
      }
      
      return this.latestAnalysis.factors.filter(factor => {
        const categoryMatch = this.selectedCategory === 'all' || factor.category === this.selectedCategory;
        const statusMatch = this.selectedStatus === 'all' || factor.status === this.selectedStatus;
        return categoryMatch && statusMatch;
      });
    }
  },
  
  mounted() {
    if (this.analysisHistory.length > 1) {
      this.initScoreChart();
    }
  },
  
  methods: {
    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },
    
    getScoreColorClass(score, isBackground = false, isStroke = false) {
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
    },
    
    getScoreTextColor(score) {
      if (score >= 80) {
        return '#10B981'; // green-500
      } else if (score >= 50) {
        return '#F59E0B'; // yellow-500
      } else {
        return '#EF4444'; // red-500
      }
    },
    
    getScoreLabel(score) {
      if (score >= 80) {
        return 'Excellent';
      } else if (score >= 70) {
        return 'Très bon';
      } else if (score >= 50) {
        return 'Moyen';
      } else if (score >= 30) {
        return 'Faible';
      } else {
        return 'Critique';
      }
    },
    
    getScoreDescription(score) {
      if (score >= 80) {
        return 'Votre page est bien optimisée pour le SEO';
      } else if (score >= 50) {
        return 'Des améliorations sont possibles';
      } else {
        return 'Des problèmes importants doivent être résolus';
      }
    },
    
    getStatusScore(status) {
      switch (status) {
        case 'success': return 100;
        case 'warning': return 50;
        case 'error': return 0;
        default: return 0;
      }
    },
    
    getStatusBgClass(status) {
      switch (status) {
        case 'success': return 'bg-green-500';
        case 'warning': return 'bg-yellow-500';
        case 'error': return 'bg-red-500';
        default: return 'bg-gray-500';
      }
    },
    
    getStatusTextClass(status) {
      switch (status) {
        case 'success': return 'text-green-600 dark:text-green-400';
        case 'warning': return 'text-yellow-600 dark:text-yellow-400';
        case 'error': return 'text-red-600 dark:text-red-400';
        default: return 'text-gray-600 dark:text-gray-400';
      }
    },
    
    getCategoryDescription(category) {
      const descriptions = {
        'Métadonnées': 'Évaluation des balises meta, titres et descriptions',
        'Contenu': 'Analyse de la qualité et de la structure du contenu',
        'Performance': 'Vitesse de chargement et optimisation technique',
        'Mobile': 'Compatibilité avec les appareils mobiles',
        'Liens': 'Structure des liens internes et externes',
        'Technique': 'Aspects techniques du SEO',
        'Sécurité': 'Sécurité et confiance du site'
      };
      
      return descriptions[category] || 'Facteurs divers affectant le référencement';
    },
    
    async analyzeNow() {
      this.isAnalyzing = true;
      
      try {
        await this.$inertia.post(`/admin/seo/analyze/${this.meta.id}`);
      } catch (error) {
        console.error('Erreur lors de l\'analyse:', error);
      } finally {
        this.isAnalyzing = false;
      }
    },
    
    initScoreChart() {
      const ctx = this.$refs.scoreChart.getContext('2d');
      
      // Préparer les données pour le graphique
      const labels = this.analysisHistory.map(analysis => {
        const date = new Date(analysis.created_at);
        return date.toLocaleDateString('fr-FR');
      });
      
      const scores = this.analysisHistory.map(analysis => analysis.score);
      
      // Créer le graphique
      this.chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Score SEO',
            data: scores,
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 2,
            tension: 0.3,
            pointBackgroundColor: 'rgba(59, 130, 246, 1)',
            pointRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              ticks: {
                stepSize: 20
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                title: function(tooltipItems) {
                  return 'Date: ' + tooltipItems[0].label;
                },
                label: function(context) {
                  return 'Score: ' + context.raw + '/100';
                }
              }
            }
          }
        }
      });
    }
  }
}
</script>

<style scoped>
/* Styles spécifiques au composant */
</style> 