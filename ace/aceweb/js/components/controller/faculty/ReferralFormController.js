angular.module('aceWeb')


.controller('ReferralFormController', function(config, $scope, $http, $state, $localStorage, AuthService, $filter)
{

  $scope.initScope = function()
  {
    $scope.submitBtn = "Submit";
    $scope.disableSubmitBtn = false;
    $scope.currentYear = new Date().getFullYear();
    $scope.firstSY = ($scope.currentYear - 1) + " - " + $scope.currentYear;
    $scope.secondSY = $scope.currentYear + " - " + ($scope.currentYear + 1);
    $scope.thirdSY = ($scope.currentYear + 1) + " - " + ($scope.currentYear + 2);
    $scope.reasons = [
    {
      name: 'Student is habitually absent or late (reaching half of allowed absences)',
      value: 1,
      check: false
    }, {
      name: 'Student is underachieving (smart but lazy)',
      value: 2,
      check: false
    }, {
      name: 'Student shows inability to perform in class (signs of failing)',
      value: 3,
      check: false
    }, {
      name: 'Student plans to transfer to another class',
      value: 4,
      check: false
    }, {
      name: 'Student shows violent/disruptive behavior',
      value: 5,
      check: false
    }, {
      name: 'Student shows emotional distress',
      value: 6,
      check: false
    }, {
      name: 'Other Reasons (please specify):',
      value: undefined,
      check: false
    }];
  }

  $scope.initScope();

  $scope.resetInput = function()
  {
    $scope.refForm.year.$setUntouched();
    $scope.refForm.course.$setUntouched();
    $scope.refForm.schoolTerm.$setUntouched();
    
    $scope.year = undefined;
    $scope.course = undefined;
    $scope.schoolTerm = undefined;
  }

  $scope.$watch("reasons", function(n, o)
  {
    var trues = $filter("filter")(n, {
      check: true
    });
    $scope.checkedReasons = trues.length <= 0;
  }, true);

  $scope.submitReferral=function(form)
  {
    if(form.$valid && !$scope.checkedReasons)
    {
      if($scope.reasons[6].check && !$scope.reasons[6].value)
      {
        $scope.invalidOtherReason = true;
        $scope.showCustomModal("ERROR", "Please specify the reason!");
      }
      else
      {
        $scope.submitBtn = "Submitting";
        $scope.disableSubmitBtn = true;

        var referralDetails =
        {
          'schoolYear' : $scope.schoolYear,
          'email' : AuthService.getEmail(),
          'schoolTerm' : $scope.schoolTerm,
          'studId' : $scope.studId,
          'studFName': $scope.studFName,
          'studLName' : $scope.studLName,
          'subjName' : $scope.subjName,
          'department' : parseInt($scope.department),
          'course' : $scope.course,
          'year' : $scope.year,
          'reason': $scope.reasons
        };
        $http({
          method: 'POST',
          url: config.apiUrl + '/auth/referralForm',
          data: referralDetails,
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
        .then(function(response)
        {
          console.log(response);
          $scope.resetForm();
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
          $scope.submitBtn = "Submit";
          $scope.disableSubmitBtn = false;
        });
      }
    }
    else if(form.$valid && $scope.checkedReasons)
    {
      $scope.showCustomModal("ERROR", "Please select a reason!");
    }
  }

  $scope.updateOtherTextArea = function(model)
  {
    if(!model)
    {
      $scope.invalidOtherReason = true;
    }
    else
    {
      $scope.invalidOtherReason = false;
    }
  }

  $scope.updateOtherCheckbox = function()
  {
    $scope.invalidOtherReason = false;
    $scope.reasons[6].value = undefined;
  }

  $scope.getStudentInfo = function(val)
  {
    var studentInfo =
    {
      'studId' : val
    }
    return $http({
      method: 'POST',
      url: config.apiUrl + '/auth/getStudentInfo',
      data: studentInfo,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      console.log(response);

      var students = JSON.parse(response.data.studInfoList)
      return students;
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

  $scope.onSelect = function ($item, $model, $label)
  {
    $scope.studFName = $item.first_name;
    $scope.studLName = $item.last_name;
    $scope.department = '' + $item.department_id;
    $scope.course = '' + $item.program;
    $scope.year = '' + $item.level;
  };

  $scope.resetForm = function ()
  {
    $scope.refForm.$setUntouched();
    $scope.refForm.$setPristine();

    $scope.schoolYear = undefined;
    $scope.schoolTerm = undefined;
    $scope.studId = undefined;
    $scope.studFName = undefined;
    $scope.studLName = undefined;
    $scope.subjName = undefined;
    $scope.course = undefined;
    $scope.year = undefined;
    $scope.department = undefined;
    $scope.reasons[0].check = false;
    $scope.reasons[1].check = false;
    $scope.reasons[2].check = false;
    $scope.reasons[3].check = false;
    $scope.reasons[4].check = false;
    $scope.reasons[5].check = false;
    $scope.reasons[6].check = false;
    $scope.reasons[6].value = undefined;
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
