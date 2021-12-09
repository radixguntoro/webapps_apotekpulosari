<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/connection/check', 'ConnectionController@readStatus');
Route::post('/admin/auth/login', 'Auth\LoginController@login');
Route::post('/admin/auth/logout', 'Auth\LoginController@logout');
Route::group(['middleware' => ['auth:api']], function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboards Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/dashboard/total/asset', 'Backend\Dashboard\DashboardCtrl@readDataTotalAsset');
    Route::get('/admin/dashboard/transactions/day', 'Backend\Dashboard\DashboardCtrl@readDataTransactionPerDay');
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
    Route::get('/admin/medicine/read/barcode', 'Backend\Master\MedicinesCtrl@readDataByBarcode');
    Route::get('/admin/medicine/read/autocomplete', 'Backend\Master\MedicinesCtrl@readAutocomplete');
    Route::post('/admin/medicine/update', 'Backend\Master\MedicinesCtrl@update');
    Route::post('/admin/medicine/update/status', 'Backend\Master\MedicinesCtrl@updateStatus');
    Route::post('/admin/medicine/delete', 'Backend\Master\MedicinesCtrl@delete');
    /*
    |--------------------------------------------------------------------------
    | Users Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/user', 'Backend\Master\UsersCtrl@index');
    Route::post('/admin/user/create', 'Backend\Master\UsersCtrl@create');
    Route::get('/admin/user/read/list', 'Backend\Master\UsersCtrl@index');
    Route::get('/admin/user/read/data', 'Backend\Master\UsersCtrl@readDataById');
    Route::get('/admin/user/read/login', 'Backend\Master\UsersCtrl@readLogin');
    Route::get('/admin/user/read/roles', 'Backend\Master\UsersCtrl@readDataRoles');
    Route::post('/admin/user/update', 'Backend\Master\UsersCtrl@update');
    Route::post('/admin/user/update/profile', 'Backend\Master\UsersCtrl@update');
    Route::post('/admin/user/update/password', 'Backend\Master\UsersCtrl@updatePassword');
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
    | Customers Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/customer', 'Backend\Master\CustomersCtrl@index');
    Route::post('/admin/customer/create', 'Backend\Master\CustomersCtrl@create');
    Route::get('/admin/customer/read/list', 'Backend\Master\CustomersCtrl@index');
    Route::get('/admin/customer/read/id/{id}', 'Backend\Master\CustomersCtrl@readDataById');
    Route::get('/admin/customer/read/all', 'Backend\Master\CustomersCtrl@readDataAll');
    Route::get('/admin/customer/read/autocomplete', 'Backend\Master\CustomersCtrl@readAutocomplete');
    Route::post('/admin/customer/update', 'Backend\Master\CustomersCtrl@update');
    Route::post('/admin/customer/update/status', 'Backend\Master\CustomersCtrl@updateStatus');
    Route::post('/admin/customer/delete', 'Backend\Master\CustomersCtrl@delete');
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
    | Sales Recipe Routes
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
    | Sales Netto Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/trSalesNetto/list', 'Backend\Transaction\TrSalesNettoCtrl@index');
    Route::get('/admin/trSalesNetto/invoice', 'Backend\Transaction\TrSalesNettoCtrl@testInvoice');
    Route::post('/admin/trSalesNetto/create/data', 'Backend\Transaction\TrSalesNettoCtrl@create');
    Route::post('/admin/trSalesNetto/create/return', 'Backend\Transaction\TrSalesNettoCtrl@createReturn');
    Route::post('/admin/trSalesNetto/create/repayment', 'Backend\Transaction\TrSalesNettoCtrl@createRepayment');
    Route::post('/admin/trSalesNetto/create/adjustments', 'Backend\Transaction\TrSalesNettoCtrl@createAdjustments');
    Route::get('/admin/trSalesNetto/testInvoice', 'Backend\Transaction\TrSalesNettoCtrl@testInvoice');
    Route::get('/admin/trSalesNetto/read/{id}', 'Backend\Transaction\TrSalesNettoCtrl@readDataById');
    Route::get('/admin/trSalesNetto/recap/print/{id}', 'Backend\Transaction\TrSalesNettoCtrl@readDataById');
    /*
    |--------------------------------------------------------------------------
    | Sales Lab Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/trSalesCredit/list', 'Backend\Transaction\TrSalesCreditCtrl@index');
    Route::get('/admin/trSalesCredit/invoice', 'Backend\Transaction\TrSalesCreditCtrl@testInvoice');
    Route::post('/admin/trSalesCredit/create/data', 'Backend\Transaction\TrSalesCreditCtrl@create');
    Route::post('/admin/trSalesCredit/create/repayment', 'Backend\Transaction\TrSalesCreditCtrl@createRepayment');
    Route::get('/admin/trSalesCredit/testInvoice', 'Backend\Transaction\TrSalesCreditCtrl@testInvoice');
    Route::get('/admin/trSalesCredit/read/{id}', 'Backend\Transaction\TrSalesCreditCtrl@readDataById');
    Route::get('/admin/trSalesCredit/recap/print/{id}', 'Backend\Transaction\TrSalesCreditCtrl@readDataById');
    /*
    |--------------------------------------------------------------------------
    | Sales Return Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/return/read/list', 'Backend\Transaction\ReturnCtrl@index');
    Route::post('/admin/return/create', 'Backend\Transaction\ReturnCtrl@create');
    Route::get('/admin/return/invoice', 'Backend\Transaction\ReturnCtrl@testInvoice');
    /*
    |--------------------------------------------------------------------------
    | Sales Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/trPurchase/list', 'Backend\Transaction\TrPurchaseCtrl@index');
    Route::post('/admin/trPurchase/create/data', 'Backend\Transaction\TrPurchaseCtrl@create');
    Route::post('/admin/trPurchase/create/repayment', 'Backend\Transaction\TrPurchaseCtrl@createRepayment');
    Route::post('/admin/trPurchase/create/return', 'Backend\Transaction\TrPurchaseCtrl@createReturn');
    Route::post('/admin/trPurchase/create/adjustments', 'Backend\Transaction\TrPurchaseCtrl@createAdjustments');
    Route::get('/admin/trPurchase/read/data/{id}', 'Backend\Transaction\TrPurchaseCtrl@readDataById');
    Route::get('/admin/trPurchase/read/detail/data/{id}', 'Backend\Transaction\TrPurchaseCtrl@readDataDetailById');
    Route::get('/admin/trPurchase/read/detail/medicine/{id}', 'Backend\Transaction\TrPurchaseCtrl@readDataDetailByMedicineId');
    Route::get('/admin/trPurchase/read/exist/invoice/{id}', 'Backend\Transaction\TrPurchaseCtrl@readExistInvoice');
    Route::get('/admin/trPurchase/recap/print/{id}', 'Backend\Transaction\TrPurchaseCtrl@readDataById');
    Route::post('/admin/trPurchase/update/date', 'Backend\Transaction\TrPurchaseCtrl@updateInvoiceDate');
    Route::post('/admin/trPurchase/update/data', 'Backend\Transaction\TrPurchaseCtrl@updateData');
    Route::post('/admin/trPurchase/update/status', 'Backend\Transaction\TrPurchaseCtrl@updateStatus');
    Route::post('/admin/trPurchase/delete/data', 'Backend\Transaction\TrPurchaseCtrl@delete');
    /*
    |--------------------------------------------------------------------------
    | Closing Cashier Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/closingCashier/list', 'Backend\Transaction\ClosingCashierCtrl@index');
    Route::post('/admin/closingCashier/create', 'Backend\Transaction\ClosingCashierCtrl@create');
    Route::get('/admin/closingCashier/sales/all', 'Backend\Transaction\ClosingCashierCtrl@readDataSalesAll');
    Route::get('/admin/closingCashier/sales/type', 'Backend\Transaction\ClosingCashierCtrl@readDataSalesType');
    Route::get('/admin/closingCashier/shift', 'Backend\Transaction\ClosingCashierCtrl@readShift');
    Route::get('/admin/closingCashier/cashier', 'Backend\Transaction\ClosingCashierCtrl@readDataByCashier');
    Route::post('/admin/closingCashier/update', 'Backend\Transaction\ClosingCashierCtrl@update');
    /*
    |--------------------------------------------------------------------------
    | Stock Opname Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/admin/stockOpname/create', 'Backend\Inventory\StockOpnameCtrl@create');
    Route::get('/admin/stockOpname/read/data/list', 'Backend\Inventory\StockOpnameCtrl@index');
    Route::get('/admin/stockOpname/read/medicine/list', 'Backend\Inventory\StockOpnameCtrl@readMedicineList');
    Route::get('/admin/stockOpname/testInvoice', 'Backend\Inventory\StockOpnameCtrl@testInvoice');
    /*
    |--------------------------------------------------------------------------
    | Stock Adjustments Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/admin/stockAdjustments/create/incomingGoods', 'Backend\Inventory\StockAdjustmentsCtrl@createIncomingGoods');
    Route::post('/admin/stockAdjustments/create/exitGoods', 'Backend\Inventory\StockAdjustmentsCtrl@createExitGoods');
    Route::get('/admin/stockAdjustments/read/data/list', 'Backend\Inventory\StockAdjustmentsCtrl@index');
    Route::get('/admin/stockAdjustments/testInvoice', 'Backend\Inventory\StockAdjustmentsCtrl@testInvoice');
    /*
    |--------------------------------------------------------------------------
    | Report Item Card Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/report/item/card/list/data', 'Backend\Report\ReportItemCardCtrl@readHistoryStockList');
    Route::get('/admin/report/item/card/detail/data', 'Backend\Report\ReportItemCardCtrl@readHistoryStockDetail');
    Route::get('/admin/report/item/card/stock/data', 'Backend\Report\ReportItemCardCtrl@readItemStockList');
    Route::get('/admin/report/item/card/stock/out', 'Backend\Report\ReportItemCardCtrl@readStockOutList');
    Route::get('/admin/report/item/card/stock/in', 'Backend\Report\ReportItemCardCtrl@readStockInList');
    /*
    |--------------------------------------------------------------------------
    | Report Sales Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/report/sales/list/data', 'Backend\Report\ReportSalesCtrl@readDataList');
    Route::get('/admin/report/sales/detail/data', 'Backend\Report\ReportSalesCtrl@readDataDetail');
    /*
    |--------------------------------------------------------------------------
    | Report Purchase Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/report/purchase/list/data', 'Backend\Report\ReportPurchaseCtrl@readDataList');
    Route::get('/admin/report/purchase/detail/data', 'Backend\Report\ReportPurchaseCtrl@readDataDetail');
    /*
    |--------------------------------------------------------------------------
    | Report Stock Opaname Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/report/stockOpname/list/data', 'Backend\Report\ReportStockOpnameCtrl@readDataList');
});
// Route::get('/admin/medicine/list', 'Backend\Master\MedicinesCtrl@index');
// Route::get('/admin/user/read/list', 'Backend\Master\UsersCtrl@index');
// Route::get('/admin/user/read/roles', 'Backend\Master\UsersCtrl@readDataRoles');
// Route::get('/admin/trPurchase/recap/print/{id}', 'Backend\Transaction\TrPurchaseCtrl@readDataById');
// Route::get('/admin/trSalesRegular/invoice', 'Backend\Transaction\TrSalesRegularCtrl@testInvoice');
// Route::get('/admin/trSalesRecipe/invoice', 'Backend\Transaction\TrSalesRecipeCtrl@testInvoice');
// Route::get('/admin/trSalesMix/invoice', 'Backend\Transaction\TrSalesMixCtrl@testInvoice');
// Route::get('/admin/trSalesLab/invoice', 'Backend\Transaction\TrSalesLabCtrl@testInvoice');
// Route::get('/admin/trSalesCredit/invoice', 'Backend\Transaction\TrSalesCreditCtrl@testInvoice');
// Route::get('/admin/trSalesNetto/invoice', 'Backend\Transaction\TrSalesNettoCtrl@testInvoice');
// Route::get('/admin/trSalesRegular/recap/print/{id}', 'Backend\Transaction\TrSalesRegularCtrl@readDataById');
// Route::get('/admin/trSalesNetto/recap/print/{id}', 'Backend\Transaction\TrSalesNettoCtrl@readDataById');
// Route::get('/admin/trSalesLab/recap/print/{id}', 'Backend\Transaction\TrSalesLabCtrl@readDataById');
// Route::get('/admin/closingCashier/list', 'Backend\Transaction\ClosingCashierCtrl@index');
// Route::get('/admin/closingCashier/shift', 'Backend\Transaction\ClosingCashierCtrl@readShift');
// Route::get('/admin/closingCashier/sales/all', 'Backend\Transaction\ClosingCashierCtrl@readDataSalesAll');
// Route::get('/admin/closingCashier/sales/type', 'Backend\Transaction\ClosingCashierCtrl@readDataSalesType');
// Route::get('/admin/trSalesNetto/list', 'Backend\Transaction\TrSalesNettoCtrl@index');
// Route::get('/admin/report/purchase/list/data', 'Backend\Report\ReportPurchaseCtrl@readDataList');
// Route::get('/admin/report/purchase/detail/data', 'Backend\Report\ReportPurchaseCtrl@readDataDetail');
// Route::get('/admin/home/read/medicines', 'Client\Home\HomeCtrl@readDataMedicines');
// Route::get('/admin/report/sales/list/data', 'Backend\Report\ReportSalesCtrl@readDataList');
// Route::get('/admin/report/sales/detail/data', 'Backend\Report\ReportSalesCtrl@readDataDetail');
// Route::get('/admin/report/stockOpname/list/data', 'Backend\Report\ReportStockOpnameCtrl@readDataList');
// Route::get('/admin/report/item/card/list/data', 'Backend\Report\ReportItemCardCtrl@readHistoryStockList');
// Route::get('/admin/report/item/card/stock/out', 'Backend\Report\ReportItemCardCtrl@readStockOutList');
// Route::get('/admin/report/item/card/stock/in', 'Backend\Report\ReportItemCardCtrl@readStockInList');
// Route::get('/admin/trPurchase/list', 'Backend\Transaction\TrPurchaseCtrl@index');
// Route::get('/admin/trSalesRegular/recap/print/{id}', 'Backend\Transaction\TrSalesRegularCtrl@readDataById');
// Route::get('/admin/user/read/data', 'Backend\Master\UsersCtrl@readDataById');
// Route::get('/admin/category/read/all', 'Backend\Master\CategoriesCtrl@readDataAll');
// Route::get('/admin/trSalesNetto/read/{id}', 'Backend\Transaction\TrSalesNettoCtrl@readDataById');
// Route::get('/admin/trSalesMix/recap/print/{id}', 'Backend\Transaction\TrSalesMixCtrl@readDataById');
// Route::get('/admin/trSalesNetto/recap/print/{id}', 'Backend\Transaction\TrSalesNettoCtrl@readDataById');
// Route::get('/admin/trSalesCredit/recap/print/{id}', 'Backend\Transaction\TrSalesCreditCtrl@readDataById');
// Route::get('/admin/trPurchase/recap/print/{id}', 'Backend\Transaction\TrPurchaseCtrl@readDataById');
// Route::get('/admin/report/item/card/stock/data', 'Backend\Report\ReportItemCardCtrl@readItemStockList');
// Route::get('/admin/report/item/card/list/data', 'Backend\Report\ReportItemCardCtrl@readHistoryStockList');
// Route::get('/admin/report/item/card/stock/export/data', 'Backend\Report\ReportItemCardCtrl@readItemStockListExport');
// Route::get('/admin/report/sales/list/data', 'Backend\Report\ReportSalesCtrl@readDataList');
// Route::get('/admin/report/purchase/list/data', 'Backend\Report\ReportPurchaseCtrl@readDataList');
// Route::get('/admin/dashboard/total/asset', 'Backend\Dashboard\DashboardCtrl@readDataTotalAsset');
// Route::get('/admin/dashboard/transactions/day', 'Backend\Dashboard\DashboardCtrl@readDataTransactionPerDay');