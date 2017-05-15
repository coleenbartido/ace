angular.module('aceWeb')


.controller('ManageFacultyController', function(config, $scope, $http, $state, AuthService)
{
  $scope.getFacultyList = function()
  {
    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/listFaculty',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.facultyAccounts = JSON.parse(response.data.facultyList);
      $scope.totalItems = $scope.facultyAccounts.length;
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
    $scope.facultyList = {};
    $scope.facultyList.email = [];
    $scope.mainCheckbox = false;

    //for pagination
    $scope.maxSize = 5;
    $scope.currentPage = 1;
    $scope.itemsPerPage = 8;

    $scope.getFacultyList();
  } //scope initScope

  $scope.initScope();

  $scope.showPopup = function(faculty)
  {
      $scope.selectedFaculty = faculty;

      $scope.email = faculty.email;
      $scope.lastName = faculty.last_name;
      $scope.firstName = faculty.first_name;
      $scope.userType = faculty.user_type_id;

  }

  $scope.updateFaculty = function(form)
  {
    if(form.$valid)
    {
      $scope.updateBtn = "Updating";
      $scope.disableUpdateBtn = true;

      var facultyDetails =
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
        data: facultyDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $scope.getFacultyList();
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


  $scope.showRegFacultyModal = function()
  {
    $scope.createBtn = "Create Account";
    $scope.disableRegBtn = false;

    $scope.regFacultyForm.$setPristine();
    $scope.regFacultyForm.facultyEmail.$setUntouched();
    $scope.regFacultyForm.facultyFirstName.$setUntouched();
    $scope.regFacultyForm.facultyLastName.$setUntouched();
    $scope.facultyEmail = "";
    $scope.facultyFirstName = "";
    $scope.facultyLastName = "";
  }

  $scope.registerFaculty = function(regFacultyForm)
  {
    if(regFacultyForm.facultyFirstName.$valid && regFacultyForm.facultyLastName.$valid)
    {
      $scope.regFacultyForm.facultyEmail.$setValidity('emailExist', true);

      if(regFacultyForm.$valid)
      {
        $scope.createBtn = "Creating Account";
        $scope.disableRegBtn = true;

        var registerDetails =
        {
          'userEmail' : AuthService.getEmail(),
          'email' : $scope.facultyEmail,
          'fName' : $scope.facultyFirstName,
          'lName' : $scope.facultyLastName
        };

        $http({
          method: 'POST',
          url: config.apiUrl + '/auth/registerFaculty',
          data: registerDetails,
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
        .then(function(response)
        {
          //for checking
          console.log(response);

          $scope.getFacultyList();

          $('#facultyRegModal').modal('hide');

          $scope.showCustomModal("SUCCESS", response.data.successMsg);
        },
        function(response)
        {
          //for checking
          console.log(response);

          if(response.status == 400)
          {
            $scope.regFacultyForm.facultyEmail.$setValidity(response.data.errorMsg, false);
          }
        })
        .finally(function()
        {
          $scope.createBtn = "Create Account";
          $scope.disableRegBtn = false;
        });
      }
    }
  }//registerFaculty

  $scope.deleteFaculty = function(email)
  {
    BootstrapDialog.show({
      title: 'Delete Faculty',
      message: 'Are you sure you want to delete this faculty?',
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

          var facultyDetails =
          {
            'userEmail' : AuthService.getEmail(),
            'facultyList': email
          }

          $http({
            method: 'POST',
            url: config.apiUrl + '/auth/deleteFaculty',
            data: facultyDetails,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
          })
          .then(function(response)
          {
            //for checking
            console.log(response);

            $scope.getFacultyList();

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
  } // $scope.deleteFaculty

  $scope.deleteFacultyList = function()
  {
    BootstrapDialog.show({
      title: 'Delete Faculty Accounts',
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

          var facultyDetails =
          {
            'userEmail' : AuthService.getEmail(),
            'facultyList' : $scope.facultyList
          }

          $http({
            method: 'POST',
            url: config.apiUrl + '/auth/deleteFaculty',
            data: facultyDetails,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
          })
          .then(function(response)
          {
            //for checking
            console.log(response);

            $scope.getFacultyList();

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
            $scope.facultyList.email = [];
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

  $scope.$watch("facultyList.email", function()
  {
    $scope.mainCheckbox = false;

    if($scope.facultyAccounts && $scope.filtered.length == $scope.facultyList.email.length)
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
    $scope.facultyList.email = [];
    $scope.mainCheckbox = false;

    if($scope.facultyAccounts && $scope.filtered.length == $scope.facultyList.email.length && $scope.filtered.length != 0)
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
    $scope.facultyList.email = [];

    if($scope.mainCheckbox)
    {
      for(var counter=0; counter < $scope.filtered.length; counter++)
      {
        $scope.facultyList.email.push($scope.filtered[counter].email);
      }
    }
  }

  $scope.disableDeleteBtn = function ()
  {
    if($scope.facultyList.email == undefined || $scope.facultyList.email.length == 0)
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
