var app = angular.module('backendApp', [
    /*
    |--------------------------------------------------------------------------
    | Inject Plugin
    |--------------------------------------------------------------------------
    */
    'angularUtils.directives.dirPagination',
    'angular.filter',
    'pluginCtrl',
    'pluginDrtv',
    'hl.sticky',
    'toastr',
    'ui.router',
    'ngAnimate',
    'ui.select',
    'ngSanitize',
    'angularFileUpload',
    'ui.utils.masks',
    'toggles',
    'ui.tinymce',
    'ngTabs',
    'ngMap',
    'chart.js',
    'ui.date',
    'globalCtrl',
    'baseCtrl',
    /*
    |--------------------------------------------------------------------------
    | Inject Master
    |--------------------------------------------------------------------------
    */
    'medicinesCtrl',
    'categoriesCtrl',
    'unitsCtrl',
    'patientsCtrl',
    'usersCtrl',
    'suppliersCtrl',
    /*
    |--------------------------------------------------------------------------
    | Inject Transaction
    |--------------------------------------------------------------------------
    */
    'trSalesRegularCtrl',
    'trSalesRecipeCtrl',
    'trSalesMixCtrl',
    'trSalesLabCtrl',
    'trPurchaseCtrl',
    /*
    |--------------------------------------------------------------------------
    | Inject Return
    |--------------------------------------------------------------------------
    */
    'returnsCtrl'
]);

app.config(function ($urlRouterProvider, $stateProvider, toastrConfig) {
    /*
    |--------------------------------------------------------------------------
    | Toastr Plugin Configurations
    |--------------------------------------------------------------------------
    */
    angular.extend(toastrConfig, {
        autoDismiss: false,
        containerId: 'toast-container',
        maxOpened: 0,
        newestOnTop: true,
        positionClass: 'toast-top-right',
        preventDuplicates: false,
        preventOpenDuplicates: false,
        target: 'body'
    });
    /*
    |--------------------------------------------------------------------------
    | Route Configurations
    |--------------------------------------------------------------------------
    */
    $stateProvider
        /*
        |--------------------------------------------------------------------------
        | Dashboard Routes
        |--------------------------------------------------------------------------
        */
        .state('admin-medicine-list', {
            url: '/',
            templateUrl: '../templates/backend/master/medicines/medicineList.html',
            controller: 'medicinesController'
            /*
            |--------------------------------------------------------------------------
            | Master Medicine Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-medicine-create', {
            url: '/medicine/create',
            templateUrl: '../templates/backend/master/medicines/medicineCreate.html',
            controller: 'medicinesController'
        }).state('admin-medicine-edit', {
            url: '/medicine/edit/:id',
            templateUrl: '../templates/backend/master/medicines/medicineEdit.html',
            controller: 'medicinesController'
            /*
            |--------------------------------------------------------------------------
            | Master Category Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-category-list', {
            url: '/category/list',
            templateUrl: '../templates/backend/master/categories/categoryList.html',
            controller: 'categoriesController'
        }).state('admin-category-create', {
            url: '/category/create',
            templateUrl: '../templates/backend/master/categories/categoryCreate.html',
            controller: 'categoriesController'
        }).state('admin-category-edit', {
            url: '/category/edit/:id',
            templateUrl: '../templates/backend/master/categories/categoryEdit.html',
            controller: 'categoriesController'
            /*
            |--------------------------------------------------------------------------
            | Master Unit Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-unit-list', {
            url: '/unit/list',
            templateUrl: '../templates/backend/master/units/unitList.html',
            controller: 'unitsController'
        }).state('admin-unit-create', {
            url: '/unit/create',
            templateUrl: '../templates/backend/master/units/unitCreate.html',
            controller: 'unitsController'
        }).state('admin-unit-edit', {
            url: '/unit/edit/:id',
            templateUrl: '../templates/backend/master/units/unitEdit.html',
            controller: 'unitsController'
            /*
            |--------------------------------------------------------------------------
            | Master Patient Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-patient-list', {
            url: '/patient/list',
            templateUrl: '../templates/backend/master/patients/patientList.html',
            controller: 'patientsController'
        }).state('admin-patient-create', {
            url: '/patient/create',
            templateUrl: '../templates/backend/master/patients/patientCreate.html',
            controller: 'patientsController'
        }).state('admin-patient-edit', {
            url: '/patient/edit/:id',
            templateUrl: '../templates/backend/master/patients/patientEdit.html',
            controller: 'patientsController'
            /*
            |--------------------------------------------------------------------------
            | Master User Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-user-list', {
            url: '/user/list',
            templateUrl: '../templates/backend/master/users/userList.html',
            controller: 'usersController'
        }).state('admin-user-create', {
            url: '/user/create',
            templateUrl: '../templates/backend/master/users/userCreate.html',
            controller: 'usersController'
        }).state('admin-user-edit', {
            url: '/user/edit/:id',
            templateUrl: '../templates/backend/master/users/userEdit.html',
            controller: 'usersController'
            /*
            |--------------------------------------------------------------------------
            | Master Supplier Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-supplier-list', {
            url: '/supplier/list',
            templateUrl: '../templates/backend/master/suppliers/supplierList.html',
            controller: 'suppliersController'
        }).state('admin-supplier-create', {
            url: '/supplier/create',
            templateUrl: '../templates/backend/master/suppliers/supplierCreate.html',
            controller: 'suppliersController'
        }).state('admin-supplier-edit', {
            url: '/supplier/edit/:id',
            templateUrl: '../templates/backend/master/suppliers/supplierEdit.html',
            controller: 'suppliersController'
            /*
            |--------------------------------------------------------------------------
            | Transaction Sales Regular Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-trSalesRegular-recap', {
            url: '/trSalesRegular/recap',
            templateUrl: '../templates/backend/transaction/trSales/trSalesRegular/trSalesRegularRecap.html',
            controller: 'trSalesRegularController'
        }).state('admin-trSalesRegular-print', {
            url: '/trSalesRegular/recap/print/:id',
            templateUrl: '../templates/backend/transaction/trSales/trSalesRegular/trSalesRegularPrint.html',
            controller: 'trSalesRegularController'
        }).state('admin-trSalesRegular-create', {
            url: '/trSalesRegular/create',
            templateUrl: '../templates/backend/transaction/trSales/trSalesRegular/trSalesRegularCreate.html',
            controller: 'trSalesRegularController'
            /*
            |--------------------------------------------------------------------------
            | Transaction Sales Lab Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-trSalesLab-recap', {
            url: '/trSalesLab/recap',
            templateUrl: '../templates/backend/transaction/trSales/trSalesLab/trSalesLabRecap.html',
            controller: 'trSalesLabController'
        }).state('admin-trSalesLab-print', {
            url: '/trSalesLab/recap/print/:id',
            templateUrl: '../templates/backend/transaction/trSales/trSalesLab/trSalesLabPrint.html',
            controller: 'trSalesLabController'
        }).state('admin-trSalesLab-create', {
            url: '/trSalesLab/create',
            templateUrl: '../templates/backend/transaction/trSales/trSalesLab/trSalesLabCreate.html',
            controller: 'trSalesLabController'
            /*
            |--------------------------------------------------------------------------
            | Transaction Sales Mix Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-trSalesMix-recap', {
            url: '/trSalesMix/recap',
            templateUrl: '../templates/backend/transaction/trSales/trSalesMix/trSalesMixRecap.html',
            controller: 'trSalesMixController'
        }).state('admin-trSalesMix-print', {
            url: '/trSalesMix/recap/print/:id',
            templateUrl: '../templates/backend/transaction/trSales/trSalesMix/trSalesMixPrint.html',
            controller: 'trSalesMixController'
        }).state('admin-trSalesMix-create', {
            url: '/trSalesMix/create',
            templateUrl: '../templates/backend/transaction/trSales/trSalesMix/trSalesMixCreate.html',
            controller: 'trSalesMixController'
            /*
            |--------------------------------------------------------------------------
            | Transaction Sales Recipe Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-trSalesRecipe-recap', {
            url: '/trSalesRecipe/recap',
            templateUrl: '../templates/backend/transaction/trSales/trSalesRecipe/trSalesRecipeRecap.html',
            controller: 'trSalesRecipeController'
        }).state('admin-trSalesRecipe-print', {
            url: '/trSalesRecipe/recap/print/:id',
            templateUrl: '../templates/backend/transaction/trSales/trSalesRecipe/trSalesRecipePrint.html',
            controller: 'trSalesRecipeController'
        }).state('admin-trSalesRecipe-create', {
            url: '/trSalesRecipe/create',
            templateUrl: '../templates/backend/transaction/trSales/trSalesRecipe/trSalesRecipeCreate.html',
            controller: 'trSalesRecipeController'
            /*
            |--------------------------------------------------------------------------
            | Transaction Purchase Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-trPurchase-recap', {
            url: '/trPurchase/recap',
            templateUrl: '../templates/backend/transaction/trPurchase/trPurchaseRecap.html',
            controller: 'trPurchaseController'
        }).state('admin-trPurchase-print', {
            url: '/trPurchase/recap/print/:id',
            templateUrl: '../templates/backend/transaction/trPurchase/trPurchasePrint.html',
            controller: 'trPurchaseController'
        }).state('admin-trPurchase-return', {
            url: '/trPurchase/recap/return/:id',
            templateUrl: '../templates/backend/transaction/trPurchase/trPurchaseReturn.html',
            controller: 'trPurchaseController'
        }).state('admin-trPurchase-create', {
            url: '/trPurchase/create',
            templateUrl: '../templates/backend/transaction/trPurchase/trPurchaseCreate.html',
            controller: 'trPurchaseController'
            /*
            |--------------------------------------------------------------------------
            | Return Routes
            |--------------------------------------------------------------------------
            */
        }).state('admin-return-trPurchase-list', {
            url: '/return/trPurchase/list',
            templateUrl: '../templates/backend/return/trPurchase/returnTrPurchaseList.html',
            controller: 'returnsController'
        }).state('admin-return-trPurchase-detail', {
            url: '/return/trPurchase/detail/:id',
            templateUrl: '../templates/backend/return/trPurchase/returnTrPurchaseDetail.html',
            controller: 'returnsController'
        });
    $urlRouterProvider.otherwise('/');
});
