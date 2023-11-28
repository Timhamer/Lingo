<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Monke;
use App\Livewire\Lingo;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', Lingo::class)->name('lingo');
