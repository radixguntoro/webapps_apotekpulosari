var app = angular.module('suppliersCtrl', ['factorySuppliers']);

app.controller('suppliersController', function ($rootScope, $scope, $stateParams, $state, toastr, suppliersFactory) {
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
        $scope.supplier = {
            name: '',
            phone: '',
            status: 'active',
        };
    };
    $scope.resetVariable();
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    $scope.getResultsPage = function (page_number) {
        $scope.loading = true;
        if (!$.isEmptyObject($scope.library_temp)) {
            suppliersFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by)
                .then(function () {
                    $scope.data = suppliersFactory.result_data;
                    $scope.total_data = suppliersFactory.total_data;
                    $scope.loading = false;
                });
        } else {
            suppliersFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by)
                .then(function () {
                    $scope.data = suppliersFactory.result_data;
                    $scope.total_data = suppliersFactory.total_data;
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
        $scope.supplier = {
            name: $scope.supplier.name,
            phones: [
                { 
                    number: $scope.supplier.phone,
                    status: 'create'
                }
            ],
            status: $scope.supplier.status,
        };
        suppliersFactory.createData($scope.supplier).then(function () {
            var process = suppliersFactory.result_process;
            if (process == 0) {
                swal(opt_failed, function () {
                    $scope.loading = false;
                });
                return;
            } else {
                $scope.loading = false;
                swal(opt_saved, function () {
                    $state.go("admin-supplier-list");
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
    if ($state.current.name == "admin-supplier-edit") {
        suppliersFactory.readDataById($stateParams.id)
            .then(function () {
                let supplier = suppliersFactory.result_data.supplier;
                $scope.supplier = {
                    name: supplier.name,
                    id: supplier.id,
                    persons_id: supplier.id,
                    phone: supplier.phone,
                    phoneId: supplier.phoneId,
                    status: supplier.status,
                };
            });

        $scope.updateData = function () {
            $scope.loading = true;
            $scope.supplier = {
                name: $scope.supplier.name,
                id: $scope.supplier.id,
                persons_id: $scope.supplier.id,
                phones: [
                    { 
                        id: $scope.supplier.phoneId,
                        number: $scope.supplier.phone,
                        status: 'edit'
                    }
                ],
                status: $scope.supplier.status,
            };
            suppliersFactory.updateData($scope.supplier).then(function () {
                var process = suppliersFactory.result_process;
                if (process == 0) {
                    swal(opt_failed, function () {
                        $scope.loading = false;
                    });
                    return;
                } else {
                    $scope.loading = false;
                    swal(opt_saved, function () {
                        $state.go("admin-supplier-list");
                    });
                }
                $scope.loading = false;
            });
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Update status
    |--------------------------------------------------------------------------
    */
    $scope.set_status = 'inactive';
    $scope.updateDataStatus = function (elm) {
        console.log(elm);
        $scope.status = elm.supplier.status == 'active' ? 'inactive' : elm.supplier.status == 'inactive' ? 'active' : '';
        suppliersFactory.updateDataStatus({
            id: elm.supplier.id,
            status: $scope.status
        }).then(function () {
            var process = suppliersFactory.result_process;
            if (process == 0) {
                toastr.error('Status data gagal diubah.', 'Gagal!');
                return;
            } else {
                toastr.success('Status data telah diubah.', 'Sukses!');
                $state.reload();
            }
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data 
    |--------------------------------------------------------------------------
    */
    $scope.deleteData = function (data) {
        $scope.loading = true;
        suppliersFactory.deleteData(data).then(function () {
            var process = suppliersFactory.result_process;
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
