<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RajaOngkirController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ---------------------
// ADMIN PAGE
// ---------------------
Route::get('/admin', function () {
    $user = Auth::user();
    if ($user && $user->role === 'admin') {
        return app(PlantController::class)->index();
    }
    return redirect()->route('home');
})->middleware('auth')->name('admin');

// ---------------------
// AUTH ROUTES
// ---------------------
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/signup', function () {
    return view('auth.signup', ['title' => 'Signup']);
});
Route::post('/signup', [AuthController::class, 'signupStore']);

// ---------------------
// PUBLIC PAGES
// ---------------------
Route::get('/', [PlantController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
Route::get('/store', [PlantController::class, 'shop'])->name('store');
Route::get('/guide', [GuideController::class, 'index'])->name('guide');

// Blog CRUD (admin-only)
Route::middleware(['auth', \App\Http\Middleware\CheckIfAdmin::class])->group(function () {
    Route::post('/blog', [\App\Http\Controllers\BlogController::class, 'store'])->name('blogs.store');
    Route::put('/blog/{blog}', [\App\Http\Controllers\BlogController::class, 'update'])->name('blogs.update');
    Route::delete('/blog/{blog}', [\App\Http\Controllers\BlogController::class, 'destroy'])->name('blogs.destroy');
});

// ---------------------
// AUTHENTICATED ROUTES
// ---------------------
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{plant}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Address
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');

    // Location / RajaOngkir
    Route::get('/location', [RajaOngkirController::class, 'index'])->name('location');
    Route::post('/save-address', [RajaOngkirController::class, 'saveAddress'])->name('save.address');
});

// Location AJAX routes (no auth required)
Route::get('/cities/{provinceId}', [RajaOngkirController::class, 'getCities'])->name('cities');
Route::get('/districts/{cityId}', [RajaOngkirController::class, 'getDistricts'])->name('districts');
Route::post('/check-ongkir', [RajaOngkirController::class, 'checkOngkir'])->name('check.ongkir');

// ---------------------
// RESOURCES
// ---------------------
Route::resource('categories', CategoryController::class);
Route::resource('plants', PlantController::class);
Route::resource('users', UserController::class);
Route::resource('promotions', PromotionController::class);
Route::resource('address', AddressController::class);

Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::post('/checkout', [PaymentController::class, 'checkout'])->name('checkout');
