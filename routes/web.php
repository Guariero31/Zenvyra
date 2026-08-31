<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Store\CatalogController;
use App\Http\Controllers\Store\ContactController;
use App\Http\Controllers\Store\ContactFormController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\SitemapController;
use App\Http\Controllers\SubscriptionBlockedController;
use App\Http\Middleware\ResolvePublicStoreTenant;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');
Route::view('/termos', 'legal.terms')->name('legal.terms');
Route::view('/privacidade', 'legal.privacy')->name('legal.privacy');

Route::middleware('guest')->group(function (): void {
    Route::get('/cadastro', [RegisterController::class, 'show'])->name('register.show');
    Route::post('/cadastro', [RegisterController::class, 'store'])->name('register.store');
});

Route::get('/assinatura-pendente/{tenant:te_slug}', SubscriptionBlockedController::class)
    ->name('subscription.blocked');

/*
|--------------------------------------------------------------------------
| Site público dos lojistas
|--------------------------------------------------------------------------
|
| Cada loja tem seu próprio subsite acessível via /{slug}. O middleware
| ResolvePublicStoreTenant resolve o tenant pelo slug, verifica se está
| ativo e aplica o search_path do schema correspondente.
|
*/
Route::middleware(ResolvePublicStoreTenant::class)
    ->prefix('/{slug}')
    ->name('store.')
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/catalogo', CatalogController::class)->name('catalog');
        Route::get('/contato', ContactController::class)->name('contact');
        Route::post('/contato', ContactFormController::class)->name('contact.send')->middleware('throttle:5,1');
        Route::get('/produto/{product}', ProductController::class)->name('product')->whereNumber('product');
        Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
    });
