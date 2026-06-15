<?php

use App\Http\Controllers\LobbyController;
use App\Livewire\Lobby\CreateRoom;
use App\Livewire\Lobby\JoinRoom;
use App\Livewire\Narrator\NarratorLobby;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'fr'])) {
        session(['locale' => $locale]);
    }
    return redirect()->route('home');
})->name('locale');

Route::get('/create', CreateRoom::class)->name('create');
Route::get('/join/{code?}', JoinRoom::class)->name('join');

Route::get('/room/{room}/narrator', NarratorLobby::class)->name('room.narrator');

Route::post('/api/rooms', [LobbyController::class, 'create'])->name('api.rooms.create');
Route::post('/api/rooms/join', [LobbyController::class, 'join'])->name('api.rooms.join');
