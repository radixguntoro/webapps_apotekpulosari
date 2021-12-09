var app = angular.module('factoryTrPurchase', []);

app.factory('trPurchaseFactory', function ($http) {
    /*
    |--------------------------------------------------------------------------
    | Declare
    |--------------------------------------------------------------------------
    */
    var init = {};
    init.data = {};
    init.result_data = [];
    init.total_data = [];
    init.result_process = '';
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    init.readDataBySearch = function (page_number, search_text, page_row, sort_by, filter) {
        return $http({
            method: 'GET',
            url: '/admin/trPurchase/list?search=' + search_text + '&page=' + page_number + '&row=' + page_row + '&sort=' + sort_by + '&filter=' + filter
        }).then(function(response) {
            init.result_data = response.data.data;
            init.total_data = response.data.total;
        });
    };

    init.readDataByPagination = function (page_number, page_row, sort_by, filter) {
        return $http({
            method: 'GET',
            url: '/admin/trPurchase/list?page=' + page_number + '&row=' + page_row + '&sort=' + sort_by + '&filter=' + filter
        }).then(function(response) {
            init.result_data = response.data.data;
            init.total_data = response.data.total;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Create data
    |--------------------------------------------------------------------------
    */
    init.createData = function(data) {
        console.log("create an", data);
        return $http({
            method: 'POST',
            url: '/admin/trPurchase/create',
            data: {
                trPurchase: data
            },
        }).then(function(response) {
            init.result_process = response.data;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    init.readDataById = function(id) {
        return $http({
            method: 'GET',
            url: '/admin/trPurchase/read/' + id,
        }).then(function(response) {
            init.result_data = response.data;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Update data status
    |--------------------------------------------------------------------------
    */
    init.updateDataStatus = function (data) {
        return $http({
            method: 'POST',
            url: '/admin/trPurchase/update/status',
            data: {
                trPurchase: data
            },
        }).then(function (response) {
            init.result_process = response.data;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data
    |--------------------------------------------------------------------------
    */
    init.deleteData = function(data) {
        return $http({
            method: 'POST',
            url: '/admin/trPurchase/delete',
            data: {
                trPurchase: data
            }
        }).then(function(response) {
            init.result_process = response.data;
        });
    }

    return init;
})
