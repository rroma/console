'use strict';

var consoleApp = angular.module('consoleApp', []);
consoleApp.controller('ScriptListCtrl', ['$scope', '$http', function($scope, $http) {
    $http.get('scripts/scripts.json').success(function(data) {
        $scope.scripts = data;
    });
}]);