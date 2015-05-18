'use strict';

var consoleApp = angular.module('consoleApp', ['ui.ace', 'consoleDirectives']);
consoleApp.controller('ScriptListCtrl', ['$scope', '$rootScope', '$http', function($scope, $rootScope, $http) {
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
        // unique name validation
        for (var i = 0; i < $scope.scripts.length; i++) {
            if ($scope.scripts[i] === $scope.activeTab.script) {
                continue;
            }
            if ($scope.scripts[i].name === $scope.activeTab.script.name) {
                $rootScope.$broadcast('duplicatedNameEvent', $scope.activeTab.script);
                // TODO refactor
                //alert('Script with name ' + $scope.activeTab.script.name + 'already exists!');
                return;
            }
        }
        if ($scope.scripts.indexOf($scope.activeTab.script) == -1) {
            $scope.scripts.push($scope.activeTab.script);
        }

        $http.post('server/save.php', $scope.scripts)
            .success(function (data) {
                //TODO refactor
                alert('Saved');
            })
            .error(function (data) {
                //TODO refactor
                alert('Error');
            });
    }
    $scope.execute = function() {
        $http.post('server/execute.php', $scope.activeTab.script)
            .success(function (data) {

                var $output = angular.element(document.querySelector('#output'));
                $output.text(data.result.output);
            })
            .error(function (data) {

            });
    };
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
    $scope.editName = function($event) {
        $event.stopPropagation();
    }
}]);