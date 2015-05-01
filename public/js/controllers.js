'use strict';

var consoleApp = angular.module('consoleApp', []);
consoleApp.controller('ScriptListCtrl', ['$scope', '$http', function($scope, $http) {
    $http.get('scripts/scripts.json').success(function(data) {
        $scope.scripts = data;
        $scope.tabs = [];
        $scope.activeTab = null;
        $scope.recentTabs = [];
        $scope.newScriptWithTab();
    });
    $scope.selectTab = function(tab) {
        $scope.activeTab = tab;
        $scope.pushToRecentTabs(tab);
    }
    $scope.pushToRecentTabs = function(tab) {
        $scope.removeFromRecentTabs(tab);
        $scope.recentTabs.push(tab);
    }
    $scope.removeFromRecentTabs = function(tab) {
        var idx = $scope.recentTabs.indexOf(tab);
        if (idx > -1) {
            $scope.recentTabs.splice(idx, 1);
        }
    }
    $scope.removeTab = function(tab) {
        var idx = $scope.tabs.indexOf(tab);
        if (idx > -1) {
            $scope.tabs.splice(idx, 1);
        }
        $scope.removeFromRecentTabs(tab);
        var recent = $scope.recentTabs.pop();
        if (recent) {
            $scope.selectTab(recent);
        }
    }
    $scope.createScript = function() {
        return {
            "name": $scope.getNewScriptName(),
            "code": ""
        };
    }
    $scope.newScriptWithTab = function() {
        var script = $scope.createScript();
        $scope.scripts.push(script);
        var tab = { "script": script };
        $scope.tabs.push(tab);
        $scope.selectTab(tab);
    }
    $scope.getNewScriptName = function() {
        var baseName = "New script ";
        var num = "";
        $scope.scripts.forEach(function(item){
            var match = item.name.match(/^New\sscript(\s[0-9]+)?$/);
            if (match) {
                num = 1 in match && match[1] ? parseInt(match[1]) + 1 : 1;
            }
        });

        return (baseName+num).trim();
    };
}]);