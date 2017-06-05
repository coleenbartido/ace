angular.module('aceWeb')


.controller('ManageStudentController', function(config, $scope, $http, $state, AuthService)
{
  $scope.getStudentList = function()
  {
    var adminDetails =
    {
      'email' : AuthService.getEmail()
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/listStudent',
      data: adminDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.students = JSON.parse(response.data.studentList);
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
    $scope.deleteBtn = "Delete";
    $scope.updateBtn = "Update";
    $scope.disableUpdateBtn = false;

    $scope.searchBox = undefined;
    $scope.studentList = {};
    $scope.studentList.student_id = [];
    $scope.mainCheckbox = false;

    //for pagination
    $scope.maxSize = 5;
    $scope.currentPage = 1;
    $scope.itemsPerPage = 8;

    $scope.getStudentList();
  } //scope initScope

  $scope.initScope();

  $scope.$watch("studentList.student_id", function()
  {
    $scope.mainCheckbox = false;

    if($scope.students && $scope.filtered.length == $scope.studentList.student_id.length)
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
    $scope.studentList.student_id = [];
    $scope.mainCheckbox = false;

    if($scope.students && $scope.filtered.length == $scope.studentList.student_id.length && $scope.filtered.length != 0)
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
    $scope.studentList.student_id = [];
    var startingItem = 0;
    var endingItem = 0;
    var lastPageLength = $scope.filtered.length % $scope.itemsPerPage;

    if($scope.currentPage >= 2)
    {
      startingItem = (($scope.currentPage - 1) * ($scope.itemsPerPage)) - 1;
    }

    if(lastPageLength == 0)
    {
      endingItem = $scope.itemsPerPage;
    }
    else
    {
      endingItem = lastPageLength;
    }

    if($scope.mainCheckbox)
    {
      for(var counter = startingItem; counter < endingItem; counter++)
      {
        $scope.studentList.student_id.push($scope.filtered[counter].student_id);
      }
    }
  }

  $scope.disableDeleteBtn = function ()
  {
    if($scope.studentList.student_id == undefined || $scope.studentList.student_id.length == 0)
    {
      return true;
    }
    return false;
  }

  $scope.deleteStudent = function(student)
  {
    BootstrapDialog.show({
      title: 'Delete Student',
      message: 'Are you sure you want to delete this student?',
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

          var studentDetails =
          {
            'studentList': student.student_id,
            'email' : AuthService.getEmail()
          }

          $http({
            method: 'POST',
            url: config.apiUrl + '/auth/deleteStudent',
            data: studentDetails,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
          })
          .then(function(response)
          {
            //for checking
            console.log(response);

            $scope.getStudentList();

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

  $scope.deleteStudentList = function()
  {
    BootstrapDialog.show({
      title: 'Delete Students',
      message: 'Are you sure you want to delete selected student/s?',
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

          var studentDetails =
          {
            'studentList' : $scope.studentList,
            'email' : AuthService.getEmail()
          }

          $http({
            method: 'POST',
            url: config.apiUrl + '/auth/deleteStudent',
            data: studentDetails,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
          })
          .then(function(response)
          {
            //for checking
            console.log(response);

            $scope.getStudentList();

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

            $scope.studentList.student_id = [];
            $scope.mainCheckbox = false;

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

  $scope.showPopup = function(student)
  {
    $scope.selectedStudent = student;

    $scope.department = student.department_id;
    $scope.studId = student.student_id;
    $scope.originalId = student.student_id;
    $scope.studLName = student.last_name;
    $scope.studFName = student.first_name;
    $scope.program = student.program_id + "";
    $scope.level = student.level_id + "";
  }

  $scope.updateStudent = function(form)
  {
    if(form.$valid)
    {
      $scope.updateBtn = "Updating";
      $scope.disableUpdateBtn = true;

      var studentDetails =
      {
        'email' : AuthService.getEmail(),
        'originalId': $scope.originalId,
        'studentId' : $scope.studId,
        'lastName' : $scope.studLName,
        'firstName' : $scope.studFName,
        'program' : $scope.program,
        'level' : $scope.level
      };

      $http({
        method: 'POST',
        url: config.apiUrl + '/auth/updateStudent',
        data: studentDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $scope.getStudentList();
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
