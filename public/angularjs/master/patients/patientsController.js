var app = angular.module('patientsCtrl', ['factoryPatients']);

app.controller('patientsController', function ($rootScope, $scope, $stateParams, $state, toastr, patientsFactory) {
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
        $scope.patient = {
            name: '',
            age: '',
            address: '',
            city: '',
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
            patientsFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by)
                .then(function () {
                    $scope.data = patientsFactory.result_data;
                    $scope.total_data = patientsFactory.total_data;
                    $scope.loading = false;
                });
        } else {
            patientsFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by)
                .then(function () {
                    $scope.data = patientsFactory.result_data;
                    $scope.total_data = patientsFactory.total_data;
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
        $scope.patient = {
            name: $scope.patient.name,
            age: $scope.patient.age,
            address: $scope.patient.address,
            city: $scope.patient.city,
            phones: [
                { 
                    number: $scope.patient.phone,
                    status: 'create'
                }
            ],
            status: $scope.patient.status,
        };
        patientsFactory.createData($scope.patient).then(function () {
            var process = patientsFactory.result_process;
            if (process == 0) {
                swal(opt_failed, function () {
                    $scope.loading = false;
                });
                return;
            } else {
                $scope.loading = false;
                swal(opt_saved, function () {
                    $state.go("admin-patient-list");
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
    if ($state.current.name == "admin-patient-edit") {
        patientsFactory.readDataById($stateParams.id)
            .then(function () {
                let patient = patientsFactory.result_data.patient;
                $scope.patient = {
                    name: patient.name,
                    id: patient.id,
                    persons_id: patient.id,
                    age: patient.age,
                    address: patient.address,
                    city: patient.city,
                    phone: patient.phone,
                    phoneId: patient.phoneId,
                    status: patient.status,
                };
            });

        $scope.updateData = function () {
            $scope.loading = true;
            $scope.patient = {
                name: $scope.patient.name,
                age: $scope.patient.age,
                address: $scope.patient.address,
                city: $scope.patient.city,
                id: $scope.patient.id,
                persons_id: $scope.patient.id,
                phones: [
                    { 
                        id: $scope.patient.phoneId,
                        number: $scope.patient.phone,
                        status: 'edit'
                    }
                ],
                status: $scope.patient.status,
            };
            patientsFactory.updateData($scope.patient).then(function () {
                var process = patientsFactory.result_process;
                if (process == 0) {
                    swal(opt_failed, function () {
                        $scope.loading = false;
                    });
                    return;
                } else {
                    $scope.loading = false;
                    swal(opt_saved, function () {
                        $state.go("admin-patient-list");
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
        $scope.status = elm.patient.status == 'active' ? 'inactive' : elm.patient.status == 'inactive' ? 'active' : '';
        patientsFactory.updateDataStatus({
            id: elm.patient.id,
            status: $scope.status
        }).then(function () {
            var process = patientsFactory.result_process;
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
        patientsFactory.deleteData(data).then(function () {
            var process = patientsFactory.result_process;
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
