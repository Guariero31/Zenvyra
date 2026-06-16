<?php

use App\Http\Controllers\SubscriptionBlockedController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/assinatura-pendente/{tenant:te_slug}', SubscriptionBlockedController::class)
    ->name('subscription.blocked');
