var app = angular.module('trSalesMixCtrl', ['firebase', 'factoryTrSalesMix', 'factoryMedicines']);

app.controller('trSalesMixController', function ($rootScope, $scope, $state, $stateParams, toastr, $firebaseArray, $firebaseObject, trSalesMixFactory, medicinesFactory) {
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

    $scope.user_roles_id = $scope.user_login.roles_id;
    let username = $scope.user_login.username;

    $scope.data_cart = [];
    $scope.resetVariable = function () {
        $scope.trSalesMix = {
            total: 0,
            discount: 0,
            grand_total: 0,
            payment: 0,
            fee_pharmacist: 0,
            balance: 0,
            patient: '',
            weight: '',
            age: '',
            medicineMix: [],
        };
        $scope.trSalesMixForm = {
            medicineMixName: '',
            total: 0,
            tuslah: 0,
            grand_total: 0,
        };
        $scope.data_cart = [];
    }

    $scope.resetVariable();
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    if ($state.current.name == "admin-trSalesMix-recap") {
        $scope.getResultsPage = function (page_number) {
            $scope.loading = true;
            if (!$.isEmptyObject($scope.library_temp)) {
                trSalesMixFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by)
                    .then(function () {
                        $scope.data = trSalesMixFactory.result_data;
                        $scope.total_data = trSalesMixFactory.total_data;
                        $scope.loading = false;
                    });
            } else {
                trSalesMixFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by)
                    .then(function () {
                        $scope.data = trSalesMixFactory.result_data;
                        $scope.total_data = trSalesMixFactory.total_data;
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
    if ($state.current.name == "admin-trSalesMix-create") {
        /*
        |--------------------------------------------------------------------------
        | Calculation
        |--------------------------------------------------------------------------
        */
        $scope.calcTotalForm = function() {
            let total = 0;
            angular.forEach($scope.data_cart, function(val, key) {
                val.subtotal = parseInt(val.price) * parseInt(val.qty);
                total += (parseInt(val.price) * parseInt(val.qty));
            });
            $scope.trSalesMixForm.total = total;
            $scope.trSalesMixForm.grand_total = total + $scope.trSalesMixForm.tuslah;
        }
        $scope.calcTuslah = (elm) => {
            console.log(elm);
            let trSalesMixForm = elm.$parent.$parent.trSalesMixForm;
            if (trSalesMixForm.tuslah == null || trSalesMixForm.tuslah == '') {
                $scope.trSalesMix.tuslah = 0;
            }
            $scope.calcTotalForm();
        }
        $scope.calcTotal = function() {
            let total = 0;
            angular.forEach($scope.trSalesMix.medicineMix, function(val, key) {
                total += parseInt(val.subtotal);
            });
            $scope.trSalesMix.total = total;
            $scope.trSalesMix.grand_total = (total - $scope.trSalesMix.discount) + $scope.trSalesMix.fee_pharmacist;
            $scope.trSalesMix.balance = $scope.trSalesMix.payment > $scope.trSalesMix.grand_total ? $scope.trSalesMix.payment - $scope.trSalesMix.grand_total : 0;
        }
        $scope.calcDiscount = (elm) => {
            let trSalesMix = elm.$parent.$parent.trSalesMix;
            if (trSalesMix.discount == null || trSalesMix.discount == '' || (trSalesMix.discount > trSalesMix.total)) {
                $scope.trSalesMix.discount = 0;
            }
            $scope.calcTotal();
        }
        $scope.calcFeePharmacist = (elm) => {
            let trSalesMix = elm.$parent.$parent.trSalesMix;
            if (trSalesMix.fee_pharmacist == null || trSalesMix.fee_pharmacist == '' || (trSalesMix.fee_pharmacist > trSalesMix.total)) {
                $scope.trSalesMix.fee_pharmacist = 0;
            }
            $scope.calcTotal();
        }
        $scope.calcPayment = (elm) => {
            let trSalesMix = elm.$parent.$parent.trSalesMix;
            if(trSalesMix.payment == null || trSalesMix.payment == '') {
                $scope.trSalesMix.payment = 0;
            }
            $scope.calcTotal();
        }
        /*
        |--------------------------------------------------------------------------
        | Add mix
        |--------------------------------------------------------------------------
        */
        $scope.addMix = function(elm, data) {
            $scope.trSalesMix.medicineMix.push({
                medicineMixName: data.medicineMixName,
                details: $scope.data_cart,
                tuslah: data.tuslah,
                price: data.total,
                subtotal: data.grand_total,
            });
            $scope.trSalesMixForm = {
                medicineMixName: '',
                total: 0,
                tuslah: 0,
                grand_total: 0,
            };
            $scope.data_cart = [];
            $scope.calcTotal();
            console.log('Komposisi', $scope.trSalesMix.medicineMix);
        }
        /*
        |--------------------------------------------------------------------------
        | Remove mix
        |--------------------------------------------------------------------------
        */
        $scope.removeMix = function(elm) {
            $scope.trSalesMix.medicineMix.splice(elm.$index, 1);
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
                    price: parseInt(medicine.tabletPriceSell) + (parseInt(medicine.tabletPriceSell) * 0.1),
                    qty: 1,
                    subtotal: parseInt(medicine.tabletPriceSell) + (parseInt(medicine.tabletPriceSell) * 0.1),
                    discount: 0,
                    unit: medicine.tabletUnit,
                }
                $scope.data_cart.push(cart_list);
                $scope.calcTotalForm();
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
            trSalesMixFactory.createData($scope.trSalesMix).then(function() {
                var process = trSalesMixFactory.result_process;
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
                    $scope.resetVariable();
                }
            });
        }
    }

    $scope.readDataById = function(id) {
        trSalesMixFactory.readDataById(id)
            .then(function () {
                $scope.tr_sales_mix = trSalesMixFactory.result_data.tr_sales_mix;
            });
    }

    if ($state.current.name == "admin-trSalesMix-print") {
        $scope.readDataById($stateParams.id);
    }
})
