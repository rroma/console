'use strict';
/* App Module */
var consoleApp = angular.module('consoleApp', []);

consoleApp.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
            when('/scripts', {
                templateUrl: 'partials/phone-list.html',
                controller: 'PhoneListCtrl'
            }).
            when('/execute', {
                templateUrl: 'partials/phone-detail.html',
                controller: 'PhoneDetailCtrl'
            });
    }]);