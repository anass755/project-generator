<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    GenerateProjectController,
    AiChatController
};
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('docker-create', [GenerateProjectController::class, 'create'])->name('docker.create');
    Route::post('docker', [GenerateProjectController::class, 'store'])->name('docker.store');
    Route::get('docker-list', [GenerateProjectController::class, 'index'])->name('docker.index');
    Route::post('/docker/projects/{id}/start', [DockerController::class, 'startProject']);
    // AI
    Route::post('/chat-ask', [AiChatController::class, 'ask'])->name('chat.ask');
    Route::post('/chat/clear', [AiChatController::class, 'clearHistory']);
});

require __DIR__.'/auth.php';
