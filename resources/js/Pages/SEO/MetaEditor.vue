<template>
  <div class="meta-editor">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold">Édition des métadonnées SEO</h1>
      <div class="flex space-x-2">
        <a 
          :href="`/admin/seo/performance/${meta.id}`" 
          class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition"
        >
          Performance
        </a>
        <a 
          :href="`/admin/seo/suggestions/${meta.id}`" 
          class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition"
        >
          Suggestions
        </a>
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
        <span class="mr-2">Score SEO:</span>
        <span 
          class="inline-block w-3 h-3 rounded-full mr-1" 
          :class="getScoreColorClass(meta.latest_analysis.score, true)"
        ></span>
        <span :class="getScoreColorClass(meta.latest_analysis.score)">
          {{ meta.latest_analysis.score }}/100
        </span>
        <span class="ml-4 text-sm text-gray-500">
          Dernière analyse: {{ formatDate(meta.last_analyzed_at) }}
        </span>
      </div>
    </div>
    
    <form @submit.prevent="saveMeta" class="space-y-6">
      <!-- Onglets pour les différentes sections -->
      <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="flex space-x-8">
          <button 
            type="button"
            @click="activeTab = 'basic'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'basic' 
                ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            Basique
          </button>
          <button 
            type="button"
            @click="activeTab = 'opengraph'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'opengraph' 
                ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            Open Graph
          </button>
          <button 
            type="button"
            @click="activeTab = 'twitter'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'twitter' 
                ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            Twitter
          </button>
          <button 
            type="button"
            @click="activeTab = 'advanced'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'advanced' 
                ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            Avancé
          </button>
        </nav>
      </div>
      
      <!-- Aperçu -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <h3 class="text-lg font-semibold mb-3">Aperçu dans les résultats de recherche</h3>
        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
          <div class="text-blue-800 dark:text-blue-400 text-xl mb-1 truncate">
            {{ formData.title || 'Titre de la page' }}
          </div>
          <div class="text-green-700 dark:text-green-500 text-sm mb-2 truncate">
            {{ meta.url }}
          </div>
          <div class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2">
            {{ formData.description || 'Description de la page...' }}
          </div>
        </div>
      </div>
      
      <!-- Analyse en temps réel -->
      <div v-if="realtimeAnalysis" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Analyse SEO en temps réel</h3>
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-2" :class="getScoreColorClass(analysisScore, true)">
              <span class="text-white font-bold">{{ analysisScore }}</span>
            </div>
            <span :class="getScoreColorClass(analysisScore)">{{ getScoreLabel(analysisScore) }}</span>
          </div>
        </div>
        
        <!-- Problèmes détectés -->
        <div v-if="analysisIssues.length > 0" class="mb-4">
          <h4 class="text-base font-medium mb-2">Problèmes détectés</h4>
          <ul class="space-y-2">
            <li v-for="(issue, index) in analysisIssues" :key="index" class="flex items-start">
              <div class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center mr-2 mt-0.5" :class="getIssueIconClass(issue.type)">
                <svg v-if="issue.type === 'error'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <svg v-else-if="issue.type === 'warning'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <span :class="getIssueTypeClass(issue.type)">{{ issue.message }}</span>
                <span v-if="issue.field" class="text-xs text-gray-500 ml-1">({{ issue.field }})</span>
              </div>
            </li>
          </ul>
        </div>
        
        <!-- Suggestions -->
        <div v-if="analysisSuggestions.length > 0">
          <h4 class="text-base font-medium mb-2">Suggestions d'amélioration</h4>
          <ul class="space-y-2">
            <li v-for="(suggestion, index) in analysisSuggestions" :key="index" class="flex items-start">
              <div class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center mr-2 mt-0.5 bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
              <span>{{ suggestion }}</span>
            </li>
          </ul>
        </div>
        
        <!-- Statistiques -->
        <div v-if="realtimeAnalysis.title && realtimeAnalysis.description" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <h4 class="text-sm font-medium mb-2">Titre</h4>
            <div class="flex items-center">
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                <div 
                  class="h-2.5 rounded-full" 
                  :class="[
                    realtimeAnalysis.title.is_too_long ? 'bg-red-500' : 'bg-green-500',
                    realtimeAnalysis.title.is_empty ? 'w-0' : 'w-' + Math.min(100, Math.round((realtimeAnalysis.title.length / realtimeAnalysis.title.max_length) * 100)) + '%'
                  ]"
                ></div>
              </div>
              <span class="ml-2 text-xs">
                {{ realtimeAnalysis.title.length }}/{{ realtimeAnalysis.title.max_length }}
              </span>
            </div>
          </div>
          
          <div>
            <h4 class="text-sm font-medium mb-2">Description</h4>
            <div class="flex items-center">
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                <div 
                  class="h-2.5 rounded-full" 
                  :class="[
                    realtimeAnalysis.description.is_too_long ? 'bg-red-500' : 'bg-green-500',
                    realtimeAnalysis.description.is_empty ? 'w-0' : 'w-' + Math.min(100, Math.round((realtimeAnalysis.description.length / realtimeAnalysis.description.max_length) * 100)) + '%'
                  ]"
                ></div>
              </div>
              <span class="ml-2 text-xs">
                {{ realtimeAnalysis.description.length }}/{{ realtimeAnalysis.description.max_length }}
              </span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Onglet Basique -->
      <div v-if="activeTab === 'basic'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="form-group">
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Titre
              <span class="text-xs text-gray-500 ml-1">
                ({{ formData.title ? formData.title.length : 0 }}/{{ config.max_title_length }} caractères)
              </span>
            </label>
            <input 
              id="title" 
              v-model="formData.title" 
              type="text" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              :class="{'border-red-500': formData.title && formData.title.length > config.max_title_length}"
            />
            <div v-if="formData.title && formData.title.length > config.max_title_length" class="text-red-500 text-xs mt-1">
              Le titre est trop long
            </div>
          </div>
          
          <div class="form-group">
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Description
              <span class="text-xs text-gray-500 ml-1">
                ({{ formData.description ? formData.description.length : 0 }}/{{ config.max_description_length }} caractères)
              </span>
            </label>
            <textarea 
              id="description" 
              v-model="formData.description" 
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              :class="{'border-red-500': formData.description && formData.description.length > config.max_description_length}"
            ></textarea>
            <div v-if="formData.description && formData.description.length > config.max_description_length" class="text-red-500 text-xs mt-1">
              La description est trop longue
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="form-group">
            <label for="keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Mots-clés (séparés par des virgules)
            </label>
            <input 
              id="keywords" 
              v-model="formData.keywords" 
              type="text" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div class="form-group">
            <label for="canonical" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              URL canonique
            </label>
            <input 
              id="canonical" 
              v-model="formData.canonical" 
              type="url" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>
      </div>
      
      <!-- Onglet Open Graph -->
      <div v-if="activeTab === 'opengraph'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="form-group">
            <label for="og_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Titre Open Graph
            </label>
            <input 
              id="og_title" 
              v-model="formData.og_title" 
              type="text" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
            <div class="text-xs text-gray-500 mt-1">
              Laissez vide pour utiliser le titre principal
            </div>
          </div>
          
          <div class="form-group">
            <label for="og_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Description Open Graph
            </label>
            <textarea 
              id="og_description" 
              v-model="formData.og_description" 
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
            <div class="text-xs text-gray-500 mt-1">
              Laissez vide pour utiliser la description principale
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="form-group">
            <label for="og_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Type Open Graph
            </label>
            <select 
              id="og_type" 
              v-model="formData.og_type" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="website">Site web</option>
              <option value="article">Article</option>
              <option value="product">Produit</option>
              <option value="profile">Profil</option>
              <option value="book">Livre</option>
              <option value="music">Musique</option>
              <option value="video">Vidéo</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="og_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              URL de l'image Open Graph
            </label>
            <input 
              id="og_image" 
              v-model="formData.og_image" 
              type="url" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>
      </div>
      
      <!-- Onglet Twitter -->
      <div v-if="activeTab === 'twitter'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="form-group">
            <label for="twitter_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Titre Twitter
            </label>
            <input 
              id="twitter_title" 
              v-model="formData.twitter_title" 
              type="text" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
            <div class="text-xs text-gray-500 mt-1">
              Laissez vide pour utiliser le titre Open Graph ou principal
            </div>
          </div>
          
          <div class="form-group">
            <label for="twitter_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Description Twitter
            </label>
            <textarea 
              id="twitter_description" 
              v-model="formData.twitter_description" 
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
            <div class="text-xs text-gray-500 mt-1">
              Laissez vide pour utiliser la description Open Graph ou principale
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="form-group">
            <label for="twitter_card" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Type de carte Twitter
            </label>
            <select 
              id="twitter_card" 
              v-model="formData.twitter_card" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="summary">Résumé</option>
              <option value="summary_large_image">Résumé avec grande image</option>
              <option value="app">Application</option>
              <option value="player">Lecteur</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="twitter_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              URL de l'image Twitter
            </label>
            <input 
              id="twitter_image" 
              v-model="formData.twitter_image" 
              type="url" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
            <div class="text-xs text-gray-500 mt-1">
              Laissez vide pour utiliser l'image Open Graph
            </div>
          </div>
        </div>
      </div>
      
      <!-- Onglet Avancé -->
      <div v-if="activeTab === 'advanced'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="form-group">
            <label for="robots" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Directives robots
            </label>
            <div class="space-y-2">
              <div class="flex items-center">
                <input 
                  id="index" 
                  v-model="robotsOptions.index" 
                  type="checkbox" 
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="index" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                  index
                </label>
              </div>
              <div class="flex items-center">
                <input 
                  id="follow" 
                  v-model="robotsOptions.follow" 
                  type="checkbox" 
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="follow" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                  follow
                </label>
              </div>
              <div class="flex items-center">
                <input 
                  id="noarchive" 
                  v-model="robotsOptions.noarchive" 
                  type="checkbox" 
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="noarchive" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                  noarchive
                </label>
              </div>
              <div class="flex items-center">
                <input 
                  id="nosnippet" 
                  v-model="robotsOptions.nosnippet" 
                  type="checkbox" 
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="nosnippet" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                  nosnippet
                </label>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="schema_markup" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Balisage de schéma JSON-LD
            </label>
            <textarea 
              id="schema_markup" 
              v-model="formData.schema_markup" 
              rows="8"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
            ></textarea>
            <div class="text-xs text-gray-500 mt-1">
              Format JSON-LD pour les données structurées
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="custom_meta" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Balises meta personnalisées (JSON)
          </label>
          <textarea 
            id="custom_meta" 
            v-model="formData.custom_meta" 
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
          ></textarea>
          <div class="text-xs text-gray-500 mt-1">
            Format: [{"name": "nom", "content": "valeur"}, ...]
          </div>
        </div>
      </div>
      
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
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  meta: {
    type: Object,
    required: true
  },
  aiSuggestions: {
    type: Object,
    default: () => ({})
  },
  config: {
    type: Object,
    required: true
  }
});

const form = useForm({
  title: props.meta.title || '',
  description: props.meta.description || '',
  keywords: props.meta.keywords || '',
  canonical: props.meta.canonical || '',
  robots: props.meta.robots || '',
  author: props.meta.author || '',
  og_title: props.meta.og_title || '',
  og_description: props.meta.og_description || '',
  og_image: props.meta.og_image || '',
  og_type: props.meta.og_type || '',
  twitter_card: props.meta.twitter_card || '',
  twitter_title: props.meta.twitter_title || '',
  twitter_description: props.meta.twitter_description || '',
  twitter_image: props.meta.twitter_image || '',
  json_ld: props.meta.json_ld || null,
  is_active: props.meta.is_active || true
});

const isAnalyzing = ref(false);
const analysisTimer = ref(null);
const analysisResults = ref(null);

const titleLength = computed(() => form.title?.length || 0);
const descriptionLength = computed(() => form.description?.length || 0);

const isTitleTooLong = computed(() => titleLength.value > props.config.max_title_length);
const isDescriptionTooLong = computed(() => descriptionLength.value > props.config.max_description_length);

const titleProgress = computed(() => (titleLength.value / props.config.max_title_length) * 100);
const descriptionProgress = computed(() => (descriptionLength.value / props.config.max_description_length) * 100);

const getProgressColorClass = (progress) => {
  if (progress > 100) return 'bg-red-500';
  if (progress > 90) return 'bg-yellow-500';
  return 'bg-green-500';
};

const submit = () => {
  form.put(route('seo.update', props.meta.id));
};

const analyzeMetadata = async () => {
  if (!props.config.ai_enabled) return;
  
  isAnalyzing.value = true;
  
  try {
    const response = await fetch('/api/seo/analyze/meta', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        title: form.title,
        description: form.description,
        keywords: form.keywords,
        canonical: form.canonical,
        robots: form.robots
      })
    });
    
    if (response.ok) {
      const data = await response.json();
      analysisResults.value = data;
    }
  } catch (error) {
    console.error('Erreur lors de l\'analyse:', error);
  } finally {
    isAnalyzing.value = false;
  }
};

const debounceAnalysis = () => {
  if (analysisTimer.value) {
    clearTimeout(analysisTimer.value);
  }
  
  analysisTimer.value = setTimeout(() => {
    analyzeMetadata();
  }, 500);
};

const getIssueTypeClass = (type) => {
  switch (type) {
    case 'error': return 'text-red-600 dark:text-red-400';
    case 'warning': return 'text-yellow-600 dark:text-yellow-400';
    case 'info': return 'text-blue-600 dark:text-blue-400';
    default: return 'text-gray-600 dark:text-gray-400';
  }
};

const getIssueIconClass = (type) => {
  switch (type) {
    case 'error': return 'bg-red-500';
    case 'warning': return 'bg-yellow-500';
    case 'info': return 'bg-blue-500';
    default: return 'bg-gray-500';
  }
};

const getScoreLabel = (score) => {
  if (score >= 80) return 'Excellent';
  if (score >= 70) return 'Très bon';
  if (score >= 50) return 'Moyen';
  if (score >= 30) return 'Faible';
  return 'Critique';
};

// Surveiller les changements dans les champs pour l'analyse en temps réel
watch(
  [
    () => form.title,
    () => form.description,
    () => form.keywords,
    () => form.canonical,
    () => form.robots
  ],
  () => {
    if (props.config.ai_enabled) {
      debounceAnalysis();
    }
  }
);
</script>

<style scoped>
/* Styles spécifiques au composant */
</style> 