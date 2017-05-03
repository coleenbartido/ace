angular.module('aceWeb')


.controller('SettingsController', function(config, $scope, $http, $state, $location, $localStorage, AuthService)
{
  $scope.getContactNum = function ()
  {
    var accountDetails =
    {
      'email' : AuthService.getEmail()
    }
    $http({
      method: 'POST',
      url: config.apiUrl + '/getContactNum',
      data: accountDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.contactNum = response.data.contactNum;
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
    $scope.saveBtn = "Save";
    $scope.disableSaveBtn = false;
    $scope.getContactNum();
  } //scope initScope

  $scope.initScope();

  $scope.showPwordModal = function()
  {
    $scope.settingsForm.$setPristine();
    $scope.settingsForm.oldPassword.$setUntouched();
    $scope.settingsForm.userPassword.$setUntouched();
    $scope.settingsForm.userConfirmPassword.$setUntouched();
    $scope.oldPassword = "";
    $scope.userPassword = "";
    $scope.userConfirmPassword = "";
  }

  $scope.showContactModal = function()
  {
    $scope.settingsForm2.$setPristine();
    $scope.settingsForm2.newContactNum.$setUntouched();
    $scope.newContactNum = $scope.contactNum;
  }

  $scope.confirmContact = function(form)
  {
    if(form.$valid)
    {
      $scope.saveBtn = "Saving";
      $scope.disableSaveBtn = true;

      var changeContactDetails =
      {
        'email' : AuthService.getEmail(),
        'contactNum' : $scope.newContactNum
      };

      $http({
        method: 'POST',
        url: config.apiUrl + '/changeContact',
        data: changeContactDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })

      .then(function(response)
      {
        //for checking
        console.log(response);

        $scope.contactNum = $scope.newContactNum;

        $('#contactModal').modal('hide');
        $scope.showCustomModal("SUCCESS", response.data.successMsg);
      },
      function(response)
      {
        console.log(response);

      })
      .finally(function()
      {
        $scope.saveBtn = "Save";
        $scope.disableSaveBtn = false;
      });
    }
  }

  //compares old password to new password
  $scope.changePassword = function(settingsForm)
  {
    if(settingsForm.userPassword.$valid && settingsForm.userConfirmPassword.$valid)
    {
      $scope.settingsForm.oldPassword.$setValidity('invalidOldPword', true);
    }

    if(settingsForm.oldPassword.$valid && settingsForm.userConfirmPassword.$valid)
    {
      $scope.settingsForm.userPassword.$setValidity('samePword', true);
    }

    if(settingsForm.$valid)
    {
      $scope.saveBtn = "Saving";
      $scope.disableSaveBtn = true;

      var changePassDetails =
      {
        'email' : AuthService.getEmail(),
        'pword' : $scope.userPassword,
        'oldPword': $scope.oldPassword
      };

      $http({
        method: 'POST',
        url: config.apiUrl + '/changePasswordInSettings',
        data: changePassDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })

      .then(function(response)
      {
        console.log(response);

        $('#pwordModal').modal('hide');
        $scope.showCustomModal("SUCCESS", response.data.successMsg);
      },
      function(response)
      {
        console.log(response);

        if(response.status == 400)
        {
          if(response.data.errorMsg == 'Invalid password')
          {
            $scope.settingsForm.oldPassword.$setValidity('invalidOldPword', false);
          }
          if(response.data.errorMsg == 'Invalid new password')
          {
            $scope.settingsForm.userPassword.$setValidity('samePword', false);
          }
        }
      })
      .finally(function()
      {
        $scope.saveBtn = "Save";
        $scope.disableSaveBtn = false;
      });
    }
  }

  $scope.showCustomModal = function(modalTitle, modalMsg)
  {
    BootstrapDialog.alert({
      title: modalTitle,
      message: modalMsg,
      type: BootstrapDialog.TYPE_PRIMARY,
      closable: false
    });
  }

})