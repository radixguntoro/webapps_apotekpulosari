var app = angular.module('factoryReturns', []);

app.factory('returnsFactory', function ($http) {
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
    init.readDataBySearch = (state, page_number, search_text, page_row, sort_by, filter) => {
        return $http({
            method: 'GET',
            url: `/admin/return/list?state=${state}&search=${search_text}&page=${page_number}&row=${page_row}&sort=${sort_by}&filter=${filter}`
        }).then(function(response) {
            init.result_data = response.data.data;
            init.total_data = response.data.total;
        });
    };

    init.readDataByPagination = (state, page_number, page_row, sort_by, filter) => {
        return $http({
            method: 'GET',
            url: `/admin/return/list?state=${state}&page=${page_number}&row=${page_row}&sort=${sort_by}&filter=${filter}`
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
    init.createData = (data) => {
        console.log("create an", data);
        return $http({
            method: 'POST',
            url: `/admin/return/create`,
            data: {
                return: data
            },
        }).then(function(response) {
            init.result_process = response.data.status;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    init.readDataById = (id, state) => {
        return $http({
            method: 'GET',
            url: `/admin/return/read/detail?id=${id}&state=${state}`,
        }).then(function(response) {
            init.result_data = response.data;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data
    |--------------------------------------------------------------------------
    */
    init.deleteData = (data) => {
        return $http({
            method: 'POST',
            url: `/admin/return/delete`,
            data: {
                return: data
            }
        }).then(function(response) {
            init.result_process = response.data;
        });
    }

    return init;
})
