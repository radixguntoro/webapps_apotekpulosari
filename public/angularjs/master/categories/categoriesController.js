var app = angular.module('categoriesCtrl', ['factoryCategories']);

app.controller('categoriesController', function ($rootScope, $scope, $stateParams, $state, toastr, categoriesFactory) {
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
        $scope.category = {
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
    categoriesFactory.readDataAll().then(function () {
        $scope.getCategories = [];
        for (var i = 0; i < categoriesFactory.result_data.length; i++) {
            $scope.getCategories[i] = categoriesFactory.result_data[i];
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
            categoriesFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by)
                .then(function () {
                    $scope.data = categoriesFactory.result_data;
                    $scope.total_data = categoriesFactory.total_data;
                    $scope.loading = false;
                });
        } else {
            categoriesFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by)
                .then(function () {
                    $scope.data = categoriesFactory.result_data;
                    $scope.total_data = categoriesFactory.total_data;
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
        if ($scope.category.name == '') {
            toastr.error('Nama kategori harus diisi.', 'Gagal!');
            return;
        } else {
            $scope.loading = true;
            categoriesFactory.createData($scope.category).then(function () {
                var process = categoriesFactory.result_process;
                if (process == 0) {
                    swal(opt_failed, function () {
                        $scope.loading = false;
                    });
                    return;
                } else {
                    $scope.loading = false;
                    swal(opt_saved, function () {
                        $state.go("admin-category-list");
                    });
                }
                $scope.loading = false;
            });
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Update data
    |--------------------------------------------------------------------------
    */
    if ($state.current.name == "admin-category-edit") {
        categoriesFactory.readDataById($stateParams.id)
            .then(function () {
                $scope.category = categoriesFactory.result_data[0];
            });

        $scope.updateData = function () {
            if ($scope.category.name == '') {
                toastr.error('Nama kategori harus diisi.', 'Gagal!');
                return;
            } else {
                $scope.loading = true;
                categoriesFactory.updateData($scope.category).then(function () {
                    var process = categoriesFactory.result_process;
                    if (process == 0) {
                        swal(opt_failed, function () {
                            $scope.loading = false;
                        });
                        return;
                    } else {
                        $scope.loading = false;
                        swal(opt_saved, function () {
                            $state.go("admin-category-list");
                        });
                    }
                    $scope.loading = false;
                });
            }
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data 
    |--------------------------------------------------------------------------
    */
    $scope.deleteData = function (data) {
        $scope.loading = true;
        categoriesFactory.deleteData(data).then(function () {
            var process = categoriesFactory.result_process;
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
