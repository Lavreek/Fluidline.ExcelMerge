<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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

Route::get('/', function () {
    $files = Storage::Files('/excel');
    return view('welcome',['files' => $files]);
});
Route::post('/upload', '\App\Http\Controllers\ExcelController@upload');
Route::get('/Download/', '\App\Http\Controllers\ExcelController@download');
