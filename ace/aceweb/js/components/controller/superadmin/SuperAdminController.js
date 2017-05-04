angular.module('aceWeb')


.controller('SuperAdminController', function(config, $scope, $http, $state, AuthService)
{
  $scope.logout = function()
  {
    AuthService.logout();
  }

  $scope.downloadUserManual = function()
  {
    $scope.downloadLink = config.apiUrl + '/auth/downloadUserManual/' + AuthService.getRole();
  }

  $scope.initScope = function()
  {
    $scope.userName = AuthService.getName();
  }

  $scope.initScope();
})
