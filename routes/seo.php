<?php

use Illuminate\Support\Facades\Route;
use Seodably\SEO\Http\Controllers\SEOController;
use Seodably\SEO\Http\Controllers\AnalysisController;

/*
|--------------------------------------------------------------------------
| Routes du tableau de bord SEO
|--------------------------------------------------------------------------
|
| Ces routes sont chargées par le SEOServiceProvider et sont protégées par
| le middleware 'web' et 'auth'. Elles sont accessibles via le préfixe
| configuré dans config/seo.php (par défaut: 'admin/seo').
|
*/

// Tableau de bord
Route::get('/', [SEOController::class, 'dashboard'])->name('seo.dashboard');

// Gestion des métadonnées SEO
Route::get('/meta/{seo}', [SEOController::class, 'edit'])->name('seo.edit');
Route::put('/meta/{seo}', [SEOController::class, 'update'])->name('seo.update');

// Analyse SEO
Route::get('/analyses', [AnalysisController::class, 'index'])->name('seo.analyses');
Route::get('/analyses/{analysis}', [AnalysisController::class, 'show'])->name('seo.analysis.show');
Route::post('/analyses', [AnalysisController::class, 'analyze'])->name('seo.analyze');

// Performance SEO
Route::get('/performance/{seo}', [SEOController::class, 'performance'])->name('seo.performance');

// Suggestions SEO
Route::get('/suggestions/{seo}', [SEOController::class, 'suggestions'])->name('seo.suggestions');
Route::post('/analyze/{seo}', [SEOController::class, 'analyze'])->name('seo.analyze.page'); 