<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>Apotek Pulosari - Point Of Sales</title>
	<!-- Angular Material style sheet -->
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/mdi/css/materialdesignicons.min.css') }}">
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/angular-toastr/dist/angular-toastr.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/angular-loading-bar/build/loading-bar.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/angular-ui-select/dist/select.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/selectize/dist/css/selectize.css') }}" />
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/angular-colorpicker-directive/css/color-picker.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/angular-tabs/angular-tabs.css') }}" />
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/jquery-ui/themes/base/jquery-ui.min.css') }}"/>
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/stickyTable/stickyTable.css') }}"/>
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/sweetalert/dist/sweetalert.css') }}"/>
	<link rel="stylesheet" href="{{ asset('plugin/bower_components/perfect-scrollbar/css/perfect-scrollbar.css') }}"/>
	<link rel="stylesheet" href="{{ asset('backend/css/style.css') }}">
	{{-- <link rel="shortcut icon" href="{{ asset('frontend/img/favicon.ico') }}"> --}}
</head>
<body ng-app="backendApp" ng-cloak ng-controller="globalController">
	<!-- Sidebar -->
    <aside class="animated faster">
        <div class="sidebar-logo d-flex align-items-center">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('backend/img/logo/logo-white.png') }}" width="26" height="26" class="d-inline-block align-top mr-3" alt="">
                <span class="logo-title animated fadeIn faster"><span class="text-amber font-weight-bold">Apotek</span> Pulosari</span>
            </a>
        </div>
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item nav-title">
                    <div class="nav-link d-flex align-items-center text-uppercase mt-3" href="#">
                        <div class="animated fadeIn faster">
                            <i class="mdi mdi-minus mdi-24px animated fadeIn faster"></i>
                            <span class="animated fadeIn faster font-weight-normal">Master</span>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center nav-sub" data-target="#master" data-toggle="collapse" ng-class="{active: $state.includes('admin-medicine-create') || $state.includes('admin-medicine-edit') || $state.includes('admin-medicine-list') || $state.includes('admin-category-create') || $state.includes('admin-category-edit') || $state.includes('admin-category-list') ||
                    $state.includes('admin-unit-create') || $state.includes('admin-unit-edit') || $state.includes('admin-unit-list') || $state.includes('admin-patient-create') || $state.includes('admin-patient-edit') || $state.includes('admin-patient-list') || $state.includes('admin-user-create') || $state.includes('admin-user-edit') || $state.includes('admin-user-list') || $state.includes('admin-supplier-create') || $state.includes('admin-supplier-edit') || $state.includes('admin-supplier-list')}" href="javascript:;">
                        <i class="mdi mdi-layers mdi-inherit mdi-24px animated fadeIn faster mr-3"></i>
                        <span class="animated fadeIn faster">Master</span>
                    </a>
                    <div class="collapse animated fadeIn faster" id="master" ng-class="{'show': $state.includes('admin-medicine-create') || $state.includes('admin-medicine-edit') || $state.includes('admin-medicine-list') || $state.includes('admin-category-create') || $state.includes('admin-category-edit') || $state.includes('admin-category-list') || $state.includes('admin-unit-create') || $state.includes('admin-patient-create') || $state.includes('admin-patient-edit') || $state.includes('admin-patient-list') || $state.includes('admin-unit-edit') || $state.includes('admin-unit-list') || $state.includes('admin-user-create') || $state.includes('admin-user-edit') || $state.includes('admin-user-list') || $state.includes('admin-supplier-create') || $state.includes('admin-supplier-edit') || $state.includes('admin-supplier-list')}">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-category-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-category-create') || $state.includes('admin-category-list') || $state.includes('admin-category-edit')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Kategori</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-unit-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-unit-create') || $state.includes('admin-unit-list') || $state.includes('admin-unit-edit')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Satuan</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-medicine-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-medicine-create') || $state.includes('admin-medicine-list') || $state.includes('admin-medicine-edit')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Obat</span>
                                </a>
                            </li>
                            {{-- <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-patient-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-patient-create') || $state.includes('admin-patient-list') || $state.includes('admin-patient-edit')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Pasien</span>
                                </a>
                            </li> --}}
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-user-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-user-create') || $state.includes('admin-user-list') || $state.includes('admin-user-edit')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Pengguna</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-supplier-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-supplier-create') || $state.includes('admin-supplier-list') || $state.includes('admin-supplier-edit')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Supplier</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item nav-title">
                    <div class="nav-link d-flex align-items-center text-uppercase mt-3" href="#">
                        <div class="animated fadeIn faster">
                            <i class="mdi mdi-minus mdi-24px animated fadeIn faster"></i>
                            <span class="animated fadeIn faster font-weight-normal">Transaksi</span>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center nav-sub collapsed" data-target="#sales" data-toggle="collapse" ng-class="{active: $state.includes('admin-trSalesRegular-recap') || $state.includes('admin-trSalesRegular-create') || $state.includes('admin-trSalesRegular-print') || $state.includes('admin-trSalesLab-recap') || $state.includes('admin-trSalesLab-create') || $state.includes('admin-trSalesLab-print') || $state.includes('admin-trSalesMix-recap') || $state.includes('admin-trSalesMix-create') || $state.includes('admin-trSalesMix-print') || $state.includes('admin-trSalesRecipe-recap') || $state.includes('admin-trSalesRecipe-create') || $state.includes('admin-trSalesRecipe-print')}" href="javascript:;">
                        <i class="mdi mdi-cash-register mdi-inherit mdi-24px animated fadeIn faster mr-3"></i>
                        <span class="animated fadeIn faster">Penjualan</span>
                    </a>
                    <div class="collapse animated fadeIn faster" id="sales" ng-class="{'show': $state.includes('admin-trSalesRegular-recap') || $state.includes('admin-trSalesRegular-create') || $state.includes('admin-trSalesRegular-print') || $state.includes('admin-trSalesLab-recap') || $state.includes('admin-trSalesLab-create') || $state.includes('admin-trSalesLab-print') || $state.includes('admin-trSalesMix-recap') || $state.includes('admin-trSalesMix-create') || $state.includes('admin-trSalesMix-print') || $state.includes('admin-trSalesRecipe-recap') || $state.includes('admin-trSalesRecipe-create') || $state.includes('admin-trSalesRecipe-print')}">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-trSalesRegular-create" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-trSalesRegular-recap') || $state.includes('admin-trSalesRegular-create') || $state.includes('admin-trSalesRegular-print')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Regular</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-trSalesMix-create" ui-sref-opts="{reload: true}" ng-class="{active: $state.includes('admin-trSalesMix-recap') || $state.includes('admin-trSalesMix-create') || $state.includes('admin-trSalesMix-print')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Racik</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-trSalesRecipe-create" ui-sref-opts="{reload: true}" ng-class="{active: $state.includes('admin-trSalesRecipe-recap') || $state.includes('admin-trSalesRecipe-create') || $state.includes('admin-trSalesRecipe-print')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Resep</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-trSalesLab-create" ui-sref-opts="{reload: true}" ng-class="{active: $state.includes('admin-trSalesLab-recap') || $state.includes('admin-trSalesLab-create') || $state.includes('admin-trSalesLab-print')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Lab</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a ui-sref="admin-trPurchase-create" class="nav-link d-flex align-items-center" ng-class="{active: $state.includes('admin-trPurchase-create') || $state.includes('admin-trPurchase-recap') || $state.includes('admin-trPurchase-return')}" ui-sref-opts="{reload: true}">
                        <i class="mdi mdi-basket mdi-inherit mdi-24px animated fadeIn faster mr-3"></i>
                        <span class="animated fadeIn faster">Pembelian</span>
                    </a>
                    <a class="nav-link d-flex align-items-center nav-sub" data-target="#return" data-toggle="collapse" ng-class="{active: $state.includes('admin-return-trPurchase-list') || $state.includes('admin-return-trSales-list')}" href="javascript:;">
                        <i class="mdi mdi-script-text-outline mdi-inherit mdi-24px animated fadeIn faster mr-3"></i>
                        <span class="animated fadeIn faster">Retur</span>
                    </a>
                    <div class="collapse animated fadeIn faster" id="return" ng-class="{'show': $state.includes('admin-return-trPurchase-list') || $state.includes('admin-return-trSales-list')}">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-return-trSales-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-return-trSales-list')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Penjualan</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-return-trPurchase-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-return-trPurchase-list')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Pembelian</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item nav-title">
                    <div class="nav-link d-flex align-items-center text-uppercase mt-3" href="#">
                        <div class="animated fadeIn faster">
                            <i class="mdi mdi-minus mdi-24px animated fadeIn faster"></i>
                            <span class="animated fadeIn faster font-weight-normal">Inventori</span>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center nav-sub collapsed" data-target="#inventory" data-toggle="collapse" ng-class="{active: $state.includes('admin-stockOpname-create') || $state.includes('admin-stockOpname-recap')}" href="javascript:;">
                        <i class="mdi mdi-warehouse mdi-inherit mdi-24px animated fadeIn faster mr-3"></i>
                        <span class="animated fadeIn faster">Inventori</span>
                    </a>
                    <div class="collapse animated fadeIn faster" id="inventory" ng-class="{'show': $state.includes('admin-stockOpname-create') || $state.includes('admin-stockOpname-recap')}">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-user-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-user-create') || $state.includes('admin-user-list') || $state.includes('admin-user-edit')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Barang Masuk</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-supplier-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-supplier-create') || $state.includes('admin-supplier-list') || $state.includes('admin-supplier-edit')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Barang Keluar</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-supplier-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-supplier-create') || $state.includes('admin-supplier-list') || $state.includes('admin-supplier-edit')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Stock Opname</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a ui-sref="admin-trCashBalanceDeposit-create" class="nav-link d-flex align-items-center" ng-class="{active: $state.includes('admin-trCashBalanceDeposit-create')}" ui-sref-opts="{reload: true}">
                        <i class="mdi mdi-lightbulb-on mdi-inherit mdi-24px animated fadeIn faster mr-3"></i>
                        <span class="animated fadeIn faster">POBB</span>
                    </a>
                </li>
                <li class="nav-item nav-title">
                    <div class="nav-link d-flex align-items-center text-uppercase mt-3" href="#">
                        <div class="animated fadeIn faster">
                            <i class="mdi mdi-minus mdi-24px animated fadeIn faster"></i>
                            <span class="animated fadeIn faster font-weight-normal">Laporan</span>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center nav-sub collapsed" data-target="#report" data-toggle="collapse" ng-class="{active: $state.includes('admin-medicine-create')}" href="javascript:;">
                        <i class="mdi mdi-chart-areaspline mdi-inherit mdi-24px animated fadeIn faster mr-3"></i>
                        <span class="animated fadeIn faster">Laporan</span>
                    </a>
                    <div class="collapse animated fadeIn faster" id="report" ng-class="{'show': $state.includes('admin-medicine-create')}">
                        {{-- <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" ui-sref="admin-medicine-list" ui-sref-opts="{reload: true}"  ng-class="{active: $state.includes('admin-medicine-create') || $state.includes('admin-medicine-list') || $state.includes('admin-medicine-edit')}">
                                    <i class="mdi mdi-square-small mdi-24px mr-3"></i>
                                    <span class="animated fadeIn faster">Regular</span>
                                </a>
                            </li>
                        </ul> --}}
                    </div>
                </li>
            </ul>
        </div>
    </aside>
    <div class="backdrop d-none"></div>
    <!-- End Sidebar -->
    <!-- Nav Bar -->
    <nav class="navbar navbar-expand-lg fixed-top pl-sm-4 pl-sm-4 pr-sm-4">
        <a class="navbar-brand mr-0 d-flex align-items-center justify-content-center d-sm-none" href="#">
            <img src="{{ asset('backend/img/logo/logo-sq.png') }}" width="24" height="24" class="d-inline-block align-top mr-0" alt="">
        </a>
        <a href="javascript:;" id="toggle-bar" class="d-none d-sm-block">
            <div class="strip"></div>
            <div class="strip"></div>
            <div class="strip"></div>
        </a>
        <!-- <a href="javascript:;" id="toggle-bar-responsive" class="d-block d-sm-none">
            <div class="strip"></div>
            <div class="strip"></div>
            <div class="strip"></div>
        </a> -->
        <ul class="nav justify-content-end">
            {{-- <li class="nav-item dropdown d-inline-flex align-items-center mr-1">
                <div class="d-flex justify-content-center align-items-center text-gray-600">
                    <div class="text-right" ng-include="'../resources/views/templates/backend/master/cashBalances/cashBalance.html'"></div>
                    <img src="{{ asset('backend/img/icon/cash.png') }}" width="24" height="24" class="ml-2 animated fadeIn faster" alt="">
                </div>   
            </li> --}}
            <li class="nav-item dropdown d-inline-flex align-items-center">
                <a class="nav-link pt-0 pb-0 pr-0 dropdown-toggle d-flex align-items-center d-flex align-items-center justify-content-end" href="javascript:;" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false">
                    <img src="{{ asset('backend/img/icon/account.png') }}" width="24" height="24" class="mr-2 animated fadeIn faster" alt="">
                    <span class="text-icon text-capitalize">{{ Auth::user()->username }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg-right animated fadeIn faster">
                    {{-- <a class="dropdown-item d-flex align-items-center" href="#"><i class="mdi mdi-account-edit-outline mdi-24px mr-3"></i>Profile</a>
                    <a class="dropdown-item d-flex align-items-center" href="#"><i class="mdi mdi-email-outline mdi-24px mr-3"></i>Messages</a>
                    <a class="dropdown-item d-flex align-items-center" href="#"><i class="mdi mdi-settings-outline mdi-24px mr-3"></i>Settings</a>
                    <div class="dropdown-divider"></div> --}}
                    <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="mdi mdi-logout mdi-20px mr-2"></i>
                        {{ __('Logout') }}
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </a>
                </div>
            </li>
            <li class="nav-item dropdown d-block d-sm-none">
                <a class="nav-link pt-0 pb-0 pr-0 dropdown-toggle d-flex align-items-center d-flex align-items-center justify-content-end" href="javascript:;" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false" id="toggle-bar-responsive">
                    <i class="mdi mdi-dots-vertical mdi-24px ml-2"></i>
                </a>
            </li>
        </ul>
    </nav>
    <div class="content p-4" fullpage>
        <ui-view></ui-view>
    </div>
    <footer class="footer d-flex align-items-center justify-content-center">
        <div class="copyright text-center">
            © {{ date('Y') }}&nbsp;<span class="font-weight-bold">Apotek Pulosari</span>. All rights reserved.
        </div>
    </footer>
    <div class="box-task elevation-smooth1 rounded-left">
        <div class="box-task-header bg-gradient-primary text-center position-relative">
            <h6 class="text-white mb-0">Daftar Tugas</h6>
            <a href="javascript:;" class="task-close">
                <i class="mdi mdi-close mdi-18px"></i>
            </a>
        </div>
        <div class="box-task-body position-relative">
        </div>
    </div>
    {{-- <button class="box-task-toggle btn-fl-gradient-primary float-br animated zoomIn faster waves-effect waves-dark" id="toggle-task">
        <i class="mdi mdi-script-text-outline mdi-24px"></i>
    </button> --}}
	{{-- Scripts --}}
	<script src="{{ asset('plugin/bower_components/jquery/dist/jquery.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/jquery-ui/jquery-ui.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/popper.js/dist/umd/popper.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/jquery.preload-master/jquery.preload.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/tinymce/tinymce.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/stickyTable/stickyTable.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/perfect-scrollbar/dist/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('plugin/bower_components/sweetalert/dist/sweetalert.min.js') }}"></script>
	{{-- Start Angular Libraries --}}
	<script src="{{ asset('plugin/bower_components/angular/angular.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/ui-autocomplete/autocomplete.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-ui-date/dist/date.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-sanitize/angular-sanitize.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-route/angular-route.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-ui-router/release/angular-ui-router.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-animate/angular-animate.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-aria/angular-aria.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-messages/angular-messages.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-sticky/dist/angular-sticky.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-toggle/angular-toggle.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-tabs/angular-tabs.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-filter/dist/angular-filter.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-ui-tinymce/src/tinymce.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-toastr/dist/angular-toastr.tpls.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-loading-bar/build/loading-bar.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-ui-select/dist/select.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-i18n/angular-i18n.min.js') }}"></script>
    <script src="{{ asset('plugin/bower_components/angular-file-upload/dist/angular-file-upload.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-input-masks/angular-input-masks-standalone.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/ngmap/build/scripts/ng-map.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/moment/min/moment.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/chart.js/dist/Chart.min.js') }}"></script>
	<script src="{{ asset('plugin/bower_components/angular-chart.js/dist/angular-chart.min.js') }}"></script>
	{{-- End Angular Libraries --}}
	{{-- Start Directive --}}
	<script src="{{ asset('plugin/bower_components/angularUtils-pagination/dirPagination.js') }}"></script>
	<script src="{{ asset('angularjs/base/pluginDirective.js') }}"></script>
	{{-- End Directive --}}

	{{-- Master Controller --}}
	<script src="{{ asset('angularjs/base/pluginController.js') }}"></script>
    <script src="{{ asset('angularjs/base/globalController.js') }}"></script>
    <script src="{{ asset('angularjs/base/baseController.js') }}"></script>
	<script src="{{ asset('angularjs/master/medicines/medicinesController.js') }}"></script>
	<script src="{{ asset('angularjs/master/patients/patientsController.js') }}"></script>
	<script src="{{ asset('angularjs/master/users/usersController.js') }}"></script>
	<script src="{{ asset('angularjs/master/suppliers/suppliersController.js') }}"></script>
	<script src="{{ asset('angularjs/master/categories/categoriesController.js') }}"></script>
	<script src="{{ asset('angularjs/master/units/unitsController.js') }}"></script>
    {{-- Transaction Controller --}}
    <script src="{{ asset('angularjs/transaction/trSales/trSalesRegular/trSalesRegularController.js') }}"></script>
    <script src="{{ asset('angularjs/transaction/trSales/trSalesRecipe/trSalesRecipeController.js') }}"></script>
    <script src="{{ asset('angularjs/transaction/trSales/trSalesLab/trSalesLabController.js') }}"></script>
    <script src="{{ asset('angularjs/transaction/trSales/trSalesMix/trSalesMixController.js') }}"></script>
    <script src="{{ asset('angularjs/transaction/trPurchase/trPurchaseController.js') }}"></script>
    <script src="{{ asset('angularjs/transaction/trPurchase/trPurchaseController.js') }}"></script>
    <script src="{{ asset('angularjs/returns/returnsController.js') }}"></script>
	{{-- Master Factory --}}
	<script src="{{ asset('angularjs/master/medicines/medicinesFactory.js') }}"></script>
	<script src="{{ asset('angularjs/master/patients/patientsFactory.js') }}"></script>
	<script src="{{ asset('angularjs/master/users/usersFactory.js') }}"></script>
	<script src="{{ asset('angularjs/master/suppliers/suppliersFactory.js') }}"></script>
	<script src="{{ asset('angularjs/master/categories/categoriesFactory.js') }}"></script>
	<script src="{{ asset('angularjs/master/units/unitsFactory.js') }}"></script>
    {{-- Transaction Factory --}}
    <script src="{{ asset('angularjs/transaction/trSales/trSalesRegular/trSalesRegularFactory.js') }}"></script>
    <script src="{{ asset('angularjs/transaction/trSales/trSalesRecipe/trSalesRecipeFactory.js') }}"></script>
    <script src="{{ asset('angularjs/transaction/trSales/trSalesLab/trSalesLabFactory.js') }}"></script>
    <script src="{{ asset('angularjs/transaction/trSales/trSalesMix/trSalesMixFactory.js') }}"></script>
    <script src="{{ asset('angularjs/transaction/trPurchase/trPurchaseFactory.js') }}"></script>
    <script src="{{ asset('angularjs/returns/returnsFactory.js') }}"></script>
    <!-- The core Firebase JS SDK is always required and must be listed first -->
    <script src="https://www.gstatic.com/firebasejs/7.18.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.18.0/firebase-analytics.js"></script>
    {{-- Global --}}
    <script src="{{ asset('plugin/bower_components/firebase/firebase.js') }}"></script>
    <script src="{{ asset('plugin/bower_components/angularfire/dist/angularfire.min.js') }}"></script>
	<script src="{{ asset('backend/js/style.js') }}"></script>
	<script src="{{ asset('angularjs/app.js') }}"></script>
    <script>
        // Your web app's Firebase configuration
        var firebaseConfig = {
            apiKey: "AIzaSyBbkUWwgaYufk36mBq3TJb9UeEhtSOIgnE",
            authDomain: "apotik-pulosari.firebaseapp.com",
            databaseURL: "https://apotik-pulosari.firebaseio.com",
            projectId: "apotik-pulosari",
            storageBucket: "apotik-pulosari.appspot.com",
            messagingSenderId: "778916407390",
            appId: "1:778916407390:web:00c812d064664a7fb7eccf",
            measurementId: "G-KF3MGT61SX"
        };
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        // firebase.analytics();
    </script>
</body>
</html>
