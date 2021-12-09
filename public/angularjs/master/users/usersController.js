var app = angular.module('usersCtrl', ['factoryUsers']);

app.controller('usersController', function ($rootScope, $scope, $stateParams, $state, toastr, usersFactory) {
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
        $scope.user = {
            name: '',
            email: '',
            phone: '',
            role: {
                selected: {
                    id: 0,
                    name: '-- Pilih Hak Akses --'
                }
            },
            username: '',
            password: '',
            status: 'active',
        };

        $scope.roles = [
            {
                "id": 99,
                "name": "Owner"
            },
            {
                "id": 1,
                "name": "Kasir"
            }
        ];
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
            usersFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by)
                .then(function () {
                    $scope.data = usersFactory.result_data;
                    $scope.total_data = usersFactory.total_data;
                    $scope.loading = false;
                });
        } else {
            usersFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by)
                .then(function () {
                    $scope.data = usersFactory.result_data;
                    $scope.total_data = usersFactory.total_data;
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
        $scope.user = {
            name: $scope.user.name,
            email: $scope.user.email,
            phones: [
                { 
                    number: $scope.user.phone,
                    status: 'create'
                }
            ],
            roles_id: $scope.user.role.selected.id,
            username: $scope.user.username,
            password: $scope.user.password,
            status: $scope.user.status,
        };
        usersFactory.createData($scope.user).then(function () {
            var process = usersFactory.result_process;
            if (process == 0) {
                swal(opt_failed, function () {
                    $scope.loading = false;
                });
                return;
            } else {
                $scope.loading = false;
                swal(opt_saved, function () {
                    $state.go("admin-user-list");
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
    if ($state.current.name == "admin-user-edit") {
        usersFactory.readDataById($stateParams.id)
            .then(function () {
                let user = usersFactory.result_data.user;
                console.log(user);
                $scope.user = {
                    name: user.name,
                    id: user.id,
                    persons_id: user.id,
                    email: user.email,
                    phone: user.phone,
                    phoneId: user.phoneId,
                    role: {
                        selected: {
                            id: user.rolesId,
                            name: user.rolesName
                        }
                    },
                    roles_id: user.rolesId,
                    username: user.username,
                    password: '',
                    status: user.status,
                };
            });

        $scope.updateData = function () {
            $scope.loading = true;
            $scope.user = {
                name: $scope.user.name,
                id: $scope.user.id,
                persons_id: $scope.user.id,
                email: $scope.user.email,
                phones: [
                    { 
                        id: $scope.user.phoneId,
                        number: $scope.user.phone,
                        status: 'edit'
                    }
                ],
                roles_id: $scope.user.role.selected.id,
                username: $scope.user.username,
                password: $scope.user.password,
                status: $scope.user.status,
            };
            usersFactory.updateData($scope.user).then(function () {
                var process = usersFactory.result_process;
                if (process == 0) {
                    swal(opt_failed, function () {
                        $scope.loading = false;
                    });
                    return;
                } else {
                    $scope.loading = false;
                    swal(opt_saved, function () {
                        $state.go("admin-user-list");
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
        $scope.status = elm.user.status == 'active' ? 'inactive' : elm.user.status == 'inactive' ? 'active' : '';
        usersFactory.updateDataStatus({
            id: elm.user.id,
            status: $scope.status
        }).then(function () {
            var process = usersFactory.result_process;
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
        usersFactory.deleteData(data).then(function () {
            var process = usersFactory.result_process;
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
