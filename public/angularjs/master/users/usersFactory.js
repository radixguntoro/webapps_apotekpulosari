var app = angular.module('factoryUsers', []);
app.factory('usersFactory', function($http, toastr) {
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
            url: '/admin/user/list?search=' + search_text + '&page=' + page_number + '&row=' + page_row + '&sort=' + sort_by
        }).then(function(response) {
            init.result_data = response.data.data;
            init.total_data = response.data.total;
        });
    };

    init.readDataByPagination = function (page_number, page_row, sort_by) {
        return $http({
            method: 'GET',
            url: '/admin/user/list?page=' + page_number + '&row=' + page_row + '&sort=' + sort_by
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
        console.log(data);
        return $http({
            method: 'POST',
            url: '/admin/user/create',
            data: {
                user: data
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
            url: '/admin/user/read/id/' + id,
        }).then(function(response) {
            init.result_data = response.data;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by login
    |--------------------------------------------------------------------------
    */
    init.readLogin = function () {
        return $http({
            method: 'GET',
            url: '/admin/user/read/login'
        }).then(function(response) {
            init.result_data = response.data
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
            url: '/admin/user/update',
            data: {
                user: data
            },
        }).then(function(response) {
            init.result_process = response.data;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Update data status
    |--------------------------------------------------------------------------
    */
    init.updateDataStatus = function (input) {
        return $http({
            method: 'POST',
            url: '/admin/user/update/status',
            data: {
                user: input
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
            url: '/admin/user/delete',
            data: {
                user: data
            }
        }).then(function(response) {
            init.result_process = response.data;
        });
    }
    
    return init;
})
