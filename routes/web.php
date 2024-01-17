<?php

use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::inertia('/', 'Welcome');
Route::inertia('/terms', 'Terms');
Route::inertia('/privacy', 'PrivacyPolicy');
Route::inertia('/contact', 'Contact');
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');

Route::get('/search', [SearchController::class, 'index'])->name('search')->middleware('throttle:search');
Route::get('/i/{imdbID}', [SearchController::class, 'show'])->name('movie.show')->middleware('throttle:movie-show');
Route::get('/i/{imdbID}', [SearchController::class, 'show'])->middleware('throttle:movie-show');

Route::post('/subscribe', [NewsletterSubscriptionController::class, 'store'])->name('subscribe')->middleware('throttle:api');
Route::post('/unsubscribe', [NewsletterSubscriptionController::class, 'unsubscribe'])->name('unsubscribe')->middleware('throttle:api');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
