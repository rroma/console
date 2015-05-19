'use strict';

var consoleApp = angular.module('consoleApp', ['ui.ace', 'consoleDirectives', 'ngAnimate', 'consoleFilters']);
consoleApp.factory('notify', ['$rootScope', function( $rootScope ) {
    return {
        mess: null,
        show: function(item){
            var self = this;
            this.mess = item;
            setTimeout(function(){
                self.mess = null;
                $rootScope.$apply()
            }, 2000);
        }
    };
}]);
consoleApp.controller('NotificationController', ['$scope', 'notify', function($scope, notify){
    $scope.notification = notify;
}]);
consoleApp.controller('ScriptListCtrl', ['$scope', 'notify', '$http', function($scope, notify, $http) {
    $http.get('scripts/scripts.json').success(function(data) {
        if (data === '') {
            data = [];
        }
        $scope.scripts = data;
        $scope.tabs = [];
        $scope.activeTab = null;
        $scope.recentTabs = [];
        $scope.newScriptWithTab();

        $scope.HTMLOutput = false;
        $scope.result = false;
    });
    $scope.save = function() {
        // unique name validation
        for (var i = 0; i < $scope.scripts.length; i++) {
            if ($scope.scripts[i] === $scope.activeTab.script) {
                continue;
            }
            if ($scope.scripts[i].name === $scope.activeTab.script.name) {
                notify.show('Fail: script with name "'+$scope.activeTab.script.name+'" already exists');
                return;
            }
        }
        if ($scope.scripts.indexOf($scope.activeTab.script) == -1) {
            $scope.scripts.push($scope.activeTab.script);
        }

        $scope.processing = true;
        $http.post('server/save.php', $scope.scripts)
            .success(function (data) {
                $scope.processing = false;
                notify.show('Success: saved');
            })
            .error(function (data) {
                notify.show('Fail: unable to save script');
                $scope.processing = false;
            });
    }
    $scope.execute = function() {
        $http.post('server/execute.php', $scope.activeTab.script)
            .success(function (data) {
                var $output = angular.element(document.querySelector('#output'));
                $scope.result = data.result;
                if ($scope.HTMLOutput) {
                    $output.html(data.result.output);
                } else {
                    $output.text(data.result.output);
                }
            })
            .error(function (data) {
                notify.show('Fail: unable to execute script');
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