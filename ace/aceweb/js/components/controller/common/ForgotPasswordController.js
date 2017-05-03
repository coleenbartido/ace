angular.module('aceWeb')


.controller('ForgotPasswordController', function(config, $scope, $http, $state, $location)
{
  $scope.initScope = function()
  {
    $scope.showResetPassForm = false;

    $scope.incorrectInput = false;
    $scope.invalidInput = false;
    $scope.validEmail = false;
    $scope.pwordChanged = false;
    $scope.invalidPword = false;
    $scope.disableLogin = false;
    $scope.resetText = "SEND REQUEST";
    $scope.resetText2 = "RESET PASSWORD";
  }

  $scope.initScope();

  $scope.verifyEmail = function(loginForm)
  {
    $scope.incorrectInput = false;
    $scope.invalidInput = false;
    $scope.validEmail = false;

    if(loginForm.$valid)
    {
      $scope.disableLogin = true;
      $scope.resetText = "SENDING REQUEST";

      var resetDetails =
      {
        'email' : $scope.userEmail
      };

      $http({
        method: 'POST',
        url: config.apiUrl + '/forgotPassword',
        data: resetDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $scope.validEmail = true;
      },
      function(response)
      {
        //for checking
        console.log(response);

        if(response.status == 400)
        {
          $scope.errMsg = response.data.errMsg;
        }
        else
        {
          $scope.errMsg = "Web service unavailable";
        }
        $scope.incorrectInput = true;
      })
      .finally(function()
      {
        $scope.disableLogin = false;
        $scope.resetText = "SEND REQUEST";
      });
    }
    else
    {
      $scope.errMsg = "Invalid Email";
      $scope.invalidInput = true;
    }
  }

  $scope.verifyURL = function()
  {
    var verifyData =
    {
      'email' : $location.search()['email'],
      'hashcode' : $location.search()['hashcode']
    };

    $http({
      method: 'POST',
      url: config.apiUrl + '/verifyToken',
      data: verifyData,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.showResetPassForm = true;
    },
    function(response)
    {
      //for checking
      console.log(response);

      if(response.status == 400)
      {
        $state.go('errorInvalidLink');
      }
    })
    .finally(function()
    {

    });
  } //closing tag verifyURL

  $scope.confirmReset = function(loginForm)
  {
    $scope.incorrectInput = false;
    $scope.invalidInput = false;
    $scope.pwordChanged = false;
    $scope.invalidPword = false;

    if(loginForm.$valid)//validation if password and password confirmation field match
    {
      $scope.disableLogin = true;
      $scope.resetText2 = "RESETTING PASSWORD";

      var resetPassDetails =
      {
        'email' : $location.search()['email'],
        'hashcode' : $location.search()['hashcode'],
        'pword' : $scope.userPassword
      };

      $http({
        method: 'POST',
        url: config.apiUrl + '/resetPassword',
        data: resetPassDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $scope.pwordChanged = true;
      },
      function(response)
      {
        //for checking
        console.log(response);

        if(response.status == 400)
        {
          $scope.errMsg = response.data.errMsg;
        }
        else
        {
          $scope.errMsg = "Web service unavailable";
        }
        $scope.incorrectInput = true;
      })
      .finally(function()
      {
        $scope.disableLogin = false;
        $scope.resetText2 = "RESET PASSWORD";
      });
    }
    else
    {
      if(loginForm.password.$invalid)
      {
        $scope.errMsg = "Invalid Password";
        $scope.invalidInput = true;
      }
      else
      {
        $scope.errMsg = "Password don't match";
        $scope.invalidPword = true;
      }
    }
  }

})