var app = angular.module('trSalesLabCtrl', ['firebase', 'factoryTrSalesLab', 'factoryMedicines']);

app.controller('trSalesLabController', function ($rootScope, $scope, $state, $stateParams, toastr,  trSalesLabFactory, medicinesFactory) {
    $rootScope.$state = $state;
    /*
    |--------------------------------------------------------------------------
    | Declare
    |--------------------------------------------------------------------------
    */
    $scope.is_verification = false;
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

    $scope.data_cart = [];
    $scope.resetVariable = function () {
        $scope.trSalesLab = {
            total: 0,
            discount: 0,
            grand_total: 0,
            payment: 0,
            balance: 0,
            patient: '',
            age: '',
            glucosa_fasting: '',
            glucosa_2hours_pp: '',
            glucosa_random: '',
            uric_acid: '',
            cholesterol: '',
            blood_pressure: '',
            details: [],
        };
        $scope.data_cart = [];
    }

    $scope.resetVariable();
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    if ($state.current.name == "admin-trSalesLab-recap") {
        $scope.getResultsPage = function (page_number) {
            $scope.loading = true;
            if (!$.isEmptyObject($scope.library_temp)) {
                trSalesLabFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by)
                    .then(function () {
                        $scope.data = trSalesLabFactory.result_data;
                        $scope.total_data = trSalesLabFactory.total_data;
                        $scope.loading = false;
                    });
            } else {
                trSalesLabFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by)
                    .then(function () {
                        $scope.data = trSalesLabFactory.result_data;
                        $scope.total_data = trSalesLabFactory.total_data;
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
    }
    /*
    |--------------------------------------------------------------------------
    | Set block text
    |--------------------------------------------------------------------------
    */
    $scope.setBlockText = function(elm, e) {
        angular.element(e.target).select();
    }
    /*
    |--------------------------------------------------------------------------
    | Set verification
    |--------------------------------------------------------------------------
    */
    $scope.setVerification = () => {
        $scope.is_verification = !$scope.is_verification ? true: false;
    }
    /*
    |--------------------------------------------------------------------------
    | Read data medicine by autocomplete
    |--------------------------------------------------------------------------
    */
    $scope.readMedicineByAutoComplete = function(typo, response) {
        medicinesFactory.readDataByAutocomplete(typo.term).then(function() {
            $scope.data_medicine = medicinesFactory.result_data.medicine.data;
            if ($scope.data_medicine.length > 0) {
                response($scope.data_medicine);
            }
        });
    };
    if ($state.current.name == "admin-trSalesLab-create") {
        /*
        |--------------------------------------------------------------------------
        | Calculation
        |--------------------------------------------------------------------------
        */
        $scope.calcTotal = function() {
            let total = 0;
            angular.forEach($scope.data_cart, function(val, key) {
                val.subtotal = parseInt(val.price) * parseInt(val.qty);
                total += (parseInt(val.price) * parseInt(val.qty));
            });
            $scope.trSalesLab.total = total;
            $scope.trSalesLab.grand_total = total - $scope.trSalesLab.discount;
            $scope.trSalesLab.balance = $scope.trSalesLab.payment > (total - $scope.trSalesLab.discount) ? $scope.trSalesLab.payment - (total - $scope.trSalesLab.discount) : 0;
        }
        $scope.calcDiscount = (elm) => {
            let trSalesLab = elm.$parent.$parent.trSalesLab;
            if (trSalesLab.discount == null || trSalesLab.discount == '' || (trSalesLab.discount > trSalesLab.total)) {
                $scope.trSalesLab.discount = 0;
            }
            $scope.calcTotal();
        }
        $scope.calcPayment = (elm) => {
            console.log(elm);
            let trSalesLab = elm.$parent.$parent.trSalesLab;
            if(trSalesLab.payment == null || trSalesLab.payment == '') {
                $scope.trSalesLab.payment = 0;
            }
            $scope.calcTotal();
        }
        /*
        |--------------------------------------------------------------------------
        | Add to cart
        |--------------------------------------------------------------------------
        */
        $scope.addCart = function(data) {
            medicinesFactory.readDataById(data.id)
            .then(function () {
                let medicine = medicinesFactory.result_data.medicine;
                let cart_list = {
                    medicines_items_id: medicine.id,
                    name: medicine.name,
                    price: medicine.tabletPriceSell,
                    qty: 1,
                    subtotal: medicine.tabletPriceSell,
                    discount: 0,
                    unit: medicine.tabletUnit,
                }
                $scope.data_cart.push(cart_list);
                $scope.calcTotal();
                console.log(cart_list);
            });
        }
        /*
        |--------------------------------------------------------------------------
        | Remove cart
        |--------------------------------------------------------------------------
        */
        $scope.removeCart = function(elm) {
            $scope.data_cart.splice(elm.$index, 1);
            $scope.calcTotal();
        }
        /*
        |--------------------------------------------------------------------------
        | Create data
        |--------------------------------------------------------------------------
        */
        $scope.createData = function() {
            $scope.trSalesLab.details = $scope.data_cart;
            console.log($scope.trSalesLab);
            trSalesLabFactory.createData($scope.trSalesLab).then(function() {
                var process = trSalesLabFactory.result_process;
                if (process == 0) {
                    swal(opt_failed, function () {
                        $scope.loading = false;
                    });
                    return;
                } else {
                    $scope.loading = false;
                    swal(opt_saved, function () {
                        $state.reload();
                    });
                    $scope.loading = false;
                    fire_trSalesLab.total = 0;
                    fire_trSalesLab.discount= 0;
                    fire_trSalesLab.grand_total = 0;
                    fire_trSalesLab.payment = 0;
                    fire_trSalesLab.balance = 0;
                    fire_trSalesLab.$save(fire_trSalesLab);

                    $scope.resetVariable();
                }
            });
        }
    }

    $scope.readDataById = function(id) {
        trSalesLabFactory.readDataById(id)
            .then(function () {
                let tr_sales_lab = trSalesLabFactory.result_data.tr_sales_lab;
                console.log(tr_sales_lab);
                $scope.tr_sales_lab = {
                    balance: tr_sales_lab.balance,
                    date: tr_sales_lab.date,
                    time: tr_sales_lab.time,
                    discount: tr_sales_lab.discount,
                    grandTotal: tr_sales_lab.grandTotal,
                    id: tr_sales_lab.id,
                    patient: tr_sales_lab.patient,
                    age: tr_sales_lab.age,
                    glucosa_fasting: tr_sales_lab.glucosa_fasting,
                    glucosa_2hours_pp: tr_sales_lab.glucosa_2hours_pp,
                    glucosa_random: tr_sales_lab.glucosa_random,
                    uric_acid: tr_sales_lab.uric_acid,
                    cholesterol: tr_sales_lab.cholesterol,
                    blood_pressure: tr_sales_lab.blood_pressure,
                    payment: tr_sales_lab.payment,
                    total: tr_sales_lab.total,
                    userName: tr_sales_lab.userName,
                    details: tr_sales_lab.details,
                };
            });
    }

    if ($state.current.name == "admin-trSalesLab-print") {
        $scope.readDataById($stateParams.id);
    }
})
