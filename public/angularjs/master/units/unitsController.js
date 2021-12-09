var app = angular.module('unitsCtrl', ['factoryUnits']);

app.controller('unitsController', function ($rootScope, $scope, $stateParams, $state, toastr, unitsFactory) {
    $rootScope.$state = $state;
    /*
    |--------------------------------------------------------------------------
    | Declare
    |--------------------------------------------------------------------------
    */
    $scope.data = [];
    $scope.library_temp = {};
    $scope.total_data_temp = {};
    $scope.total_data = 0;
    $scope.pagination = {
        current: 1
    };

    $scope.rows = [10, 25, 50, 100];
    $scope.page_row = $scope.rows[0];
    $scope.sort_by = '';
    let new_page = 1;

    $scope.resetVariable = function () {
        $scope.unit = {
            id: '',
            name: ''
        };
    }

    $scope.resetVariable();
    /*
    |--------------------------------------------------------------------------
    | Read all data
    |--------------------------------------------------------------------------
    */
    unitsFactory.readDataAll().then(function () {
        $scope.getunits = [];
        for (var i = 0; i < unitsFactory.result_data.length; i++) {
            $scope.getunits[i] = unitsFactory.result_data[i];
        }
    });
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    $scope.getResultsPage = function (page_number) {
        $scope.loading = true;
        if (!$.isEmptyObject($scope.library_temp)) {
            unitsFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by)
                .then(function () {
                    $scope.data = unitsFactory.result_data;
                    $scope.total_data = unitsFactory.total_data;
                    $scope.loading = false;
                });
        } else {
            unitsFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by)
                .then(function () {
                    $scope.data = unitsFactory.result_data;
                    $scope.total_data = unitsFactory.total_data;
                    $scope.loading = false;
                });
        }
    }

    $scope.getResultsPage(new_page, $scope.page_row, $scope.sort_by);

    $scope.pageChanged = function (new_page, page_row, sort_by) {
        $scope.getResultsPage(new_page, page_row, sort_by);
    };

    $scope.searchData = function () {
        if ($scope.search_text.length >= 3) {
            if ($.isEmptyObject($scope.library_temp)) {
                $scope.library_temp = $scope.data;
                $scope.total_data_temp = $scope.total_data;
                $scope.data = {};
            }
            $scope.getResultsPage(new_page);
        } else {
            if (!$.isEmptyObject($scope.library_temp)) {
                $scope.data = $scope.library_temp;
                $scope.total_data = $scope.total_data_temp;
                $scope.library_temp = {};
                $scope.getResultsPage(new_page);
            }
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Sorting data
    |--------------------------------------------------------------------------
    */
    $scope.sortColumn = "name";
    $scope.reverseSort = false;
    $scope.sortBy = function (column) {
        $scope.reverseSort = ($scope.sortColumn == column) ? !$scope.reverseSort : false;
        $scope.sortColumn = column;
        $scope.sort_by = $scope.reverseSort == false ? "desc" : "asc";
        $scope.getResultsPage(new_page, $scope.page_row, $scope.sort_by);
        console.log($scope.reverseSort);
    }

    $scope.getSortClass = function (column) {
        if ($scope.sortColumn == column) {
            return $scope.reverseSort ? 'sort-asc' : 'sort-desc'
        }
        return '';
    }
    /*
    |--------------------------------------------------------------------------
    | Create data
    |--------------------------------------------------------------------------
    */
    $scope.createData = function () {
        $scope.loading = true;
        unitsFactory.createData($scope.unit).then(function () {
            var process = unitsFactory.result_process;
            if (process == 0) {
                swal(opt_failed, function () {
                    $scope.loading = false;
                });
                return;
            } else {
                $scope.loading = false;
                swal(opt_saved, function () {
                    $state.go("admin-unit-list");
                });
            }
            $scope.loading = false;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Update data
    |--------------------------------------------------------------------------
    */
    if ($state.current.name == "admin-unit-edit") {
        unitsFactory.readDataById($stateParams.id)
            .then(function () {
                $scope.unit = unitsFactory.result_data[0];
            });

        $scope.updateData = function () {
            $scope.loading = true;
            unitsFactory.updateData($scope.unit).then(function () {
                var process = unitsFactory.result_process;
                if (process == 0) {
                    swal(opt_failed, function () {
                        $scope.loading = false;
                    });
                    return;
                } else {
                    $scope.loading = false;
                    swal(opt_saved, function () {
                        $state.go("admin-unit-list");
                    });
                }
                $scope.loading = false;
            });
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data 
    |--------------------------------------------------------------------------
    */
    $scope.deleteData = function (data) {
        $scope.loading = true;
        unitsFactory.deleteData(data).then(function () {
            var process = unitsFactory.result_process;
            if (process == 0) {
                swal(opt_failed, function () {
                    $scope.loading = false;
                });
                return;
            } else {
                $scope.loading = false;
                swal(opt_deleted, function () {
                    $state.reload();
                });
            }
            $scope.loading = false;
        });
    }
})
