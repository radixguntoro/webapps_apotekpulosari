var app = angular.module('factoryTrSalesRecipe', ['firebase']);

app.factory('trSalesRecipeFactory', function ($http) {
    /*
    |--------------------------------------------------------------------------
    | Declare
    |--------------------------------------------------------------------------
    */
    var init = {};
    init.data = {};
    init.result_data = [];
    init.result_firedata = [];
    init.total_data = [];
    init.result_process = '';
    init.result_firebase = '';
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    init.readDataBySearch = function (page_number, search_text, page_row, sort_by) {
        return $http({
            method: 'GET',
            url: '/admin/trSalesRecipe/list?search=' + search_text + '&page=' + page_number + '&row=' + page_row + '&sort=' + sort_by
        }).then(function(response) {
            init.result_data = response.data.data;
            init.total_data = response.data.total;
        });
    };

    init.readDataByPagination = function (page_number, page_row, sort_by) {
        return $http({
            method: 'GET',
            url: '/admin/trSalesRecipe/list?page=' + page_number + '&row=' + page_row + '&sort=' + sort_by
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
            url: '/admin/trSalesRecipe/create',
            data: {
                trSalesRecipe: data
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
            url: '/admin/trSalesRecipe/recap/print/' + id,
        }).then(function(response) {
            init.result_data = response.data;
        });
    }

    return init;
})
