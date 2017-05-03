angular.module('aceWeb')


.controller('DatabaseController', function(config, $scope, $http, $state, $localStorage, AuthService, $timeout)
{
  $scope.initScope = function()
  {
    $scope.verifyBtn = "Verify";
    $scope.restoreBtn = "Restore";
    $scope.currentDateNum = new Date().toString('MM-dd-yy-HH-mm');  
  }

  $scope.initScope();

  $scope.showModal = function(form)
  {
    $scope.isNotAuthenticated = false;
    $scope.invalidFile = false;
    $scope.isFileEmpty = false;
    $scope.isNoFile = false;

    angular.element("input[type='file']").val(null);
    $scope.file = undefined;
    $scope.userPassword = undefined;
    form.$setPristine();
    form.userPassword.$setUntouched();
  }

  $scope.setValidPassword = function(form)
  {
    form.userPassword.$setValidity('incorrectPword', true);
  }

  $scope.downloadBackup = function(form)
  {
    if(form.$valid)
    {
      $scope.disableVerifyBtn = true;
      $scope.verifyBtn = "Verifying";

      var userDetails =
      {
        'email' : AuthService.getEmail(),
        'password' : $scope.userPassword
      }

      $http({
        method: 'POST',
        url: config.apiUrl + '/downloadBackup',
        data: userDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        var anchor = document.createElement('a');
        anchor.href = 'data:attachment/sql;charset=utf-8,' + encodeURI(response.data);
        anchor.target = '_blank';
        anchor.download = 'ace_backup_' + $scope.currentDateNum + '.sql';
        document.body.appendChild(anchor);
        anchor.click();
        $timeout(function () {
           anchor.remove();
        }, 50);

        $('#backupModal').modal('hide');
      },
      function(response)
      {
        //for checking
        console.log(response);

        if(response.data.errorMsg == "Incorrect Password")
        {
          $scope.userPassword = undefined;
          $scope.backupForm.userPassword.$setValidity('incorrectPword', false);
        }
        else
        {
          $('#backupModal').modal('hide');
          $scope.showCustomModal("ERROR", response.data.errorMsg);
        }
      })
      .finally(function()
      {
        $scope.disableVerifyBtn = false;
        $scope.verifyBtn = "Verify";
      });
    } 
  }

  $scope.resetDatabase = function(form)
  {
    if(form.$valid)
    {
      $scope.disableVerifyBtn = true;
      $scope.verifyBtn = "Verifying";

      var userDetails =
      {
        'email' : AuthService.getEmail(),
        'password' : $scope.userPassword
      }

      $http({
        method: 'POST',
        url: config.apiUrl + '/resetDatabase',
        data: userDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $('#resetModal').modal('hide');
        $scope.showCustomModal("SUCCESS", response.data.successMsg);
      },
      function(response)
      {
        //for checking
        console.log(response);

        if(response.data.errorMsg == "Incorrect Password")
        {
          $scope.userPassword = undefined;
          $scope.resetForm.userPassword.$setValidity('incorrectPword', false);
        }
        else
        {
          $('#resetModal').modal('hide');
          $scope.showCustomModal("ERROR", response.data.errorMsg);
        }
      })
      .finally(function()
      {
        $scope.disableVerifyBtn = false;
        $scope.verifyBtn = "Verify";
      });
    }
  }

  $scope.restoreBackup = function(form)
  {
    if(form.$valid)
    {
      $scope.disableVerifyBtn = true;
      $scope.verifyBtn = "Verifying";

      var userDetails =
      {
        'email' : AuthService.getEmail(),
        'password' : $scope.userPassword
      }

      $http({
        method: 'POST',
        url: config.apiUrl + '/verifyAdminAccount',
        data: userDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $('#restoreModal').modal('hide');
        $('#fileUploadModal').modal('show');
      },
      function(response)
      {
        //for checking
        console.log(response);

        $scope.userPassword = undefined;
        $scope.restoreForm.userPassword.$setValidity('incorrectPword', false);
      })
      .finally(function()
      {
        $scope.disableVerifyBtn = false;
        $scope.verifyBtn = "Verify";
      });
    }
  }

  $scope.uploadFile = function()
  {
    if($scope.file)
    {
      $scope.disableRestoreBtn = true;
      $scope.restoreBtn = "Restoring";

      var fd = new FormData();
      fd.append('file', $scope.file);
      fd.append('email', AuthService.getEmail());
      fd.append('password', $scope.userPassword);

      $http.post(config.apiUrl + '/restoreBackup', fd, {
        transformRequest: angular.identity,
        headers: {'Content-Type': undefined,'Process-Data': false}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $('#fileUploadModal').modal('hide');

        $scope.showCustomModal("SUCCESS", response.data.successMsg);
      },
      function(response)
      {
        //for checking
        console.log(response);

        if(response.data.errorMsg == "Authentication failed")
        {
          $scope.isNotAuthenticated = true;
        }
        else if(response.data.errorMsg == "Invalid backup file")
        {
          $scope.invalidFile = true;
        }
        else if(response.data.errorMsg == "Empty backup file")
        {
          $scope.isFileEmpty = true;
        }     
        else
        {
          $('#fileUploadModal').modal('hide');
          $scope.showCustomModal("ERROR", response.data.errorMsg);
        }     
      })
      .finally(function()
      {
        $scope.disableRestoreBtn = false;
        $scope.restoreBtn = "Restore";
      });
    }
    else
    {
      $scope.isNoFile = true;
    }
  }

  $scope.fileNameChanged = function() 
  {
    $scope.isNotAuthenticated = false;
    $scope.invalidFile = false;
    $scope.isFileEmpty = false;
    $scope.isNoFile = false;
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