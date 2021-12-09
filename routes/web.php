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
Auth::routes();
Route::group(['middleware' => ['auth']], function () {
    /*
    |--------------------------------------------------------------------------
    | Categories Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/category', 'Backend\Master\CategoriesCtrl@index');
    Route::post('/admin/category/create', 'Backend\Master\CategoriesCtrl@create');
    Route::get('/admin/category/read/list', 'Backend\Master\CategoriesCtrl@index');
    Route::get('/admin/category/read/all', 'Backend\Master\CategoriesCtrl@readDataAll');
    Route::get('/admin/category/read/id/{id}', 'Backend\Master\CategoriesCtrl@readDataById');
    Route::post('/admin/category/update', 'Backend\Master\CategoriesCtrl@update');
    Route::post('/admin/category/delete', 'Backend\Master\CategoriesCtrl@delete');
    /*
    |--------------------------------------------------------------------------
    | Units Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/unit', 'Backend\Master\UnitsCtrl@index');
    Route::post('/admin/unit/create', 'Backend\Master\UnitsCtrl@create');
    Route::get('/admin/unit/read/list', 'Backend\Master\UnitsCtrl@index');
    Route::get('/admin/unit/read/all', 'Backend\Master\UnitsCtrl@readDataAll');
    Route::get('/admin/unit/read/id/{id}', 'Backend\Master\UnitsCtrl@readDataById');
    Route::post('/admin/unit/update', 'Backend\Master\UnitsCtrl@update');
    Route::post('/admin/unit/delete', 'Backend\Master\UnitsCtrl@delete');
    /*
    |--------------------------------------------------------------------------
    | Medicines Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/', 'Backend\Dashboard\DashboardCtrl@index')->name('dashboard');
    Route::get('/admin/medicine', 'Backend\Master\MedicinesCtrl@index');
    Route::get('/admin/medicine/list', 'Backend\Master\MedicinesCtrl@index');
    Route::post('/admin/medicine/create', 'Backend\Master\MedicinesCtrl@create');
    Route::get('/admin/medicine/read/id/{id}', 'Backend\Master\MedicinesCtrl@readDataById');
    Route::get('/admin/medicine/read/autocomplete', 'Backend\Master\MedicinesCtrl@readAutocomplete');
    Route::post('/admin/medicine/update', 'Backend\Master\MedicinesCtrl@update');
    Route::post('/admin/medicine/update/status', 'Backend\Master\MedicinesCtrl@updateStatus');
    Route::post('/admin/medicine/delete', 'Backend\Master\MedicinesCtrl@delete');
    /*
    |--------------------------------------------------------------------------
    | Patients Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/patient', 'Backend\Master\PatientsCtrl@index');
    Route::get('/admin/patient/list', 'Backend\Master\PatientsCtrl@index');
    Route::post('/admin/patient/create', 'Backend\Master\PatientsCtrl@create');
    Route::get('/admin/patient/read/id/{id}', 'Backend\Master\PatientsCtrl@readDataById');
    Route::get('/admin/patient/read/autocomplete', 'Backend\Master\PatientsCtrl@readAutocomplete');
    Route::post('/admin/patient/update', 'Backend\Master\PatientsCtrl@update');
    Route::post('/admin/patient/update/status', 'Backend\Master\PatientsCtrl@updateStatus');
    Route::post('/admin/patient/delete', 'Backend\Master\PatientsCtrl@delete');
    /*
    |--------------------------------------------------------------------------
    | Users Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/user', 'Backend\Master\UsersCtrl@index');
    Route::get('/admin/user/list', 'Backend\Master\UsersCtrl@index');
    Route::post('/admin/user/create', 'Backend\Master\UsersCtrl@create');
    Route::get('/admin/user/read/id/{id}', 'Backend\Master\UsersCtrl@readDataById');
    Route::get('/admin/user/read/login', 'Backend\Master\UsersCtrl@readLogin');
    Route::post('/admin/user/update', 'Backend\Master\UsersCtrl@update');
    Route::post('/admin/user/update/profile', 'Backend\Master\UsersCtrl@update');
    Route::post('/admin/user/update/status', 'Backend\Master\UsersCtrl@updateStatus');
    Route::post('/admin/user/delete', 'Backend\Master\UsersCtrl@delete');
    /*
    |--------------------------------------------------------------------------
    | Suppliers Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/supplier', 'Backend\Master\SuppliersCtrl@index');
    Route::post('/admin/supplier/create', 'Backend\Master\SuppliersCtrl@create');
    Route::get('/admin/supplier/read/list', 'Backend\Master\SuppliersCtrl@index');
    Route::get('/admin/supplier/read/id/{id}', 'Backend\Master\SuppliersCtrl@readDataById');
    Route::get('/admin/supplier/read/all', 'Backend\Master\SuppliersCtrl@readDataAll');
    Route::get('/admin/supplier/read/autocomplete', 'Backend\Master\SuppliersCtrl@readAutocomplete');
    Route::post('/admin/supplier/update', 'Backend\Master\SuppliersCtrl@update');
    Route::post('/admin/supplier/update/status', 'Backend\Master\SuppliersCtrl@updateStatus');
    Route::post('/admin/supplier/delete', 'Backend\Master\SuppliersCtrl@delete');
    /*
    |--------------------------------------------------------------------------
    | Sales Regular Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/trSalesRegular/list', 'Backend\Transaction\TrSalesRegularCtrl@index');
    Route::get('/admin/trSalesRegular/invoice', 'Backend\Transaction\TrSalesRegularCtrl@testInvoice');
    Route::post('/admin/trSalesRegular/create', 'Backend\Transaction\TrSalesRegularCtrl@create');
    Route::get('/admin/trSalesRegular/testInvoice', 'Backend\Transaction\TrSalesRegularCtrl@testInvoice');
    Route::get('/admin/trSalesRegular/read/{id}', 'Backend\Transaction\TrSalesRegularCtrl@readDataById');
    Route::get('/admin/trSalesRegular/recap/print/{id}', 'Backend\Transaction\TrSalesRegularCtrl@readDataById');
    /*
    |--------------------------------------------------------------------------
    | Sales Lab Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/trSalesLab/list', 'Backend\Transaction\TrSalesLabCtrl@index');
    Route::get('/admin/trSalesLab/invoice', 'Backend\Transaction\TrSalesLabCtrl@testInvoice');
    Route::post('/admin/trSalesLab/create', 'Backend\Transaction\TrSalesLabCtrl@create');
    Route::get('/admin/trSalesLab/testInvoice', 'Backend\Transaction\TrSalesLabCtrl@testInvoice');
    Route::get('/admin/trSalesLab/read/{id}', 'Backend\Transaction\TrSalesLabCtrl@readDataById');
    Route::get('/admin/trSalesLab/recap/print/{id}', 'Backend\Transaction\TrSalesLabCtrl@readDataById');
    /*
    |--------------------------------------------------------------------------
    | Sales Mix Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/trSalesMix/list', 'Backend\Transaction\TrSalesMixCtrl@index');
    Route::get('/admin/trSalesMix/invoice', 'Backend\Transaction\TrSalesMixCtrl@testInvoice');
    Route::post('/admin/trSalesMix/create', 'Backend\Transaction\TrSalesMixCtrl@create');
    Route::get('/admin/trSalesMix/testInvoice', 'Backend\Transaction\TrSalesMixCtrl@testInvoice');
    Route::get('/admin/trSalesMix/read/{id}', 'Backend\Transaction\TrSalesMixCtrl@readDataById');
    Route::get('/admin/trSalesMix/recap/print/{id}', 'Backend\Transaction\TrSalesMixCtrl@readDataById');
    /*
    |--------------------------------------------------------------------------
    | Sales Recipe Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/trSalesRecipe/list', 'Backend\Transaction\TrSalesRecipeCtrl@index');
    Route::get('/admin/trSalesRecipe/invoice', 'Backend\Transaction\TrSalesRecipeCtrl@testInvoice');
    Route::post('/admin/trSalesRecipe/create', 'Backend\Transaction\TrSalesRecipeCtrl@create');
    Route::get('/admin/trSalesRecipe/testInvoice', 'Backend\Transaction\TrSalesRecipeCtrl@testInvoice');
    Route::get('/admin/trSalesRecipe/read/{id}', 'Backend\Transaction\TrSalesRecipeCtrl@readDataById');
    Route::get('/admin/trSalesRecipe/recap/print/{id}', 'Backend\Transaction\TrSalesRecipeCtrl@readDataById');
    /*
    |--------------------------------------------------------------------------
    | Purchases Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/trPurchase/list', 'Backend\Transaction\TrPurchaseCtrl@index');
    Route::post('/admin/trPurchase/create/data', 'Backend\Transaction\TrPurchaseCtrl@create');
    Route::post('/admin/trPurchase/create/repayment', 'Backend\Transaction\TrPurchaseCtrl@createRepayment');
    Route::get('/admin/trPurchase/read/data/{id}', 'Backend\Transaction\TrPurchaseCtrl@readDataById');
    Route::get('/admin/trPurchase/read/detail/data/{id}', 'Backend\Transaction\TrPurchaseCtrl@readDataDetailById');
    Route::get('/admin/trPurchase/read/detail/medicine/{id}', 'Backend\Transaction\TrPurchaseCtrl@readDataDetailByMedicineId');
    Route::get('/admin/trPurchase/read/exist/invoice/{id}', 'Backend\Transaction\TrPurchaseCtrl@readExistInvoice');
    Route::post('/admin/trPurchase/update/date', 'Backend\Transaction\TrPurchaseCtrl@updateInvoiceDate');
    Route::post('/admin/trPurchase/update/data', 'Backend\Transaction\TrPurchaseCtrl@updateData');
    Route::post('/admin/trPurchase/update/status', 'Backend\Transaction\TrPurchaseCtrl@updateStatus');
    Route::post('/admin/trPurchase/delete/data', 'Backend\Transaction\TrPurchaseCtrl@delete');
    /*
    |--------------------------------------------------------------------------
    | Returns Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/return/list', 'Backend\Inventory\ReturnsCtrl@index');
    Route::post('/admin/return/create', 'Backend\Inventory\ReturnsCtrl@create');
    Route::get('/admin/return/read/detail', 'Backend\Inventory\ReturnsCtrl@readDataById');
    Route::post('/admin/return/delete', 'Backend\Inventory\ReturnsCtrl@delete');
});

