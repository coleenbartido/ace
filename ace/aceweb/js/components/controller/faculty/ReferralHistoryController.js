angular.module('aceWeb')


.controller('ReferralHistoryController', function(config, $scope, $http, $state, $localStorage, AuthService, $interval, $rootScope, $filter)
{
  $scope.getReportList = function()
  {
    var reportDetails =
    {
      'email' : AuthService.getEmail()
    }
    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/referralHistory',
      data: reportDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.reports = JSON.parse(response.data.reportsList);

      if($scope.reports.length > 0)
      {
        $scope.SYList = $filter('orderBy')($scope.reports, 'school_year', true);
        $scope.SYList = $filter('unique')($scope.SYList, 'school_year');

        if(!$scope.initList || $scope.selectedSY == undefined || $scope.currentSYSize > $scope.SYList.length)
        {
          $scope.selectedSY = $scope.SYList[0].school_year;
        }

        for(var counter=0; counter < $scope.reports.length; counter++)
        {
          //convert string date into javascript date object
          strDate = $scope.reports[counter].report_date.replace(/-/g,'/');
          $scope.reports[counter].report_date = new Date(strDate);
        }

        $scope.currentSYSize = $scope.SYList.length;
        $scope.subReports = $filter('filter')($scope.reports, {school_year: $scope.selectedSY});
      }
      else
      {
        $scope.selectedSY = undefined;
      }

      $scope.totalItems = $scope.reports.length;
      $scope.isLoading = false;
      $scope.initList = true;
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
    $scope.initList = false;
    $scope.selectedSY = undefined;
    $scope.searchBox = undefined;
    $scope.reportList = {};
    $scope.reportList.report_id = [];
    $scope.mainCheckbox = false;

    //for pagination
    $scope.maxSize = 5;
    $scope.currentPage = 1;
    $scope.itemsPerPage = 8;

    $scope.getReportList();
  } //scope initScope

  $scope.initScope();

  $scope.reportPoll = $interval($scope.getReportList, 3000);

  $scope.$on('$destroy',function()
  {
    if($scope.reportPoll)
    {
      $interval.cancel($scope.reportPoll);
    }
  })

  $scope.$watch("reportList.report_id", function()
  {
    $scope.mainCheckbox = false;

    if($scope.reports && $scope.filtered.length == $scope.reportList.report_id.length)
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
    $scope.reportList.report_id = [];
    $scope.mainCheckbox = false;

    if($scope.reports && $scope.filtered.length == $scope.reportList.report_id.length && $scope.filtered.length != 0)
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
    $scope.reportList.report_id = [];
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
        $scope.reportList.report_id.push($scope.filtered[counter].report_id);
      }
    }
  }

  $scope.updateSYList = function ()
  {
    $scope.reportList.report_id = [];
    $scope.mainCheckbox = false;
    $scope.subReports = $filter('filter')($scope.reports, {school_year: $scope.selectedSY});
    $scope.subReports = $filter('filter')($scope.subReports, $scope.searchBox);
  }

  $scope.disableActionBtn = function ()
  {
    if($scope.reportList.report_id == undefined || $scope.reportList.report_id.length == 0)
    {
      return true;
    }
    return false;
  }

  $scope.selectAllRead = function ()
  {
    $scope.reportList.report_id = [];

    for(var counter=0; counter < $scope.filtered.length; counter++)
    {
      if($scope.filtered[counter].is_updated == 0)
      {
        $scope.reportList.report_id.push($scope.filtered[counter].report_id);
      }
    }
  }

  $scope.selectAllUnread = function ()
  {
    $scope.reportList.report_id = [];

    for(var counter=0; counter < $scope.filtered.length; counter++)
    {
      if($scope.filtered[counter].is_updated == 1)
      {
        $scope.reportList.report_id.push($scope.filtered[counter].report_id);
      }
    }
  }

  $scope.markAsRead = function()
  {
    $interval.cancel($rootScope.notifPoll);
    $interval.cancel($scope.reportPoll);

    var reportDetails =
    {
      'reportId' : $scope.reportList
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/markFacultyReport',
      data: reportDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })

    .then(function(response)
    {
      console.log(response);

      $scope.getReportList();
      $rootScope.getNotif();
    },
    function(response)
    {
      console.log(response);
      //for checking
    })
    .finally(function()
    {
      $scope.reportList.report_id = [];
      $scope.mainCheckbox = false;

      $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);
      $scope.reportPoll = $interval($scope.getReportList, 3000);
    });
  }

  $scope.markAsUnread = function()
  {
    $interval.cancel($rootScope.notifPoll);
    $interval.cancel($scope.reportPoll);

    var reportDetails =
    {
      'reportId' : $scope.reportList
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/markFacultyReportAsUnread',
      data: reportDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.getReportList();
      $rootScope.getNotif();
    },
    function(response)
    {
      //for checking
      console.log(response);

    })
    .finally(function()
    {
      $scope.reportList.report_id = [];
      $scope.mainCheckbox = false;

      $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);
      $scope.reportPoll = $interval($scope.getReportList, 3000);
    });
  }

  $scope.viewReport = function(report)
  {
    $scope.selectedReport = report;
    $scope.reasonList = report.report_reasons;

    if(report.counselor_note == null)
    {
      report.counselor_note_view = "N/A";
    }
    else
    {
      report.counselor_note_view = report.counselor_note;
    }

    if(report.referral_comment == null)
    {
      report.referral_comment_view = "N/A";
    }
    else
    {
      report.referral_comment_view = report.referral_comment;
    }

    if($scope.selectedReport.is_updated == 1)
    {
      $interval.cancel($rootScope.notifPoll);
      $interval.cancel($scope.reportPoll);

      var reportDetails =
      {
        'reportId' : $scope.selectedReport.report_id
      }

      $http({
        method: 'POST',
        url: config.apiUrl + '/auth/markFacultyReport',
        data: reportDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $scope.getReportList();
        $rootScope.getNotif();
      },
      function(response)
      {
        //for checking
        console.log(response);

      })
      .finally(function()
      {
        $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);
        $scope.reportPoll = $interval($scope.getReportList, 3000);
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
