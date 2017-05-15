angular.module('aceWeb')


.controller('ManageAdminController', function(config, $scope, $http, $state, AuthService)
{

  $scope.getAdminList = function()
  {
    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/listAdmin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.adminAccounts = JSON.parse(response.data.adminList);
      $scope.totalItems = $scope.adminAccounts.length;
      $scope.isLoading = false;
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

    $scope.createBtn = "Create Account";
    $scope.disableRegBtn = false;
    $scope.deleteBtn = "Delete";
    $scope.updateBtn = "Update";

    $scope.searchBox = undefined;
    $scope.adminList = {};
    $scope.adminList.email = [];
    $scope.mainCheckbox = false;

    //for pagination
    $scope.maxSize = 5;
    $scope.currentPage = 1;
    $scope.itemsPerPage = 8;

    $scope.getAdminList();
  } //scope initScope

  $scope.initScope();

  $scope.showPopup = function(admin)
  {
    $scope.selectedAdmin = admin;

    $scope.email = admin.email;
    $scope.lastName = admin.last_name;
    $scope.firstName = admin.first_name;
    $scope.userType = admin.user_type_id;

  }

  $scope.updateAdmin = function(form)
  {
    if(form.$valid)
    {
      $scope.updateBtn = "Updating";
      $scope.disableUpdateBtn = true;

      var adminDetails =
      {
        'userEmail' : AuthService.getEmail(),
        'email': $scope.email,
        'lastName' : $scope.lastName,
        'firstName' : $scope.firstName,
        'userType':  $scope.userType
      };

      $http({
        method: 'POST',
        url: config.apiUrl + '/auth/updateAccount',
        data: adminDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $scope.getAdminList();
        $('#editModal').modal('hide');
        $scope.showCustomModal("SUCCESS", response.data.successMsg);
      },
      function(response)
      {
        //for checking
        console.log(response);

        $('#editModal').modal('hide');
        $scope.showCustomModal("ERROR", response.data.errorMsg);
      })
      .finally(function()
      {
        $scope.updateBtn = "Update";
        $scope.disableUpdateBtn = false;
      });
    }
  }


  $scope.showRegAdminModal = function()
  {
    $scope.createBtn = "Create Account";
    $scope.disableRegBtn = false;

    $scope.regAdminForm.$setPristine();
    $scope.regAdminForm.adminEmail.$setUntouched();
    $scope.regAdminForm.adminFirstName.$setUntouched();
    $scope.regAdminForm.adminLastName.$setUntouched();
    $scope.regAdminForm.adminDepartment.$setUntouched();
    $scope.adminEmail = "";
    $scope.adminFirstName = "";
    $scope.adminLastName = "";
    $scope.adminDepartment = "";
  }

  $scope.registerAdmin = function(regAdminForm)
  {
    if(regAdminForm.adminFirstName.$valid && regAdminForm.adminLastName.$valid && regAdminForm.adminDepartment.$valid)
    {
      $scope.regAdminForm.adminEmail.$setValidity('emailExist', true);

      if(regAdminForm.$valid)
      {
        $scope.createBtn = "Creating Account";
        $scope.disableRegBtn = true;

        var registerDetails =
        {
          'userEmail' : AuthService.getEmail(),
          'email' : $scope.adminEmail,
          'fName' : $scope.adminFirstName,
          'lName' : $scope.adminLastName,
          'department' : parseInt($scope.adminDepartment)
        };

        $http({
          method: 'POST',
          url: config.apiUrl + '/auth/registerAdmin',
          data: registerDetails,
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
        .then(function(response)
        {
          //for checking
          console.log(response);

          $scope.getAdminList();

          $('#adminRegModal').modal('hide');

          $scope.showCustomModal("SUCCESS", response.data.successMsg);
        },
        function(response)
        {
          //for checking
          console.log(response);

          if(response.status == 400)
          {
            $scope.regAdminForm.adminEmail.$setValidity(response.data.errorMsg, false);
          }
        })
        .finally(function()
        {
          $scope.createBtn = "Create Account";
          $scope.disableRegBtn = false;
        });
      }
    }
  }

  $scope.deleteAdmin = function(email)
  {
    BootstrapDialog.show({
      title: 'Delete Administrator',
      message: 'Are you sure you want to delete this administrator?',
      type: BootstrapDialog.TYPE_PRIMARY,
      closable: false,
      buttons:
      [{
        label: $scope.deleteBtn,
        cssClass: 'btn-danger',
        autospin: true,
        action: function(dialogRef)
        {
          $scope.deleteBtn = "Deleting";
          dialogRef.enableButtons(false);

          var adminDetails =
          {
            'userEmail' : AuthService.getEmail(),
            'adminList': email
          }

          $http({
            method: 'POST',
            url: config.apiUrl + '/auth/deleteAdmin',
            data: adminDetails,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
          })
          .then(function(response)
          {
            //for checking
            console.log(response);

            $scope.getAdminList();

            $scope.showCustomModal("SUCCESS", response.data.successMsg);
          },
          function(response)
          {
            //for checking
            console.log(response);

            $scope.showCustomModal("ERROR", response.data.errorMsg);
          })
          .finally(function()
          {
            $scope.deleteBtn = "Delete";
            dialogRef.enableButtons(true);
            dialogRef.close();
          });
        }
      },
      {
        label: 'Cancel',
        action: function(dialogRef)
        {
          dialogRef.close();
        }
      }]
    });
  } // $scope.deleteAdmin

  $scope.deleteAdminList = function()
  {
    BootstrapDialog.show({
      title: 'Delete Administrators',
      message: 'Are you sure you want to delete selected accounts?',
      type: BootstrapDialog.TYPE_PRIMARY,
      closable: false,
      buttons:
      [{
        label: $scope.deleteBtn,
        cssClass: 'btn-danger',
        autospin: true,
        action: function(dialogRef)
        {
          $scope.deleteBtn = "Deleting";
          dialogRef.enableButtons(false);

          var adminDetails =
          {
            'userEmail' : AuthService.getEmail(),
            'adminList' : $scope.adminList
          }

          $http({
            method: 'POST',
            url: config.apiUrl + '/auth/deleteAdmin',
            data: adminDetails,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
          })
          .then(function(response)
          {
            //for checking
            console.log(response);

            $scope.getAdminList();

            $scope.showCustomModal("SUCCESS", response.data.successMsg);
          },
          function(response)
          {
            //for checking
            console.log(response);

            $scope.showCustomModal("ERROR", response.data.errorMsg);
          })
          .finally(function()
          {
            $scope.adminList.email = [];
            $scope.mainCheckbox = false;

            $scope.deleteBtn = "Delete";
            dialogRef.enableButtons(true);
            dialogRef.close();
          });
        }
      },
      {
        label: 'Cancel',
        action: function(dialogRef)
        {
          dialogRef.close();
        }
      }]
    });
  }

  $scope.$watch("adminList.email", function()
  {
    $scope.mainCheckbox = false;

    if($scope.adminAccounts && $scope.filtered.length == $scope.adminList.email.length)
    {
      $scope.mainCheckbox = true;
    }
    else
    {
      $scope.mainCheckbox = false;
    }
  }, true);

  $scope.$watch("searchBox", function()
  {
    $scope.adminList.email = [];
    $scope.mainCheckbox = false;

    if($scope.adminAccounts && $scope.filtered.length == $scope.adminList.email.length && $scope.filtered.length != 0)
    {
      $scope.mainCheckbox = true;
    }
    else
    {
      $scope.mainCheckbox = false;
    }
  }, true);

  $scope.controlCheckbox = function ()
  {
    $scope.adminList.email = [];

    if($scope.mainCheckbox)
    {
      for(var counter=0; counter < $scope.filtered.length; counter++)
      {
        $scope.adminList.email.push($scope.filtered[counter].email);
      }
    }
  }

  $scope.disableDeleteBtn = function ()
  {
    if($scope.adminList.email == undefined || $scope.adminList.email.length == 0)
    {
      return true;
    }
    return false;
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
