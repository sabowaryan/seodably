<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Barre de navigation supérieure -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <Link :href="route('dashboard')" class="flex items-center">
                <svg class="h-8 w-8 text-blue-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">Seodably</span>
              </Link>
            </div>
            
            <!-- Liens de navigation -->
            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
              <NavLink :href="route('seo.dashboard')" :active="route().current('seo.dashboard')">
                Tableau de bord
              </NavLink>
              <NavLink :href="route('seo.meta.index')" :active="route().current('seo.meta.*')">
                Métadonnées
              </NavLink>
              <NavLink :href="route('seo.analyses.index')" :active="route().current('seo.analyses.*')">
                Analyses
              </NavLink>
              <NavLink :href="route('seo.settings')" :active="route().current('seo.settings')">
                Paramètres
              </NavLink>
            </div>
          </div>
          
          <!-- Boutons de droite -->
          <div class="hidden sm:flex sm:items-center sm:ml-6">
            <div class="ml-3 relative">
              <button 
                @click="darkMode = !darkMode" 
                class="p-1 rounded-full text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none"
              >
                <svg v-if="darkMode" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
              </button>
            </div>
            
            <!-- Menu déroulant du profil -->
            <div class="ml-3 relative">
              <Dropdown align="right" width="48">
                <template #trigger>
                  <button class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition duration-150 ease-in-out">
                    <div>{{ $page.props.auth.user.name }}</div>
                    <div class="ml-1">
                      <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </button>
                </template>
                
                <template #content>
                  <DropdownLink :href="route('profile.edit')">
                    Profil
                  </DropdownLink>
                  <DropdownLink :href="route('logout')" method="post" as="button">
                    Déconnexion
                  </DropdownLink>
                </template>
              </Dropdown>
            </div>
          </div>
          
          <!-- Bouton hamburger pour mobile -->
          <div class="-mr-2 flex items-center sm:hidden">
            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Menu de navigation mobile -->
      <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <ResponsiveNavLink :href="route('seo.dashboard')" :active="route().current('seo.dashboard')">
            Tableau de bord
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('seo.meta.index')" :active="route().current('seo.meta.*')">
            Métadonnées
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('seo.analyses.index')" :active="route().current('seo.analyses.*')">
            Analyses
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('seo.settings')" :active="route().current('seo.settings')">
            Paramètres
          </ResponsiveNavLink>
        </div>
        
        <!-- Menu de profil mobile -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
          <div class="px-4">
            <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ $page.props.auth.user.name }}</div>
            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
          </div>
          
          <div class="mt-3 space-y-1">
            <ResponsiveNavLink :href="route('profile.edit')">
              Profil
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
              Déconnexion
            </ResponsiveNavLink>
          </div>
        </div>
      </div>
    </nav>
    
    <!-- En-tête de la page -->
    <header v-if="$slots.header" class="bg-white dark:bg-gray-800 shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>
    
    <!-- Contenu principal -->
    <main>
      <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Fil d'Ariane -->
          <div v-if="breadcrumbs && breadcrumbs.length > 0" class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
              <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                  <Link :href="route('seo.dashboard')" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                      <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    SEO
                  </Link>
                </li>
                <li v-for="(item, index) in breadcrumbs" :key="index">
                  <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <Link 
                      v-if="item.url" 
                      :href="item.url" 
                      class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white"
                    >
                      {{ item.name }}
                    </Link>
                    <span 
                      v-else 
                      class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400"
                    >
                      {{ item.name }}
                    </span>
                  </div>
                </li>
              </ol>
            </nav>
          </div>
          
          <!-- Contenu de la page -->
          <slot />
        </div>
      </div>
    </main>
    
    <!-- Pied de page -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-4">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ new Date().getFullYear() }} Seodably - Tous droits réservés
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Version {{ version }}
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

defineProps({
  breadcrumbs: {
    type: Array,
    default: () => []
  },
  version: {
    type: String,
    default: '1.0.0'
  }
});

const page = usePage();
const showingNavigationDropdown = ref(false);

// Gestion du mode sombre
const darkMode = ref(localStorage.getItem('darkMode') === 'true' || 
                   (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches));

watch(darkMode, (newValue) => {
  localStorage.setItem('darkMode', newValue);
  if (newValue) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}, { immediate: true });
</script>

<style>
/* Styles globaux pour le mode sombre */
.dark {
  color-scheme: dark;
}

/* Transition pour le mode sombre */
html.dark {
  transition: background-color 0.3s ease;
}
</style> 