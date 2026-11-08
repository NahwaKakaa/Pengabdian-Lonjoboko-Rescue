<?php

use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::controller(PublicController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/peta-risiko', 'riskMap')->name('risk-map');
    Route::get('/jalur-evakuasi', 'evacuation')->name('evacuation');
    Route::get('/edukasi', 'education')->name('education');
    Route::get('/edukasi/{content:slug}', 'educationShow')->name('education.show');
    Route::get('/darurat', 'emergency')->name('emergency');
    Route::get('/informasi/{announcement:slug}', 'announcementShow')->name('announcement.show');
    Route::get('/relawan', 'volunteers')->name('volunteers');
});
