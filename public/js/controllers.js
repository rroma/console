'use strict';

var consoleApp = angular.module('consoleApp', ['ui.ace']);
consoleApp.controller('ScriptListCtrl', ['$scope', '$http', function($scope, $http) {
    $http.get('scripts/scripts.json').success(function(data) {
        if (data === '') {
            data = [];
        }
        $scope.scripts = data;
        $scope.tabs = [];
        $scope.activeTab = null;
        $scope.recentTabs = [];
        $scope.newScriptWithTab();
    });
    $scope.save = function() {
        if ($scope.scripts.indexOf($scope.activeTab) == -1) {
            for (var i = 0; i < $scope.scripts.length; i++) {
                if ($scope.scripts[i].name === $scope.activeTab.script.name) {
                    // TODO refactor
                    alert('Script with name '+$scope.activeTab.script.name+'already exists!');
                    return;
                }
            }
            $scope.scripts.push($scope.activeTab.script);
            $http.post('server/save.php', $scope.scripts)
                .success(function(data){
                    alert('zal');
                })
                .error(function(data){
                    alert('peth');
                });
        }
    }
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
        } else {
            $scope.newScriptWithTab();
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
        var tab = { "script": script };
        $scope.tabs.push(tab);
        $scope.selectTab(tab);
    }
    $scope.openScript = function(script) {
        var tab = null;
        $scope.tabs.forEach(function(item) {
            if (item.script === script) {
                tab = item;
            }
        });
        if (tab) {
            $scope.selectTab(tab);
            return;
        }
        var tab = { "script": script };
        $scope.tabs.push(tab);
        $scope.selectTab(tab);
    }
    $scope.getNewScriptName = function() {
        return "New script";
    };
}]);