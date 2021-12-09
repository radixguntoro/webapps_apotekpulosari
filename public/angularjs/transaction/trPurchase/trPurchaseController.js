var app = angular.module('trPurchaseCtrl', ['factoryTrPurchase', 'factoryMedicines', 'factoryReturns', 'factorySuppliers']);

app.controller('trPurchaseController', function ($rootScope, $scope, $state, $stateParams, toastr, trPurchaseFactory, medicinesFactory, returnsFactory, suppliersFactory) {
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

    $scope.filter = [];
    let filter = '';

    $scope.get_id = [];
    let get_id = '';

    $scope.resetVariable = function () {
        $scope.trPurchase = {
            supplierId: '',
            supplierName: '',
            supplierPhone: '',
            invoiceNumber: '',
            date: '',
            total: 0,
            subtotal: 0,
            discount: 0,
            grandTotal: 0,
            ppn: 0,
            ppnPrice: 0,
            status: 'cod',
        };
        $scope.return = {
            total: 0,
            subtotal: 0,
            discount: 0,
            grandTotal: 0,
            ppn: 0,
            ppnPrice: 0,
        }
        $scope.trPurchase.details = [];
        $scope.data_cart = [];
        $scope.return_cart = [];
        $scope.is_ppn = false;
    }

    $scope.is_payment = false;
    $scope.is_check_all = false;
    $scope.is_correction = false;

    $scope.resetVariable();

    $scope.getStatus = [
        {id: 'cod', name: 'Cash On Delivery'},
        {id: 'credit', name: 'Hutang'},
        {id: 'consignment', name: 'Konsinyasi'},
        {id: 'paid', name: 'Lunas'}
    ];
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    if ($state.current.name == "admin-trPurchase-recap") {
        $scope.getResultsPage = function (page_number) {
            $scope.loading = true;
            if (!$.isEmptyObject($scope.library_temp)) {
                trPurchaseFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by, filter)
                    .then(function () {
                        $scope.data = trPurchaseFactory.result_data;
                        $scope.total_data = trPurchaseFactory.total_data;
                        $scope.loading = false;
                    });
            } else {
                trPurchaseFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by, filter)
                    .then(function () {
                        $scope.data = trPurchaseFactory.result_data;
                        $scope.total_data = trPurchaseFactory.total_data;
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
    | Set filter
    |--------------------------------------------------------------------------
    */
    $scope.setFilter = function(elm, event, id) {
        let is_exist = 0;
        angular.forEach($scope.filter, function(v, k) {
            if (v == id) {
                is_exist += 1
            }
        });

        if (event.currentTarget.checked == false) {
            if (is_exist < 1) {
                $scope.filter.push(id);
            }
        } else {
            if (is_exist > 0) {
                var index = $scope.filter.indexOf(id);
                $scope.filter.splice(index, is_exist);
            }
        }

        filter = elm.$parent.filter;
        $scope.getResultsPage(new_page, $scope.page_row, $scope.sort_by, filter);
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
    /*
    |--------------------------------------------------------------------------
    | Read data supplier by autocomplete
    |--------------------------------------------------------------------------
    */
    $scope.readSupplierByAutoComplete = function(typo, response) {
        suppliersFactory.readDataByAutocomplete(typo.term).then(function() {
            $scope.data_supplier = suppliersFactory.result_data.supplier.data;
            if ($scope.data_supplier.length > 0) {
                response($scope.data_supplier);
            }
        });
    };
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
    | Set & unset correction page
    |--------------------------------------------------------------------------
    */
    $scope.setCorrection = function(elm) {
        $scope.is_correction = true;
    }
    $scope.unsetCorrection = function(elm) {
        $scope.is_correction = false;
    }
    /*
    |--------------------------------------------------------------------------
    | Set check list
    |--------------------------------------------------------------------------
    */
    $scope.setCheckList = function(elm) {
        $scope.is_payment = true;
    }
    $scope.unsetCheckList = function(elm) {
        $scope.is_payment = false;
        $scope.get_id = [];
        console.log($scope.get_id);
    }
    $scope.setCheckAll = function(flag) {
        if (flag) {
            $scope.get_id = [];
            $scope.is_check_all = false;
        } else {
            angular.forEach($scope.data, function(v, k) {
                if (v.status == 'credit') {
                    $scope.get_id.push(v.id);
                }
            });
            $scope.is_check_all = true;
        }
        console.log($scope.get_id);
    }
    $scope.setCheckData = function(elm, event, id) {
        let is_exist = 0;
        angular.forEach($scope.get_id, function(v, k) {
            if (v == id) {
                is_exist += 1
            }
        });

        if (event.currentTarget.checked == true) {
            if (is_exist < 1) {
                $scope.get_id.push(id);
            }
        } else {
            if (is_exist > 0) {
                var index = $scope.get_id.indexOf(id);
                $scope.get_id.splice(index, is_exist);
            }
        }

        get_id = elm.$parent.get_id;
        console.log(get_id);
    }
    /*
    |--------------------------------------------------------------------------
    | Set supplier info
    |--------------------------------------------------------------------------
    */
    $scope.setSupplier = function(data) {
        suppliersFactory.readDataById(data.id).then(function() {
            $scope.trPurchase.supplierId = suppliersFactory.result_data.supplier.id;
            $scope.trPurchase.supplierName = suppliersFactory.result_data.supplier.name;
            $scope.trPurchase.supplierPhone = suppliersFactory.result_data.supplier.phone;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Calculation
    |--------------------------------------------------------------------------
    */
    $scope.calcTotal = function() {
        if ($state.current.name == "admin-trPurchase-create") {
            let total = 0;
            angular.forEach($scope.data_cart, function(val, key) {
                total += val.subtotal;
                val.subtotal = (val.price - (val.price * val.discount)) * val.qty;
                val.tabletPriceSell = (val.price + (val.price * val.profit)) + ((val.price + (val.price * val.profit)) * $scope.trPurchase.ppn);
            });
            $scope.trPurchase.total = total;
            $scope.trPurchase.subtotal = total - $scope.trPurchase.discount;
            $scope.trPurchase.grandTotal = $scope.trPurchase.subtotal + $scope.trPurchase.ppnPrice;
        }

        if ($state.current.name == "admin-trPurchase-return") {
            let total = 0;
            angular.forEach($scope.return_cart, function(val, key) {
                total += val.subtotal;
                val.subtotal = val.price * val.qty;
            });
            $scope.return.total = total;
            $scope.return.subtotal = total - $scope.return.discount;
            $scope.return.grandTotal = $scope.return.subtotal + $scope.return.ppnPrice;
        }
    }
    
    $scope.calcPriceSell = function(elm, unit) {
        console.log(elm);
        switch (unit) {
            case 'box':
                let baseBox = elm;
                baseBox.cart.tabletPriceSell = (baseBox.cart.price + (baseBox.cart.price * baseBox.cart.profit));  
                baseBox.cart.subtotal = (baseBox.cart.price + (baseBox.cart.price * baseBox.cart.profit)) - (baseBox.cart.price * baseBox.cart.discount) * baseBox.cart.qty;   
                break;
            case 'strip':
                let baseStrip = elm;
                baseStrip.cart.tabletPriceSell = (baseStrip.cart.price + (baseStrip.cart.price * baseStrip.cart.profit));  
                baseStrip.cart.subtotal = (baseStrip.cart.price + (baseStrip.cart.price * baseStrip.cart.profit)) - (baseStrip.cart.price * baseStrip.cart.discount) * baseStrip.cart.qty;  
                break;
            case 'tablet':
                let baseTablet = elm;
                if (baseTablet.cart.price == '' || baseTablet.cart.price == null) {
                    baseTablet.cart.price = 0;
                    $scope.calcTotal();
                } else {
                    baseTablet.cart.tabletPriceSell = (baseTablet.cart.price + (baseTablet.cart.price * baseTablet.cart.profit));  
                    baseTablet.cart.subtotal = (baseTablet.cart.price - (baseTablet.cart.price * baseTablet.cart.discount)) * baseTablet.cart.qty;
                    $scope.calcTotal();
                }
                break;
            default:
                break;
        }
    }
    $scope.calcDiscountPercent = function(elm) {
        let baseTablet = elm;
        if (baseTablet.cart.discount == '' || baseTablet.cart.discount == null || baseTablet.cart.discount > 1) {
            baseTablet.cart.discount = 0;
            $scope.calcTotal();
        } else {
            baseTablet.cart.subtotal = (baseTablet.cart.price - (baseTablet.cart.price * baseTablet.cart.discount)) * baseTablet.cart.qty;
            $scope.calcTotal();
        }
        $scope.calcTotal();
    }
    $scope.calcQty = function(elm) {
        let baseTablet = elm;
        if (baseTablet.cart.qty == '' || baseTablet.cart.qty == null) {
            baseTablet.cart.qty = 1;
            $scope.calcTotal();
        } else {
            baseTablet.cart.subtotal = (baseTablet.cart.price - (baseTablet.cart.price * baseTablet.cart.discount)) * baseTablet.cart.qty;
            $scope.calcTotal();
        }
        $scope.calcTotal();
    }
    $scope.calcDiscount = function(elm) {
        $scope.is_ppn = false;
        $scope.trPurchase.ppn = 0;
        $scope.trPurchase.ppnPrice = 0;
        if ($state.current.name == "admin-trPurchase-return") {
            $scope.return.ppn = 0;
            $scope.return.ppnPrice = 0;
        }
        if (elm.discount == '' || elm.discount == null || elm.discount > elm.total) {
            elm.discount = 0;
            $scope.calcTotal();
        } else {
            $scope.calcTotal();
        }
        $scope.calcTotal();
    }
    $scope.calcCompareQty = function(elm) {
        console.log(elm.item);
        if (elm.item.qtyReturn == '' || elm.item.qtyReturn == null || elm.item.qtyReturn > elm.item.qty) {
            elm.item.qtyReturn = 0;
        }
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
                    id: medicine.tabletId,
                    name: medicine.name,
                    price_purchase_old: parseFloat(medicine.tabletPricePurchase),
                    price_sell_old: parseFloat(medicine.tabletPriceSell),
                    tabletPricePurchase: parseFloat(medicine.tabletPricePurchase),
                    tabletPriceSell: parseFloat(medicine.tabletPriceSell),
                    price: parseFloat(medicine.tabletPricePurchase),
                    qty: 1,
                    profit: parseFloat(medicine.tabletProfitPercent),
                    subtotal: parseFloat(medicine.tabletPricePurchase),
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
    | Remove to cart
    |--------------------------------------------------------------------------
    */
    $scope.removeCart = function(elm) {
        $scope.data_cart.splice(elm.$index, 1);
        $scope.is_ppn = false;
        $scope.trPurchase.ppn = 0;
        $scope.trPurchase.ppnPrice = 0;
        $scope.trPurchase.discount = 0;
        $scope.calcTotal();
    }
    /*
    |--------------------------------------------------------------------------
    | Add to cart return
    |--------------------------------------------------------------------------
    */
    $scope.addCartReturn = function(elm, trPurchase, trPurchaseDetail) {
        trPurchaseDetail = {
            trPurchaseId: trPurchase.id,
            discount: trPurchaseDetail.discount,
            id: trPurchaseDetail.id,
            medicineName: trPurchaseDetail.medicineName,
            medicineId: trPurchaseDetail.medicineId,
            price: trPurchaseDetail.price,
            qty: trPurchaseDetail.qtyReturn,
            subtotal: trPurchaseDetail.price * trPurchaseDetail.qtyReturn,
            state: 'trPurchase',
        }
        $scope.return_cart.push(trPurchaseDetail);
        $scope.calcTotal();
    }
    /*
    |--------------------------------------------------------------------------
    | Remove to cart return
    |--------------------------------------------------------------------------
    */
    $scope.removeCartReturn = function(elm) {
        $scope.return_cart.splice(elm.$index, 1);
        $scope.is_ppn = false;
        $scope.return.ppn = 0;
        $scope.return.ppnPrice = 0;
        $scope.return.discount = 0;
        $scope.calcTotal();
    }
    /*
    |--------------------------------------------------------------------------
    | Create data
    |--------------------------------------------------------------------------
    */
    $scope.createData = function() {
        let trPurchaseDetail = [];
        angular.forEach($scope.data_cart, function(v, k) {
            trPurchaseDetail.push({
                discount: v.discount,
                id: v.id,
                medicines_items_id: v.medicines_items_id,
                name: v.name,
                price: v.price,
                price_purchase_old: v.price_purchase_old,
                price_sell_old: v.price_sell_old,
                profit: v.profit,
                qty: v.qty,
                subtotal: v.subtotal,
                tablet_price_purchase: v.tabletPricePurchase,
                tablet_price_sell: v.tabletPriceSell,
                unit: v.unit,
            });
        });

        let trPurchase = {
            suppliers_persons_id: $scope.trPurchase.supplierId,
            invoice_number: $scope.trPurchase.invoiceNumber,
            date: $scope.trPurchase.date,
            total: $scope.trPurchase.total,
            discount: $scope.trPurchase.discount,
            grand_total: $scope.trPurchase.grandTotal,
            ppn: $scope.trPurchase.ppn,
            ppn_price: $scope.trPurchase.ppnPrice,
            status: $scope.trPurchase.status,
            details: trPurchaseDetail
        };
        console.log(trPurchase);
        trPurchaseFactory.createData(trPurchase).then(function() {
            var process = trPurchaseFactory.result_process;
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
    /*
    |--------------------------------------------------------------------------
    | Set PPN
    |--------------------------------------------------------------------------
    */
    $scope.setPpn = function(flag) {
        if (flag) {
            $scope.is_ppn = false;
            $scope.trPurchase.ppn = 0;
            $scope.trPurchase.ppnPrice = 0;
            if ($state.current.name == "admin-trPurchase-return") {
                $scope.return.ppn = 0;
                $scope.return.ppnPrice = 0;
            }
            $scope.calcTotal();
        } else {
            $scope.is_ppn = true;
            $scope.trPurchase.ppn = 0.1;
            $scope.trPurchase.ppnPrice =  $scope.trPurchase.grandTotal * 0.1;
            if ($state.current.name == "admin-trPurchase-return") {
                $scope.return.ppn = 0.1;
                $scope.return.ppnPrice = $scope.return.grandTotal * 0.1;
            }
            $scope.calcTotal();
        }
        console.log($scope.trPurchase.ppn);
    }
    /*
    |--------------------------------------------------------------------------
    | Create return
    |--------------------------------------------------------------------------
    */
    $scope.createReturn = function(elm) {
        console.log($scope.return);
        console.log($scope.return_cart);
        let trPurchaseReturn = {
            trPurchaseId: $scope.trPurchase.id,
            total: $scope.return.total,
            subtotal: $scope.return.subtotal,
            discount: $scope.return.discount,
            grandTotal: $scope.return.grandTotal,
            ppn: $scope.return.ppn,
            state: 'trPurchase',
            details: $scope.return_cart
        }

        $scope.loading = true;

        returnsFactory.createData(trPurchaseReturn).then(function() {
            var process = returnsFactory.result_process;
            console.log(process);
            if (process == 0) {
                swal(opt_failed, function () {
                    $scope.loading = false;
                });
                return;
            } else {
                $scope.loading = false;
                swal(opt_saved, function () {
                    $state.go('admin-trPurchase-recap');
                });
                angular.element('.modal-backdrop').remove();
                $scope.resetVariable();
            }
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    $scope.readDataById = function(id) {
        trPurchaseFactory.readDataById(id)
            .then(function () {
                let tr_purchase = trPurchaseFactory.result_data.trPurchase;
                let tr_purchase_details = trPurchaseFactory.result_data.trPurchase.details;
                $scope.trPurchaseDetail = [];

                angular.forEach(tr_purchase_details, function(v, k) {
                    $scope.trPurchaseDetail.push({
                        discount: v.discount,
                        id: v.id,
                        medicineName: v.medicineName,
                        medicineId: v.medicineId,
                        price: v.price,
                        qty: v.qty,
                        qtyReturn: 0,
                        subtotal: v.subtotal,
                    });
                });

                $scope.trPurchase = {
                    invoiceNumber: tr_purchase.invoiceNumber,
                    supplierName: tr_purchase.supplierName,
                    supplierPhone: tr_purchase.supplierPhone,
                    date: tr_purchase.date,
                    createdAt: tr_purchase.createdAt,
                    timeAt: tr_purchase.timeAt,
                    discount: tr_purchase.discount,
                    ppn: tr_purchase.ppn,
                    grandTotal: tr_purchase.grandTotal,
                    id: tr_purchase.id,
                    total: tr_purchase.total,
                    userName: tr_purchase.userName,
                    details: $scope.trPurchaseDetail
                };
            });
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    if ($state.current.name == "admin-trPurchase-return") {
        $scope.readDataById($stateParams.id);
    }
    /*
    |--------------------------------------------------------------------------
    | Update data status
    |--------------------------------------------------------------------------
    */
    $scope.updateDataStatus = function () {
        trPurchaseFactory.updateDataStatus($scope.get_id).then(function () {
            var process = trPurchaseFactory.result_process;
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
            }
            $scope.loading = false;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data 
    |--------------------------------------------------------------------------
    */
    $scope.deleteData = function (data) {
        $scope.loading = true;
        trPurchaseFactory.deleteData(data).then(function () {
            var process = trPurchaseFactory.result_process;
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
