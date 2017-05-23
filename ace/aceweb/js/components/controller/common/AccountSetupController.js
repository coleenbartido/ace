angular.module('aceWeb')


.controller('AccountSetupController', function(config, $scope, $http, $state, $location)
{
  $scope.initScope = function()
  {
    $scope.showAccountSetupForm = false;

    $scope.invalidInput = false;
    $scope.accountActivated = false;

    $scope.disableLogin = false;
    $scope.activateText = "ACTIVATE ACCOUNT";
  }

  $scope.initScope();

  $scope.verifyURL = function()
  {
    if($location.search()['email'] && $location.search()['hashcode']) // check if both the url parameters exist
    {
      var verifyData =
      {
        'email' : $location.search()['email'], //get the value of one of the parameter in the current url
        'hashCode' : $location.search()['hashcode'] //get the value of one of the parameter in the current url
      };

      $http({
        method: 'GET',
        url: config.apiUrl + '/verify',
        params: verifyData, //instead of data, we pass the verifyData object as a parameter in url since we're using GET instead of POST. Thus, the url should be like this ../../../ace/webservice/public/verify?email="INSERT VALUE HERE"&hashCode="INSERT VALUE HERE".
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $scope.showAccountSetupForm = true;
      },
      function(response)
      {
        //for checking
        console.log(response);

        $state.go('errorInvalidLink');
      })
      .finally(function()
      {

      });
    }
    else
    {
      //for checking
      $state.go('errorInvalidLink'); ////go to error page (tell the user that the link is invalid)
    }
  };

  $scope.submitFurtherDetails = function(form)
  {
    $scope.invalidInput = false;
    $scope.accountActivated = false;

    if(form.$valid)
    {
      $scope.disableLogin = true;
      $scope.activateText = "ACTIVATING ACCOUNT";

      var accountDetails =
      {
        'email' : $location.search()['email'], //get the value of one of the parameter in the current url
        'hashCode' : $location.search()['hashcode'], //get the value of one of the parameter in the current url
        'contactNumber' : $scope.userContactNumber,
        'pword' : $scope.userPassword
      };

      $http({
        method: 'POST',
        url: config.apiUrl + '/accountSetup',
        data: accountDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}

      })
      .then(function(response)
      {
        //for checking
        console.log(response);
        $scope.accountActivated = true;
      },
      function(response)
      {
        //for checking
        console.log(response);

        $scope.invalidInput = true;
        $scope.errMsg = "Account already activated";
      })
      .finally(function()
      {
        $scope.disableLogin = false;
        $scope.activateText = "ACTIVATE ACCOUNT";
      });
    }
    else
    {
      $scope.invalidInput = true;
      $scope.errMsg = "Invalid Input";
      /*
      if(form.password.$invalid)
      {
        $scope.errMsg = "Invalid Password";
      }
      else if(form.confirmpassword.$error.compareTo)
      {
        $scope.errMsg = "Password don't match";
      }
      else if(form.contactnumber.$invalid)
      {
        $scope.errMsg = "Invalid Contact Number";
      }
      else
      {
        $scope.errMsg = "Invalid Input";
      }
      */
    }
  }
})
