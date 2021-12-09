var app = angular.module('globalCtrl', ['factoryUsers']);

app.controller('globalController', function ($scope, usersFactory) {
    $scope.user_login = {
        users_persons_id: '',
        roles_id: '',
        username: '',
    }
    usersFactory.readLogin().then(function() {
        $scope.user_login = {
            users_persons_id: usersFactory.result_data.users_persons_id,
            roles_id: usersFactory.result_data.roles_id,
            username: usersFactory.result_data.username,
        }
        console.log($scope.user_login);
    });
}).filter('sumOfValue', function () {
    return function (data, key) {
        if (angular.isUndefined(data) || angular.isUndefined(key))
            return 0;
        let sum = 0;
        angular.forEach(data, function (value) {
            sum += parseInt(value[key]);
        });
        return sum;
    }
});
