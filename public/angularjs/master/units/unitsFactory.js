var app = angular.module('factoryUnits', []);
app.factory('unitsFactory', function($http) {
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
    init.readDataBySearch = function (page_number, search_text, page_row, sort_by) {
        return $http({
            method: 'GET',
            url: '/admin/unit/read/list?search=' + search_text + '&page=' + page_number + '&row=' + page_row + '&sort=' + sort_by
        }).then(function(response) {
            init.result_data = response.data.data;
            init.total_data = response.data.total;
        });
    };

    init.readDataByPagination = function (page_number, page_row, sort_by) {
        return $http({
            method: 'GET',
            url: '/admin/unit/read/list?page=' + page_number + '&row=' + page_row + '&sort=' + sort_by
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
        return $http({
            method: 'POST',
            url: '/admin/unit/create',
            data: {
                unit: data
            },
        }).then(function(response) {
            init.result_process = response.data;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Read all data
    |--------------------------------------------------------------------------
    */
    init.readDataAll = function() {
        return $http({
            method: 'GET',
            url: '/admin/unit/read/all'
        }).then(function(response) {
            init.result_data = response.data;
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
            url: '/admin/unit/read/id/' + id,
        }).then(function(response) {
            init.result_data = response.data;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Update data
    |--------------------------------------------------------------------------
    */
    init.updateData = function(data) {
        return $http({
            method: 'POST',
            url: '/admin/unit/update',
            data: {
                unit: data
            }
        }).then(function(response) {
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
            url: '/admin/unit/delete',
            data: {
                unit: data
            }
        }).then(function(response) {
            init.result_process = response.data;
        });
    }

    return init;
})
