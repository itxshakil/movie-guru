<?php

declare(strict_types=1);

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DetailController;
use App\Http\Controllers\HomeController;
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

Route::get('/', [HomeController::class, 'index']);
Route::inertia('/terms', 'Terms');
Route::inertia('/privacy', 'PrivacyPolicy');
Route::inertia('/contact', 'Contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/search', [SearchController::class, 'index'])->name('search')->middleware('throttle:search');
Route::get('/search/{search}', [SearchController::class, 'show'])->name('search.show')->middleware('throttle:search');
Route::get('/i/{imdbID}', [DetailController::class, 'show'])->name('movie.show')->middleware('throttle:movie-show');

Route::post('/subscribe', [NewsletterSubscriptionController::class, 'store'])->name('subscribe')->middleware('throttle:api');
Route::post('/unsubscribe', [NewsletterSubscriptionController::class, 'unsubscribe'])->name('unsubscribe')->middleware('throttle:api');

Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
