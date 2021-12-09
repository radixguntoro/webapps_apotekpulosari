var app = angular.module('returnsCtrl', ['factoryReturns', 'factoryMedicines', 'factoryReturns', 'factorySuppliers']);

app.controller('returnsController', function($rootScope, $scope, $state, $stateParams, toastr, returnsFactory) {
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

    $scope.resetVariable =  () => {
        $scope.returns = {
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
        $scope.returns.details = [];
        $scope.data_cart = [];
        $scope.is_ppn = false;
    }

    $scope.is_payment = false;
    $scope.is_check_all = false;

    $scope.resetVariable();

    $scope.getStatus = [
        {id: 'cod', name: 'Cash On Delivery'},
        {id: 'credit', name: 'Hutang'},
        {id: 'consignment', name: 'Konsinyasi'},
        {id: 'paid', name: 'Lunas'}
    ];
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter returns
    |--------------------------------------------------------------------------
    */
    if ($state.current.name == "admin-return-trPurchase-list") {
        $scope.getResultsPage = (page_number) => {
            $scope.loading = true;
            if (!$.isEmptyObject($scope.library_temp)) {
                returnsFactory.readDataBySearch('trPurchase', page_number, $scope.search_text, $scope.page_row, $scope.sort_by, filter)
                    .then(() => {
                        $scope.data = returnsFactory.result_data;
                        $scope.total_data = returnsFactory.total_data;
                        $scope.loading = false;
                    });
            } else {
                returnsFactory.readDataByPagination('trPurchase', page_number, $scope.page_row, $scope.sort_by, filter)
                    .then(() => {
                        $scope.data = returnsFactory.result_data;
                        $scope.total_data = returnsFactory.total_data;
                        $scope.loading = false;
                    });
            }
        }

        $scope.getResultsPage(new_page, $scope.page_row, $scope.sort_by);

        $scope.pageChanged = (new_page, page_row, sort_by) => {
            $scope.getResultsPage(new_page, page_row, sort_by);
        };

        $scope.searchData = () => {
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
    $scope.setFilter = (elm, event, id) => {
        let is_exist = 0;
        angular.forEach($scope.filter, (v, k) => {
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
    | Read data by id
    |--------------------------------------------------------------------------
    */
    if ($state.current.name == "admin-return-trPurchase-detail") {
        let state = 'trPurchase';
        returnsFactory.readDataById($stateParams.id, state).then(() => {
            $scope.trPurchase = returnsFactory.result_data.trPurchase;
            $scope.trPurchaseReturn = returnsFactory.result_data.trPurchaseReturn;
        });
    }
})
