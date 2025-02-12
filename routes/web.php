<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

 
Route::get('/redirect', function (Request $request) {
    $request->session()->put('state', $state = Str::random(40));

    $request->session()->put(
        'code_verifier', $code_verifier = Str::random(128)
    );

    $codeChallenge = strtr(rtrim(
        base64_encode(hash('sha256', $code_verifier, true))
        , '='), '+/', '-_');

    $query = http_build_query([
        'client_id' => '9e2dc014-faf7-48ff-b707-c06a489504c9',
        'redirect_uri' => 'http://localhost:8001/callback',
        'response_type' => 'code',
        'scope' => '',
        'state' => $state,
        'code_challenge' => $codeChallenge,
        'code_challenge_method' => 'S256',
        // 'prompt' => 'login', // "none", "consent", or "login"
    ]);
 
    return redirect('http://localhost:8000/oauth/authorize?'.$query);
});

Route::get('/callback', function (Request $request) {
    // dd($request->all());
    
    $state = $request->session()->pull('state');
    
    $codeVerifier = $request->session()->pull('code_verifier');

    
    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class,
        'Invalid state value.'
    );
 
    $response = Http::asForm()->post('http://localhost:8000/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => '9e2dc014-faf7-48ff-b707-c06a489504c9',
        'redirect_uri' => 'http://localhost:8001/callback',
        'code_verifier' => $codeVerifier,
        'code' => $request->code,
    ]);
 
    return $response->json();
});

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

require __DIR__.'/auth.php';
