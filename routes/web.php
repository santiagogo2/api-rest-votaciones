<?php

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

// Rutas para la gestión de usuarios
Route::post('/api/user/login','UserController@login');
Route::post('/api/user/massive','UserController@massiveStore')->middleware('api-auth');
Route::resource('/api/user', 'UserController')->middleware('api-auth');
Route::get('/api/user/cc/{idNumber}', 'UserController@showByIdNumber')->middleware('api-auth');
Route::put('/api/user/update/password/{id}', 'UserController@passwordUpdate')->middleware('api-auth');

// Rutas para la gestión de los postulados
Route::resource('/api/postulates', 'PostulatesController')->middleware('api-auth');
Route::get('/api/postulates/by/category/{category}', 'PostulatesController@getPostulateByCategory')->middleware('api-auth');
Route::get('/api/postulates/by/category/results/{category}', 'PostulatesController@getPostulateByCategoryWithResults')->middleware('api-auth');
Route::get('/api/postulates/get/image/{filename}', 'PostulatesController@getPostulateImage');
Route::post('/api/postulates/upload/image', 'PostulatesController@upload')->middleware('api-auth');

// Rutas para la gestión de las votaciones
Route::resource('/api/votes', 'VotesController')->middleware('api-auth');
Route::get('/api/votes/search/vote/{userId}/{categoryId}', 'VotesController@searchVote')->middleware('api-auth');
Route::get('/api/votes/search/results/category/postulate/{categoryId}/{postulateId}', 'VotesController@getResultsByPostulate')->middleware('api-auth');

// Rutas para procesar las categorías de las votaciones
Route::resource('/api/category/votes', 'VotesCategoryController')->middleware('api-auth');