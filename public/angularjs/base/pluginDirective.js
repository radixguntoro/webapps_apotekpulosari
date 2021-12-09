var app = angular.module('pluginDrtv', []);
var windowHeight = $(window).outerHeight();
var headerHeight = $("header").outerHeight();

app.directive('zurbinit', [function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            $(document).foundation();
        }
    };
}]).directive('fullpage', [function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            windowHeight = $(window).outerHeight();
            headerHeight = $(".navbar").height();
            footerHeight = $(".footer").height();
            contentHeight = windowHeight - headerHeight - footerHeight - 16;
            $(".content").css("min-height", contentHeight);
        }
    };
}]).directive('fullbody', [function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            windowHeight = $(window).height();
            contentHeight = windowHeight - 56;
            $(".vmd-content").css("height", contentHeight);
        }
    };
}]).directive('stickytable', [function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            theadWidth = $(".vmd-table").outerWidth();
            $(".sticky-header").css("width", theadWidth);
        }
    };
}]).directive('fullheight', [function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            var windowHeight = $(window).outerHeight();
            var sidebarHeight = $(".vmd-adm-header").outerHeight();
            var footerHeight = $("footer").outerHeight();
            var contentHeight = windowHeight - sidebarHeight - footerHeight - 24;
            $(".vmd-adm-page").css("min-height", contentHeight);
        }
    };
}]).directive('fullloader', [function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            var windowHeight = $(window).outerHeight();
            var sidebarHeight = $(".vmd-adm-header").outerHeight();
            var footerHeight = $("footer").outerHeight();
            var contentHeight = windowHeight - sidebarHeight - footerHeight - 24;
            $(".vmd-adm-loader").css("min-height", contentHeight);
        }
    };
}]).directive('fulltable', [function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            var windowHeight = $(window).outerHeight();
            var sidebarHeight = $(".vmd-adm-header").outerHeight();
            var footerHeight = $("footer").outerHeight();
            var subHead = $(".vmd-page-header").outerHeight();
            var subNeck = $(".vmd-page-subheader").outerHeight();
            var subFoot = $(".vmd-page-footer").outerHeight();
            var contentHeight = windowHeight - sidebarHeight - footerHeight - subHead - subNeck - subFoot - 60 - 24;
            $(".vmd-box-table").css("min-height", contentHeight);
        }
    };
}]).directive('stickytable', [function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            $(document).trigger("stickyTable");
        }
    };
}]).directive('squarecol', [function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            var widthCol = $(".vmd-box-square").innerWidth();
            var contentHeight = widthCol;
            $(".vmd-box-square").css("height", contentHeight);
            // $(".vmd-box-square-get").css("height", contentHeight + 37);
        }
    };
}]).directive('ngThumb', ['$window', function ($window) {
    var helper = {
        support: !!($window.FileReader && $window.CanvasRenderingContext2D),
        isFile: function (item) {
            return angular.isObject(item) && item instanceof $window.File;
        },
        isImage: function (file) {
            var type = '|' + file.type.slice(file.type.lastIndexOf('/') + 1) + '|';
            return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
        }
    };

    return {
        restrict: 'A',
        template: '<canvas/>',
        link: function (scope, element, attributes) {
            if (!helper.support) return;

            var params = scope.$eval(attributes.ngThumb);

            if (!helper.isFile(params.file)) return;
            if (!helper.isImage(params.file)) return;

            var canvas = element.find('canvas');
            var reader = new FileReader();

            reader.onload = onLoadFile;
            reader.readAsDataURL(params.file);

            function onLoadFile(event) {
                var img = new Image();
                img.onload = onLoadImage;
                img.src = event.target.result;
            }

            function onLoadImage() {
                var width = params.width || this.width / this.height * params.height;
                var height = params.height || this.height / this.width * params.width;
                canvas.attr({
                    width: width,
                    height: height
                });
                canvas[0].getContext('2d').drawImage(this, 0, 0, width, height);
            }
        }
    };
}]).directive('autocompitem', [function ($parse, $location) {
    return {
        restrict: 'E',
        replace: true,
        template: '<input type="text" id="search-item">',
        link: function (scope, element, attrs) {
            function rupiah(val) {
                var number_string = val.toString();
                var sisa = number_string.length % 3;
                var rupiah = number_string.substr(0, sisa);
                var ribuan = number_string.substr(sisa).match(/\d{3}/g);

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                return rupiah;
            }
            scope.$watch(attrs.selection, function (selection) {
                // event when select item
                element.on("autocompleteselect", function (e, ui) {
                    e.preventDefault(); // prevent the "value" being written back after we've done our own changes
                    this.value = ui.item.name;
                });

                element.autocomplete({
                        source: scope.readMedicineByAutoComplete,
                        minLength: 3,
                        select: function (event, ui) {
                            event.preventDefault();
                            scope.addCart(ui.item);
                            scope.barcodeAdd = '';
                            angular.element('#search-item').val('');
                        }
                    })
                    .focus(function () {
                        $(this).autocomplete("search");
                    })
                    .data("ui-autocomplete")._renderItem = function (ul, item) {
                        console.log("Hasil", item);
                        var regex = new RegExp(this.term, "gi");
                        let displayBox = item.boxQty > 0 ? 'd-block' : 'd-none';

                        if (item.id == 0) {
                            return $("<li class=''></li>")
                                .data("item.autocomplete", item)
                                .append("<div class='text-center text-capitalize'>" + item.name + "</div>")
                                .appendTo(ul);
                        } else {
                            return $("<li class=''></li>")
                                .data("item.autocomplete", item)
                                .append(
                                    "<div>"
                                        + "<a>" +
                                            "<div>" + item.name.replace(regex, "<span class='font-weight-bold text-uppercase'>" + this.term + "</span>") + 
                                                "<div>"+
                                                    "<span> Harga:"+  
                                                        "<span class='font-weight-bold text-blue'> Rp. " + rupiah(item.tabletPriceSell) + "</span><span class='" + displayBox + "'> per Biji</span>" +
                                                    "</span>"
                                                +"</div>" 
                                            + "</div>"
                                            + "<div class='text-capitalize'>" + "Stok:&nbsp;" + 
                                                "<span class='badge badge-gray-300 py-1 px-2'>&nbsp;" + 
                                                    rupiah(item.qtyTotal) 
                                                + "&nbsp;Biji</span>"
                                            + "</div>"
                                        + "</a>" +
                                    "</div>"
                                )
                                .appendTo(ul);
                        }
                    };
            });
        }
    };
}]).directive('autocompsupp', [function ($parse, $location) {
    return {
        restrict: 'E',
        replace: true,
        template: '<input type="text" id="search-supplier">',
        link: function (scope, element, attrs) {
            scope.$watch(attrs.selection, function (selection) {
                // event when select item
                element.on("autocompleteselect", function (e, ui) {
                    e.preventDefault(); // prevent the "value" being written back after we've done our own changes
                    this.value = ui.item.name;
                });

                element.autocomplete({
                        source: scope.readSupplierByAutoComplete,
                        minLength: 3,
                        select: function (event, ui) {
                            event.preventDefault();
                            scope.setSupplier(ui.item);
                            angular.element('#search-supplier').val('');
                        }
                    })
                    .focus(function () {
                        $(this).autocomplete("search");
                    })
                    .data("ui-autocomplete")._renderItem = function (ul, item) {
                        console.log(item);
                        var regex = new RegExp(this.term, "gi");
                        if (item.id == 0) {
                            return $("<li class=''></li>")
                                .data("item.autocomplete", item)
                                .append("<div class='text-center text-capitalize'>" + item.name + "</div>")
                                .appendTo(ul);
                        } else {
                            return $("<li class=''></li>")
                                .data("item.autocomplete", item)
                                .append(
                                    "<div>"
                                        + "<a>" +
                                            "<div>" + item.name.replace(regex, "<span class='font-weight-bold text-uppercase'>" + this.term + "</span>")
                                        + "</a>" +
                                    "</div>"
                                )
                                .appendTo(ul);
                        }
                    };
            });
        }
    };
}]);
