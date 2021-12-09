var app = angular.module('dashboardsCtrl', ['chart.js', 'factoryDashboards']);

app.controller('dashboardsController', function ($rootScope, $scope, $timeout, $stateParams, $filter, $state, toastr, dashboardsFactory) {
    $rootScope.$state = $state;
    /*
    |--------------------------------------------------------------------------
    | Deklarasi Variable
    |--------------------------------------------------------------------------
    */
    $scope.dashboard = {
        count_member: 0,
        count_driver: 0,
        count_trbooking: 0,
        count_exroadcost: 0,
    }

    /*
    |--------------------------------------------------------------------------
    | Fungsi menampilkan data per halaman dan pencarian daftar data
    |--------------------------------------------------------------------------
    */
    $scope.getCountDataMember = function () {
        $scope.loading = true;
        dashboardsFactory.getCountDataMember()
            .then(function () {
                $scope.dashboard.count_member = dashboardsFactory.result_data;
                $timeout(function () {
                    $scope.loading = false;
                }, 1000)
            });
    }

    $scope.getCountDataDriver = function () {
        $scope.loading = true;
        dashboardsFactory.getCountDataDriver()
            .then(function () {
                $scope.dashboard.count_driver = dashboardsFactory.result_data;
                $timeout(function () {
                    $scope.loading = false;
                }, 1000)
            });
    }

    $scope.getCountDataTrBooking = function () {
        $scope.loading = true;
        dashboardsFactory.getCountDataTrBooking()
            .then(function () {
                $scope.dashboard.count_trbooking = dashboardsFactory.result_data.total;
                $timeout(function () {
                    $scope.loading = false;
                }, 1000)
            });
    }

    $scope.getCountDataExRoadCost = function () {
        $scope.loading = true;
        dashboardsFactory.getCountDataExRoadCost()
            .then(function () {
                $scope.dashboard.count_exroadcost = dashboardsFactory.result_data.total;
                $timeout(function () {
                    $scope.loading = false;
                }, 1000)
            });
    }

    $scope.loadData = function () {
        $scope.getCountDataTrBooking();
        $scope.getCountDataExRoadCost();
        $scope.getCountDataMember();
        $scope.getCountDataDriver();
    }

    $scope.loadData();

    $scope.options = {
        tooltips: {
            mode: 'label',
            callbacks: {
                label: function (tooltipItem, data) {
                    return data.datasets[tooltipItem.datasetIndex].label + ": " + tooltipItem.yLabel.toString().split(/(?=(?:...)*$)/).join('.');
                }
            }
        },
        scales: {
            yAxes: [{
                id: 'y-axis-1',
                type: 'bar',
                display: true,
                position: 'left',
                ticks: {
                    userCallback: function (value, index, values) {
                        // Convert the number to a string and splite the string every 3 charaters from the end
                        value = value.toString();
                        value = value.split(/(?=(?:...)*$)/);

                        // Convert the array to a string and format the output
                        value = value.join('.');
                        return '' + value;
                    }
                }
            }]
        }
    };

    $timeout(function () {
        $scope.labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        $scope.series = ['Series A'];

        $scope.bgcolor_trbo = [{
            backgroundColor: '#fdd83557',
            borderColor: '#fdd835'
        }];

        $scope.data_trbo = [
            [20, 60, 70, 65, 56, 55, 65, 59, 80, 81, 75, 90]
        ];

        $scope.data_excost = [
            [20, 60, 78, 75, 65, 90, 95, 75, 78, 88, 90, 85]
        ];
    }, 1000);
});
