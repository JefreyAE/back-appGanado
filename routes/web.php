<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Middleware\AuthorizationMiddleware;

Route::get('/', function () {
    return view('welcome');
});

/*Rutas definitivas de la API commit A2*/

Route::post('/api/register','UserController@register');
Route::post('/api/user/login','UserController@login');
Route::get('/api/user/{validation}','UserController@validateAccount');
Route::post('/api/user/update','UserController@update')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);

Route::get('/api/animals/index','AnimalController@index')->middleware(ApiAuthMiddleware::class);
Route::get('/api/animals/dead','AnimalController@dead')->middleware(ApiAuthMiddleware::class);
Route::get('/api/animals/indexAll','AnimalController@indexAll')->middleware(ApiAuthMiddleware::class);
Route::post('/api/animals/create','AnimalController@create')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);
Route::post('/api/animals/find','AnimalController@find')->middleware(ApiAuthMiddleware::class);
Route::get('/api/animals/animal/detail/{id}','AnimalController@detail')->middleware(ApiAuthMiddleware::class);
Route::get('/api/animals/animal/{id}','AnimalController@getAnimal')->middleware(ApiAuthMiddleware::class);
Route::get('/api/animals/injectables/{id}','AnimalController@injectables')->middleware(ApiAuthMiddleware::class);
Route::get('/api/animals/incidents/{id}','AnimalController@incidents')->middleware(ApiAuthMiddleware::class);
Route::get('/api/animals/offprings/{id}','AnimalController@offsprings')->middleware(ApiAuthMiddleware::class);
Route::post('/api/animals/upload','AnimalController@upload')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);
Route::get('/api/animals/image/{filename}','AnimalController@getImage');
Route::get('/api/animals/images_names/{id}','AnimalController@getImagesNames')->middleware(ApiAuthMiddleware::class);
Route::get('/api/animals/deleteImage/{image_name}/{animal_id}','AnimalController@deleteImage')->middleware(ApiAuthMiddleware::class);
Route::put('/api/animals/update','AnimalController@update')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);
Route::get('/api/animals/delete/{id}','AnimalController@deleteAnimal')->middleware(ApiAuthMiddleware::class);

Route::get('/api/injectables/index','InjectableController@index')->middleware(ApiAuthMiddleware::class);
Route::get('/api/injectables/injectable/detail/{creation_time}','InjectableController@detail')->middleware(ApiAuthMiddleware::class);
Route::post('/api/injectables/create','InjectableController@create')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);
Route::delete('/api/injectables/injectable/delete-one', 'InjectableController@deleteOne')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);
Route::delete('/api/injectables/injectable/delete', 'InjectableController@delete')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);

Route::get('/api/incidents/index','IncidentController@index')->middleware(ApiAuthMiddleware::class);
Route::post('/api/incidents/create','IncidentController@create')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);
Route::delete('/api/incidents/incident/delete-one', 'IncidentController@deleteOne')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);

Route::get('/api/sales/index','SaleController@index')->middleware(ApiAuthMiddleware::class);
Route::get('/api/sales/sale/{id}','SaleController@getSale')->middleware(ApiAuthMiddleware::class);
Route::post('/api/sales/create','SaleController@create')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);
Route::post('/api/sales/find','SaleController@find')->middleware(ApiAuthMiddleware::class);
Route::delete('/api/sales/sale/delete-one', 'SaleController@deleteOne')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);
Route::put('/api/sales/update','SaleController@update')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);

Route::get('/api/purchases/index','PurchaseController@index')->middleware(ApiAuthMiddleware::class);
Route::get('/api/purchases/purchase/{id}','PurchaseController@getPurchase')->middleware(ApiAuthMiddleware::class);
Route::post('/api/purchases/create','PurchaseController@create')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);
Route::post('/api/purchases/find','PurchaseController@find')->middleware(ApiAuthMiddleware::class);
Route::delete('/api/purchases/purchase/delete-one', 'PurchaseController@deleteOne')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);
Route::put('/api/purchases/update','PurchaseController@update')->middleware(ApiAuthMiddleware::class, AuthorizationMiddleware::class);

Route::get('/api/notifications/index','NotificationController@index')->middleware(ApiAuthMiddleware::class);
Route::get('/api/notifications/generate','NotificationController@generate')->middleware(ApiAuthMiddleware::class);
Route::get('/api/notifications/indexAll','NotificationController@indexAll')->middleware(ApiAuthMiddleware::class);
Route::get('/api/notifications/checked','NotificationController@checked')->middleware(ApiAuthMiddleware::class);
Route::get('/api/notifications/state/{id}','NotificationController@state')->middleware(ApiAuthMiddleware::class);

Route::get('/api/statistics/index','StatisticsController@index')->middleware(ApiAuthMiddleware::class);
Route::get('/api/statistics/auctions','StatisticsController@auctions')->middleware(ApiAuthMiddleware::class);