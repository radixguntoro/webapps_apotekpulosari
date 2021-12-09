var app = angular.module('trSalesRegularCtrl', ['firebase', 'factoryTrSalesRegular', 'factoryMedicines']);

app.controller('trSalesRegularController', function ($rootScope, $scope, $state, $stateParams, toastr, $firebaseArray, $firebaseObject, trSalesRegularFactory, medicinesFactory) {
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
        $scope.trSalesRegular = {
            total: 0,
            discount: 0,
            grand_total: 0,
            payment: 0,
            balance: 0,
        };
    }

    $scope.resetVariable();
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    if ($state.current.name == "admin-trSalesRegular-recap") {
        $scope.getResultsPage = function (page_number) {
            $scope.loading = true;
            if (!$.isEmptyObject($scope.library_temp)) {
                trSalesRegularFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by)
                    .then(function () {
                        $scope.data = trSalesRegularFactory.result_data;
                        $scope.total_data = trSalesRegularFactory.total_data;
                        $scope.loading = false;
                    });
            } else {
                trSalesRegularFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by)
                    .then(function () {
                        $scope.data = trSalesRegularFactory.result_data;
                        $scope.total_data = trSalesRegularFactory.total_data;
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
    if ($state.current.name == "admin-trSalesRegular-create") {
        let ref_trSalesRegular_details = $scope.user_roles_id == '99' ? firebase.database().ref().child("trSalesRegular") : firebase.database().ref().child("trSalesRegular").child(username).child("details");
        let ref_trSalesRegular = $scope.user_roles_id == '99' ? firebase.database().ref().child("trSalesRegular") : firebase.database().ref().child("trSalesRegular").child(username);
        let fire_trSalesRegularDetails = $firebaseArray(ref_trSalesRegular_details);
        let fire_trSalesRegular = $firebaseObject(ref_trSalesRegular);
        /*
        |--------------------------------------------------------------------------
        | Calculation
        |--------------------------------------------------------------------------
        */
        $scope.calcTotal = function(username) {
            let total = 0;
            let data_cart = [];

            if ($scope.user_roles_id == '1') {
                angular.forEach($scope.data_cart, function(val, key) {
                    total += (parseInt(val.price) * parseInt(val.qty));
                });
            } else {
                angular.forEach($scope.data_cart, function(val_p, key) {
                    if (val_p.$id == username) {
                        angular.forEach(val_p.items, function(val, key) {
                            total += (parseInt(val.price) * parseInt(val.qty));
                            data_cart = val_p.items;
                        })
                    }
                });
            }

            console.log(username);
            console.log(total);
            console.log($scope.data_cart);
            console.log(fire_trSalesRegular);
            console.log(data_cart);

            if ($scope.data_cart.length > 0) {
                if ($scope.user_roles_id == '1') {
                    fire_trSalesRegular.total = parseInt(total);
                    fire_trSalesRegular.discount = parseInt(fire_trSalesRegular.discount) > 0 ? parseInt(fire_trSalesRegular.discount) : 0;
                    fire_trSalesRegular.grand_total = parseInt(total) - parseInt(fire_trSalesRegular.discount);
                    fire_trSalesRegular.payment = parseInt(fire_trSalesRegular.payment) > 0 ? parseInt(fire_trSalesRegular.payment) : 0;
                    fire_trSalesRegular.balance = parseInt(fire_trSalesRegular.payment) > 0 ? parseInt(fire_trSalesRegular.payment) - (parseInt(total) - parseInt(fire_trSalesRegular.discount)) : 0;
                    fire_trSalesRegular.details = $scope.data_cart;
                    fire_trSalesRegular.$save(fire_trSalesRegular);

                    $scope.trSalesRegular.total = fire_trSalesRegular.total;
                    $scope.trSalesRegular.grand_total = fire_trSalesRegular.grand_total;
                    $scope.trSalesRegular.payment = fire_trSalesRegular.payment;
                    $scope.trSalesRegular.balance = fire_trSalesRegular.balance;
                } else {
                    console.log('Here', username);
                    let ref_trSalesRegular = firebase.database().ref().child("trSalesRegular").child(username);
                    let fire_trSalesRegular = $firebaseObject(ref_trSalesRegular);
                    fire_trSalesRegular.total = parseInt(total);
                    fire_trSalesRegular.discount = 0;
                    fire_trSalesRegular.grand_total = parseInt(total) - parseInt(fire_trSalesRegular.discount);
                    fire_trSalesRegular.payment = 0;
                    fire_trSalesRegular.balance = parseInt(fire_trSalesRegular.payment) > 0 ? parseInt(fire_trSalesRegular.payment) - (parseInt(total) - parseInt(fire_trSalesRegular.discount)) : 0;
                    fire_trSalesRegular.details = data_cart;
                    fire_trSalesRegular.$save(fire_trSalesRegular);
                }
            } else {
                if ($scope.user_roles_id == '1') {
                    fire_trSalesRegular.total = 0;
                    fire_trSalesRegular.discount= 0;
                    fire_trSalesRegular.grand_total = 0;
                    fire_trSalesRegular.payment = 0;
                    fire_trSalesRegular.balance = 0;
                    fire_trSalesRegular.$save(fire_trSalesRegular);

                    $scope.resetVariable();
                }
            }
        }

        $scope.calcBalance = function(data) {
            fire_trSalesRegular.balance = 0;
            if (data.payment == '' || data.payment == null) {
                data.payment = 0;
                fire_trSalesRegular.balance = 0;
                fire_trSalesRegular.$save(fire_trSalesRegular);
            } else if (data.payment > data.grand_total) {
                fire_trSalesRegular.balance = data.payment - data.grand_total;
                fire_trSalesRegular.$save(fire_trSalesRegular);
                $scope.calcTotal(username);
            }
        }

        $scope.calcDiscount = function(data) {
            if (data.discount == '' || data.discount == null) {
                data.discount = 0;
                fire_trSalesRegular.$save(fire_trSalesRegular);
                $scope.calcTotal(username);
            } else if (data.discount > 0) {
                $scope.calcTotal(username);
            }
        }
        /*
        |--------------------------------------------------------------------------
        | Add to cart
        |--------------------------------------------------------------------------
        */
        $scope.addCart = function(data) {
            let cart_list = {
                medicines_items_id: data.id,
                name: data.name,
                price: data.tabletPriceSell,
                qty: 1,
                subtotal: data.tabletPriceSell,
                discount: 0,
                unit: data.tabletUnit,
            }

            fire_trSalesRegularDetails.$add(cart_list).then(function() {
                $scope.readCart();
            });
        }
        /*
        |--------------------------------------------------------------------------
        | Update cart
        |--------------------------------------------------------------------------
        */
        $scope.updateCartPrice = function(data, username) {
            fire_trSalesRegularDetails.$save(data).then(function() {
                $scope.readCart(username);
            });
        }
        
        $scope.updateCartQty = function(data) {
            data.subtotal = (parseInt(data.price) * parseInt(data.qty));
            fire_trSalesRegularDetails.$save(data).then(function() {
                $scope.readCart(username='');
            });
        }
        /*
        |--------------------------------------------------------------------------
        | Remove cart
        |--------------------------------------------------------------------------
        */
        $scope.removeCart = function(data) {
            fire_trSalesRegularDetails.$remove(data).then(function() {
                $scope.readCart(username='');
            });
        }
        /*
        |--------------------------------------------------------------------------
        | Read cart
        |--------------------------------------------------------------------------
        */
        $scope.readCart = function(username='') {
            $scope.data_cart = fire_trSalesRegularDetails;

            fire_trSalesRegular.$bindTo($scope, "trSalesRegular");
            $scope.calcTotal(username);
        }

        $scope.readCart();
        /*
        |--------------------------------------------------------------------------
        | Create data
        |--------------------------------------------------------------------------
        */
        $scope.createData = function() {
            trSalesRegularFactory.createData(fire_trSalesRegular).then(function() {
                var process = trSalesRegularFactory.result_process;
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
                    fire_trSalesRegular.total = 0;
                    fire_trSalesRegular.discount= 0;
                    fire_trSalesRegular.grand_total = 0;
                    fire_trSalesRegular.payment = 0;
                    fire_trSalesRegular.balance = 0;
                    fire_trSalesRegular.$save(fire_trSalesRegular);

                    $scope.resetVariable();
                }
            });
        }
    }

    $scope.readDataById = function(id) {
        trSalesRegularFactory.readDataById(id)
            .then(function () {
                let tr_sales_regular = trSalesRegularFactory.result_data.tr_sales_regular;
                console.log(tr_sales_regular);
                $scope.tr_sales_regular = {
                    balance: tr_sales_regular.balance,
                    createdAt: tr_sales_regular.createdAt,
                    timeAt: tr_sales_regular.timeAt,
                    discount: tr_sales_regular.discount,
                    grandTotal: tr_sales_regular.grandTotal,
                    id: tr_sales_regular.id,
                    payment: tr_sales_regular.payment,
                    total: tr_sales_regular.total,
                    userName: tr_sales_regular.userName,
                    details: tr_sales_regular.details,
                };
            });
    }

    if ($state.current.name == "admin-trSalesRegular-print") {
        $scope.readDataById($stateParams.id);
    }
})
