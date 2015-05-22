'use strict';

var consoleApp = angular.module('consoleApp', ['ui.ace', 'consoleDirectives', 'ngAnimate', 'consoleFilters']);
consoleApp.factory('notify', ['$rootScope', function( $rootScope ) {
    return {
        mess: {},
        show: function(item, type){
            var self = this;
            this.mess = {
                text: item,
                type: type
            };
            setTimeout(function(){
                self.mess = null;
                $rootScope.$apply()
            }, 2000);
        }
    };
}]);
consoleApp.factory('uuid', [function() {
    return {
        s4: function () {
            return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
        },
        generate: function () {
            return (this.s4() + this.s4() + "-" + this.s4() + "-4" + this.s4().substr(0, 3) + "-" + this.s4() + "-" + this.s4() + this.s4() + this.s4()).toLowerCase();
        }
    };
}]);
consoleApp.controller('NotificationController', ['$scope', 'notify', function($scope, notify){
    $scope.notification = notify;
}]);
consoleApp.controller('ScriptListCtrl', ['$scope', 'notify', '$http', 'uuid', function($scope, notify, $http, uuid) {
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
        var selectedScript = $scope.activeTab.script;
        // unique name validation
        for (var i = 0; i < $scope.scripts.length; i++) {
            if ($scope.scripts[i] === selectedScript) {
                continue;
            }
            if ($scope.scripts[i].name === selectedScript.name) {
                notify.show('Fail: script with name "'+selectedScript.name+'" already exists', 'error');
                return;
            }
        }

        if (!selectedScript.id) {
            selectedScript.id = uuid.generate();
        }

        $scope.processing = true;
        $http.post('server/save.php', selectedScript)
            .success(function (data) {
                $scope.processing = false;
                if (data.success) {
                    notify.show('Success: saved', 'success');
                    if ($scope.scripts.indexOf(selectedScript) == -1) {
                        $scope.scripts.push(selectedScript);
                    }
                } else {
                    if ($scope.scripts.indexOf(selectedScript) == -1) {
                        selectedScript.id = "";
                    }
                    notify.show('Fail: unable to save script', 'error');
                }
            })
            .error(function (data) {
                $scope.processing = false;
                selectedScript.id = "";
                notify.show('Fail: server error', 'error');
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
                notify.show('Fail: unable to execute script', 'error');
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
            $scope.removeFromRecentTabs(tab);
            var recent = $scope.recentTabs.pop();
            if (recent) {
                $scope.selectTab(recent);
            } else {
                $scope.newScriptWithTab();
            }
            $scope.tabs.splice(idx, 1);
        }
    }
    $scope.createScript = function() {
        return {
            "id": "",
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