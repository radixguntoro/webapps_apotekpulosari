var app = angular.module('factoryMedicines', []);

app.factory('medicinesFactory', function ($http) {
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
            url: '/admin/medicine/list?search=' + search_text + '&page=' + page_number + '&row=' + page_row + '&sort=' + sort_by + '&filter=' + filter
        }).then(function (response) {
            init.result_data = response.data.data;
            init.total_data = response.data.total;
        });
    };
    init.readDataByPagination = function (page_number, page_row, sort_by, filter) {
        return $http({
            method: 'GET',
            url: '/admin/medicine/list?page=' + page_number + '&row=' + page_row + '&sort=' + sort_by + '&filter=' + filter
        }).then(function (response) {
            init.result_data = response.data.data;
            init.total_data = response.data.total;
            console.log("Data produk", init.result_data);
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
            url: '/admin/medicine/read/id/' + id,
        }).then(function(response) {
            init.result_data = response.data;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by autocomplete
    |--------------------------------------------------------------------------
    */
    init.readDataByAutocomplete = function(type) {
        return $http({
            method: 'GET',
            url: '/admin/medicine/read/autocomplete?type=' + type,
        }).then(function(response) {
            init.result_data = response.data;
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
            url: '/admin/medicine/create',
            data: {
                medicine: data
            },
        }).then(function(response) {
            init.result_process = response.data.status;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Update data
    |--------------------------------------------------------------------------
    */
    init.updateData = function(data) {
        console.log("Update an", data);
        return $http({
            method: 'POST',
            url: '/admin/medicine/update',
            data: {
                medicine: data
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
        console.log(input);
        return $http({
            method: 'POST',
            url: '/admin/medicine/update/status',
            data: {
                medicine: input
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
            url: '/admin/medicine/delete',
            data: {
                medicine: data
            }
        }).then(function(response) {
            init.result_process = response.data;
        });
    }

    return init;
})
