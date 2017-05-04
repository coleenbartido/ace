angular.module('aceWeb')


.controller('FacultyController', function(config, $scope, $http, $state, $localStorage, AuthService, $rootScope, $interval)
{
  $scope.logout = function()
  {
    AuthService.logout();
  }

  $scope.downloadUserManual = function()
  {
    $scope.downloadLink = config.apiUrl + '/auth/downloadUserManual/' + AuthService.getRole();
  }

  $rootScope.getNotif = function()
  {
    var accountDetails =
    {
      'email' : AuthService.getEmail()
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/getNotifList',
      data: accountDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.isLoading = false;

      $rootScope.referralUpdateCount = response.data.referralUpdateCount;
      $rootScope.newMessageCount = response.data.newMessageCount;
    },
    function(response)
    {
      //for checking
      console.log(response);

    })
    .finally(function()
    {

    });
  }

  $scope.initScope = function()
  {
    $scope.isLoading = true;
    $scope.userName = AuthService.getName();
    $rootScope.getNotif();
  }

  $scope.initScope();

  $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);

  $scope.$on('$destroy',function()
  {
    if($rootScope.notifPoll)
    {
      $interval.cancel($rootScope.notifPoll);
    }
  })
})
