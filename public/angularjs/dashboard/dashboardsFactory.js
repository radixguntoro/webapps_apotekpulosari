var app = angular.module('factoryDashboards', []);
app.factory('dashboardsFactory', function ($http, toastr) {
    /*
    |--------------------------------------------------------------------------
    | Deklarasi variable
    |--------------------------------------------------------------------------
    */
    var init = {};
    init.data = {};
    init.result_data = [];
    init.total_data = [];
    init.result_process = '';

    init.getCountDataMember = function () {
        return $http({
            method: 'GET',
            url: 'dashboard/getCountDataMember'
        }).then(function (response) {
            init.result_data = response.data;
        }, function (response) {});
    };

    init.getCountDataDriver = function () {
        return $http({
            method: 'GET',
            url: 'dashboard/getCountDataDriver'
        }).then(function (response) {
            init.result_data = response.data;
        }, function (response) {});
    };

    init.getCountDataTrBooking = function () {
        return $http({
            method: 'GET',
            url: 'dashboard/getCountDataTrBooking'
        }).then(function (response) {
            init.result_data = response.data;
        }, function (response) {});
    };

    init.getCountDataExRoadCost = function () {
        return $http({
            method: 'GET',
            url: 'dashboard/getCountDataExRoadCost'
        }).then(function (response) {
            init.result_data = response.data;
        }, function (response) {});
    };

    return init;
})
