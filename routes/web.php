<?php

use App\Http\Controllers\Auth\KeycloakController;
use Illuminate\Support\Facades\Route;

/* Route::get('/', function () {
    return view('welcome');
}); */

Route::middleware(['web'])->group(function () {
    Route::get('auth/keycloak/redirect', [KeycloakController::class, 'redirect'])->name('auth.keycloak.redirect');
    Route::get('auth/keycloak/callback', [KeycloakController::class, 'callback'])->name('auth.keycloak.callback');
    Route::get('logout', [KeycloakController::class, 'logout'])->name('auth.keycloak.logout');
});
