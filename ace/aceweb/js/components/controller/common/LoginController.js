angular.module('aceWeb')


.controller('LoginController', function(config, $scope, $http, $state, $localStorage, AuthService)
{
  $scope.initScope = function()
  {
    $scope.incorrectInput = false;
    $scope.invalidInput = false;
    $scope.disableLogin = false;
    $scope.loginText = "SIGN IN";
  }

  $scope.initScope();

  $scope.login = function(loginForm)
  {
    $scope.incorrectInput = false;
    $scope.invalidInput = false;

    if(loginForm.$valid)
    {
      $scope.disableLogin = true;
      $scope.loginText = "SIGNING IN";

      var loginDetails =
      {
        'email' : $scope.userEmail,
        'pword' : $scope.userPassword
      };

      $http({
        method: 'POST',
        url: config.apiUrl + '/login',
        data: loginDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response) //eto yung gagawin ng function mo after mo makuha yung response from server
      {
        //success callback (handles all the success response 2xx status codes)
        console.log(response); //for checking

        AuthService.storeUserCredentials(response.data.token);

        if(AuthService.getRole() == 3)
        {
          $state.go('faculty.referralForm');
        }
        else if(AuthService.getRole() == 2)
        {
          $state.go('admin.reports');
        }
        else if(AuthService.getRole() == 1)
        {
          $state.go('superadmin.manageAdmin');
        }
      },
      function(response)
      {
        //error callback (handles all the error response 4xx status codes)
        console.log(response);

        $scope.errMsg = response.data.errMsg;
        $scope.incorrectInput = true;
        $scope.userPassword = undefined;

        $scope.disableLogin = false;
        $scope.loginText = "SIGN IN";
      })
      .finally(function()
      {
        //things to handle whether the response is success or not (ex: disable or hide loading)

      })
    }
    else
    {
      $scope.errMsg = "Invalid Email or Password";
      $scope.invalidInput = true;
    }
  }
})