var app = angular.module('trSalesRecipeCtrl', ['firebase', 'factoryTrSalesRecipe', 'factoryMedicines']);

app.controller('trSalesRecipeController', function ($rootScope, $scope, $state, $stateParams, toastr, $firebaseArray, $firebaseObject, trSalesRecipeFactory, medicinesFactory) {
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
        $scope.trSalesRecipe = {
            total: 0,
            discount: 0,
            grand_total: 0,
            payment: 0,
            balance: 0,
            patient: '',
            address: '',
            doctor: '',
            date: '',
            medicineRecipe: [],
        };
        $scope.trSalesRecipeForm = {
            medicineRecipeName: '',
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
    if ($state.current.name == "admin-trSalesRecipe-recap") {
        $scope.getResultsPage = function (page_number) {
            $scope.loading = true;
            if (!$.isEmptyObject($scope.library_temp)) {
                trSalesRecipeFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by)
                    .then(function () {
                        $scope.data = trSalesRecipeFactory.result_data;
                        $scope.total_data = trSalesRecipeFactory.total_data;
                        $scope.loading = false;
                    });
            } else {
                trSalesRecipeFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by)
                    .then(function () {
                        $scope.data = trSalesRecipeFactory.result_data;
                        $scope.total_data = trSalesRecipeFactory.total_data;
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
    $scope.setBlockText = (elm, e) => {
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
    $scope.readMedicineByAutoComplete = (typo, response) => {
        medicinesFactory.readDataByAutocomplete(typo.term).then(() => {
            $scope.data_medicine = medicinesFactory.result_data.medicine.data;
            if ($scope.data_medicine.length > 0) {
                response($scope.data_medicine);
            }
        });
    };
    if ($state.current.name == "admin-trSalesRecipe-create") {
        /*
        |--------------------------------------------------------------------------
        | Calculation
        |--------------------------------------------------------------------------
        */
        $scope.calcTotalForm = () => {
            let total = 0;
            angular.forEach($scope.data_cart, (val, key) => {
                val.subtotal = parseInt(val.price) * parseInt(val.qty);
                total += (parseInt(val.price) * parseInt(val.qty));
            });
            $scope.trSalesRecipeForm.total = total;
            $scope.trSalesRecipeForm.grand_total = total + $scope.trSalesRecipeForm.tuslah;
        }
        $scope.calcTuslah = (elm) => {
            console.log(elm);
            let trSalesRecipeForm = elm.$parent.$parent.trSalesRecipeForm;
            if (trSalesRecipeForm.tuslah == null || trSalesRecipeForm.tuslah == '') {
                $scope.trSalesRecipe.tuslah = 0;
            }
            $scope.calcTotalForm();
        }
        $scope.calcTotal = () => {
            let total = 0;
            angular.forEach($scope.trSalesRecipe.medicineRecipe, (val, key) => {
                total += parseInt(val.subtotal);
            });
            $scope.trSalesRecipe.total = total;
            $scope.trSalesRecipe.grand_total = total - $scope.trSalesRecipe.discount;
            $scope.trSalesRecipe.balance = $scope.trSalesRecipe.payment > (total - $scope.trSalesRecipe.discount) ? $scope.trSalesRecipe.payment - (total - $scope.trSalesRecipe.discount) : 0;
        }
        $scope.calcDiscount = (elm) => {
            let trSalesRecipe = elm.$parent.$parent.trSalesRecipe;
            if (trSalesRecipe.discount == null || trSalesRecipe.discount == '' || (trSalesRecipe.discount > trSalesRecipe.total)) {
                $scope.trSalesRecipe.discount = 0;
            }
            $scope.calcTotal();
        }
        $scope.calcPayment = (elm) => {
            let trSalesRecipe = elm.$parent.$parent.trSalesRecipe;
            if(trSalesRecipe.payment == null || trSalesRecipe.payment == '') {
                $scope.trSalesRecipe.payment = 0;
            }
            $scope.calcTotal();
        }
        /*
        |--------------------------------------------------------------------------
        | Add Recipe
        |--------------------------------------------------------------------------
        */
        $scope.addRecipe = (elm, data) => {
            $scope.trSalesRecipe.medicineRecipe.push({
                medicineRecipeName: data.medicineRecipeName,
                details: $scope.data_cart,
                tuslah: data.tuslah,
                price: data.total,
                subtotal: data.grand_total,
            });
            $scope.trSalesRecipeForm = {
                medicineRecipeName: '',
                total: 0,
                tuslah: 0,
                grand_total: 0,
            };
            $scope.data_cart = [];
            $scope.calcTotal();
            console.log('Komposisi', $scope.trSalesRecipe.medicineRecipe);
        }
        /*
        |--------------------------------------------------------------------------
        | Remove Recipe
        |--------------------------------------------------------------------------
        */
        $scope.removeRecipe = (elm) => {
            $scope.trSalesRecipe.medicineRecipe.splice(elm.$index, 1);
            $scope.calcTotal();
        }
        /*
        |--------------------------------------------------------------------------
        | Add to cart
        |--------------------------------------------------------------------------
        */
        $scope.addCart = (data) => {
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
            });
        }
        /*
        |--------------------------------------------------------------------------
        | Remove cart
        |--------------------------------------------------------------------------
        */
        $scope.removeCart = (elm) => {
            $scope.data_cart.splice(elm.$index, 1);
            $scope.calcTotal();
        }
        /*
        |--------------------------------------------------------------------------
        | Create data
        |--------------------------------------------------------------------------
        */
        $scope.createData = () => {
            trSalesRecipeFactory.createData($scope.trSalesRecipe).then(() => {
                var process = trSalesRecipeFactory.result_process;
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

    $scope.readDataById = (id) => {
        trSalesRecipeFactory.readDataById(id)
            .then(function () {
                let tr_sales_recipe = trSalesRecipeFactory.result_data.tr_sales_recipe;
                console.log(tr_sales_recipe);
                $scope.tr_sales_recipe = {
                    balance: tr_sales_recipe.balance,
                    date: tr_sales_recipe.date,
                    time: tr_sales_recipe.time,
                    discount: tr_sales_recipe.discount,
                    grandTotal: tr_sales_recipe.grandTotal,
                    id: tr_sales_recipe.id,
                    patient: tr_sales_recipe.patient,
                    doctor: tr_sales_recipe.doctor,
                    payment: tr_sales_recipe.payment,
                    total: tr_sales_recipe.total,
                    userName: tr_sales_recipe.userName,
                    details: tr_sales_recipe.details,
                };
            });
    }

    if ($state.current.name == "admin-trSalesRecipe-print") {
        $scope.readDataById($stateParams.id);
    }
})
