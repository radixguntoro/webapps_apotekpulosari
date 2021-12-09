var app = angular.module('medicinesCtrl', ['factoryMedicines', 'factoryCategories', 'factoryUnits', 'factorySuppliers']);

app.controller('medicinesController', function ($rootScope, $scope, $state, $stateParams, toastr, medicinesFactory, categoriesFactory, unitsFactory, suppliersFactory) {
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
    $scope.filter = [];
    let filter = '';

    $scope.rows = [10, 25, 50, 100];
    $scope.page_row = $scope.rows[0];
    $scope.sort_by = '';
    let new_page = 1;

    $scope.resetVariable = function () {
        $scope.medicine = {
            categoryName: '',
            categoryId: '',
            id: '',
            name: '',
            qtyMin: 0,
            qtyTotal: 0,
            status: 'active',
            category: {
                selected: {
                    id: 1002000005,
                    name: 'OTC/Bebas',
                }
            },
            unit: {
                selected: {
                    id: 1,
                    name: 'Biji',
                }
            },
            supplier: {
                selected: {
                    id: 1022000000,
                    name: 'Apotek Pulosari',
                }
            },
            supplierPersonsId: '',
            supplierPersonsName: '',
            tabletBarcode: '',
            tabletId: '',
            tabletPricePurchase: 0.00,
            tabletPriceSell: 0.00,
            tabletProfitPercent: 0.1,
            tabletProfitValue: '',
            tabletQrcode: '',
            tabletQty: 0,
            tabletUnit: 'Tablet',
        };
    }

    $scope.resetVariable();
    /*
    |--------------------------------------------------------------------------
    | Calculate purchase price
    |--------------------------------------------------------------------------
    */
    $scope.calcPricePurchase = function(elm, unit) {
        console.log(elm);
        switch (unit) {
            // case 'box':
            //     let baseBox = elm.$parent.$parent;
            //     baseBox.medicine.boxPricePurchase = baseBox.medicine.boxPriceSell - (parseInt(baseBox.medicine.boxPriceSell) * baseBox.medicine.boxProfitPercent);
            //     console.log(baseBox.medicine.boxProfitPercent);    
            //     break;
            // case 'strip':
            //     let baseStrip = elm.$parent.$parent;
            //     baseStrip.medicine.stripPricePurchase = baseStrip.medicine.stripPriceSell - (parseInt(baseStrip.medicine.stripPriceSell) * baseStrip.medicine.stripProfitPercent);
            //     console.log(baseStrip.medicine.stripProfitPercent);    
            //     break;
            case 'tablet':
                let baseTablet = elm.$parent.$parent;
                baseTablet.medicine.tabletPricePurchase = baseTablet.medicine.tabletPriceSell - (parseInt(baseTablet.medicine.tabletPriceSell) * baseTablet.medicine.tabletProfitPercent);
                console.log(baseTablet.medicine.tabletProfitPercent);    
                break;
            default:
                break;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    $scope.getResultsPage = function (page_number) {
        $scope.loading = true;
        if (!$.isEmptyObject($scope.library_temp)) {
            medicinesFactory.readDataBySearch(page_number, $scope.search_text, $scope.page_row, $scope.sort_by, filter)
                .then(function () {
                    $scope.data = medicinesFactory.result_data;
                    $scope.total_data = medicinesFactory.total_data;
                    $scope.loading = false;
                });
        } else {
            medicinesFactory.readDataByPagination(page_number, $scope.page_row, $scope.sort_by, filter)
                .then(function () {
                    $scope.data = medicinesFactory.result_data;
                    $scope.total_data = medicinesFactory.total_data;
                    $scope.loading = false;
                });
        }
    }

    $scope.getResultsPage(new_page, $scope.page_row, $scope.sort_by, filter);

    $scope.pageChanged = function (new_page, page_row, sort_by, filter) {
        $scope.getResultsPage(new_page, page_row, sort_by, filter);
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
    | Read all data category
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
    | Read all data unit
    |--------------------------------------------------------------------------
    */
    unitsFactory.readDataAll().then(function () {
        $scope.getUnits = [];
        for (var i = 0; i < unitsFactory.result_data.length; i++) {
            $scope.getUnits[i] = unitsFactory.result_data[i];
        }
    });
    /*
    |--------------------------------------------------------------------------
    | Read all data supplier
    |--------------------------------------------------------------------------
    */
    suppliersFactory.readDataAll().then(function () {
        $scope.getSuppliers = [];
        for (var i = 0; i < suppliersFactory.result_data.length; i++) {
            $scope.getSuppliers[i] = suppliersFactory.result_data[i];
        }
    });
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
        $scope.getResultsPage(new_page, $scope.page_row, $scope.sort_by, filter);
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
    | Set profit percent
    |--------------------------------------------------------------------------
    */
    $scope.setProfitPercentByCategory = function(category_id) {
        switch (category_id) {
            case '1002000001':
                $scope.medicine.tabletProfitPercent = 0.15
                break;
            case '1002000002':
                $scope.medicine.tabletProfitPercent = 0.16
                break;
            case '1002000003':
                $scope.medicine.tabletProfitPercent = 0.13
                break;
            case '1002000004':
                $scope.medicine.tabletProfitPercent = 0.0925
                break;
            case '1002000005':
                $scope.medicine.tabletProfitPercent = 0.1
                break;
            default:
                break;
        }
        return $scope.medicine.tabletProfitPercent;
    }
    /*
    |--------------------------------------------------------------------------
    | Set discount by category
    |--------------------------------------------------------------------------
    */
    $scope.setCategory = function(elm, category) {
        $scope.medicine.tabletProfitPercent = $scope.setProfitPercentByCategory(category.selected.id);
        $scope.calcPricePurchase(elm, 'tablet');
        console.log($scope.medicine.tabletProfitPercent);
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
    | Create data
    |--------------------------------------------------------------------------
    */
    $scope.createData = function () {
        $scope.medicine = {
            device: 'desktop',
            categories_name: $scope.medicine.category.selected.name,
            categories_id: $scope.medicine.category.selected.id,
            unit_name: $scope.medicine.unit.selected.name,
            unit_id: $scope.medicine.unit.selected.id,
            id: $scope.medicine.id,
            name: $scope.medicine.name,
            qty_min: $scope.medicine.qtyMin,
            qty_total: $scope.medicine.qtyTotal,
            status: $scope.medicine.status,
            suppliers_persons_id: $scope.medicine.supplier.selected.id,
            suppliers_persons_name: $scope.medicine.supplier.selected.name,
            detail: [
                {
                    barcode: $scope.medicine.tabletBarcode,
                    id: $scope.medicine.tabletId,
                    price_purchase: $scope.medicine.tabletPricePurchase,
                    price_sell: $scope.medicine.tabletPriceSell,
                    profit_percent: $scope.medicine.tabletProfitPercent,
                    profit_value: $scope.medicine.tabletProfitValue,
                    qrcode: $scope.medicine.tabletQrcode,
                    qty: $scope.medicine.tabletQty,
                    unit: $scope.medicine.tabletUnit,
                }
            ],
        };
        $scope.loading = true;
        medicinesFactory.createData($scope.medicine).then(function () {
            var process = medicinesFactory.result_process;
            console.log("Status simpan", process);
            if (process == 0) {
                swal(opt_failed, function () {
                    $scope.loading = false;
                });
                return;
            } else if(process == 2) {
                $scope.loading = false;
                toastr.error('Data sudah terdaftar.', 'Gagal!');
                $scope.resetVariable();
            } else {
                $scope.loading = false;
                swal(opt_saved, function () {
                    $state.go("admin-medicine-list");
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
    if ($state.current.name == "admin-medicine-edit") {
        medicinesFactory.readDataById($stateParams.id)
            .then(function () {
                let medicine = medicinesFactory.result_data.medicine;
                $scope.medicine = {
                    categoryName: medicine.categoryName,
                    categoryId: medicine.categoryId,
                    category: {
                        selected: {
                            id: medicine.categoryId,
                            name: medicine.categoryName,
                        }
                    },
                    unitName: medicine.unitName,
                    unitId: medicine.unitId,
                    unit: {
                        selected: {
                            id: medicine.unitId,
                            name: medicine.unitName,
                        }
                    },
                    id: medicine.id,
                    name: medicine.name,
                    qtyMin: medicine.qtyMin,
                    qtyTotal: medicine.qtyTotal,
                    status: medicine.status,
                    supplierPersonsId: medicine.supplierPersonsId,
                    supplierPersonsName: medicine.supplierPersonsName,
                    supplier: {
                        selected: {
                            id: medicine.supplierPersonsId,
                            name: medicine.supplierPersonsName,
                        }
                    },
                    tabletBarcode: medicine.tabletBarcode,
                    tabletId: medicine.tabletId,
                    tabletPricePurchase: medicine.tabletPricePurchase,
                    tabletPriceSell: medicine.tabletPriceSell,
                    tabletProfitPercent: medicine.tabletProfitPercent,
                    tabletProfitValue: medicine.tabletProfitValue,
                    tabletQrcode: medicine.tabletQrcode,
                    tabletQty: medicine.tabletQty,
                    tabletUnit: medicine.tabletUnit,
                };

                console.log($scope.medicine);
                $state.go("admin-medicine-edit", {
                    id: medicine.id
                });
            });

        $scope.updateData = function () {
            $scope.loading = true;
            $scope.medicine = {
                device: 'desktop',
                categories_name: $scope.medicine.category.selected.name,
                categories_id: $scope.medicine.category.selected.id,
                unit_name: $scope.medicine.unit.selected.name,
                unit_id: $scope.medicine.unit.selected.id,
                id: $scope.medicine.id,
                name: $scope.medicine.name,
                qty_min: $scope.medicine.qtyMin,
                qty_total: $scope.medicine.qtyTotal,
                status: $scope.medicine.status,
                suppliers_persons_id: $scope.medicine.supplier.selected.id,
                suppliers_persons_name: $scope.medicine.supplier.selected.name,
                detail: [
                    {
                        barcode: $scope.medicine.tabletBarcode,
                        id: $scope.medicine.tabletId,
                        price_purchase: $scope.medicine.tabletPricePurchase,
                        price_sell: $scope.medicine.tabletPriceSell,
                        profit_percent: $scope.medicine.tabletProfitPercent,
                        profit_value: $scope.medicine.tabletProfitValue,
                        qrcode: $scope.medicine.tabletQrcode,
                        qty: $scope.medicine.tabletQty,
                        unit: $scope.medicine.tabletUnit,
                    }
                ],
            };
            medicinesFactory.updateData($scope.medicine).then(function () {
                var process = medicinesFactory.result_process;
                if (process == 0) {
                    swal(opt_failed, function () {
                        $scope.loading = false;
                    });
                    return;
                } else {
                    $scope.loading = false;
                    swal(opt_saved, function () {
                        $state.go("admin-medicine-list");
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
        medicinesFactory.deleteData(data).then(function () {
            var process = medicinesFactory.result_process;
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
    /*
    |--------------------------------------------------------------------------
    | Update data status
    |--------------------------------------------------------------------------
    */
    $scope.set_status = 'inactive';
    $scope.updateDataStatus = function (elm) {
        console.log(elm);
        $scope.status = elm.medicine.status == 'active' ? 'inactive' : elm.medicine.status == 'inactive' ? 'active' : '';
        medicinesFactory.updateDataStatus({
            id: elm.medicine.id,
            status: $scope.status
        }).then(function () {
            var process = medicinesFactory.result_process;
            if (process == 0) {
                toastr.error('Status data gagal diubah.', 'Gagal!');
                return;
            } else {
                toastr.success('Status data telah diubah.', 'Sukses!');
                $state.reload();
            }
        });
    }
})
