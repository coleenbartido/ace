angular.module('aceWeb')


.controller('ReportsController', function(config, $scope, $http, $state, $localStorage, AuthService, $interval, $rootScope, $filter)
{
  $scope.getReportList = function()
  {
    var reportDetails =
    {
      'email' : AuthService.getEmail()
    }
    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/reports',
      data: reportDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.reports = JSON.parse(response.data.reportsList);
      $scope.SYList = $scope.reports;

      if($scope.reports.length > 0)
      {
        $scope.SYList = $filter('orderBy')($scope.SYList, 'report_date', true);
        $scope.SYList = $filter('unique')($scope.SYList, 'school_year');

        if(!$scope.initList || $scope.selectedSY == undefined || $scope.currentSYSize > $scope.SYList.length)
        {
          $scope.selectedSY = $scope.SYList[0].school_year;
          $scope.selectedTerm = $scope.SYList[0].term + ""; 
        }
        
        for(var counter=0; counter < $scope.reports.length; counter++)
        {
          //convert string date into javascript date object
          strDate = $scope.reports[counter].report_date.replace(/-/g,'/');
          $scope.reports[counter].report_date = new Date(strDate);
        }
        
        $scope.currentSYSize = $scope.SYList.length;
      }
      else
      {
        $scope.selectedSY = undefined;
        $scope.selectedTerm = undefined;
      }

      $scope.subReports = $filter('filter')($scope.reports, {school_year: $scope.selectedSY});
      $scope.subReports = $filter('filter')($scope.subReports, {term: $scope.selectedTerm});
      $scope.subReports = $filter('orderBy')($scope.subReports, 'report_date', true);

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
    $scope.currentYear = new Date().getFullYear();
    $scope.firstSY = ($scope.currentYear - 1) + " - " + $scope.currentYear;
    $scope.secondSY = $scope.currentYear + " - " + ($scope.currentYear + 1);
    $scope.thirdSY = ($scope.currentYear + 1) + " - " + ($scope.currentYear + 2);
    $scope.sendBtn = "Send";
    $scope.isLoading = true;
    $scope.initList = false;
    $scope.selectedSY = undefined;
    $scope.currentDateNum = Date.today().toString('MMddyy');
    $scope.searchBox = undefined;
    $scope.reportList = {};
    $scope.reportList.report_id = [];
    $scope.mainCheckbox = false;
    $scope.userDepartment = AuthService.getDepartment();

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
    $scope.tempFiltered = '';

    if($scope.filtered)
    {
      $scope.tempFiltered = $scope.filtered;
    }

    $scope.mainCheckbox = false;
    var pageSize = $scope.itemsPerPage;
    var lastPageLength = $scope.tempFiltered.length % $scope.itemsPerPage;

    if($scope.tempFiltered.length < $scope.currentPage * $scope.itemsPerPage)
    {
      pageSize = lastPageLength;
    }

    if($scope.reports && pageSize == $scope.reportList.report_id.length)
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
    var endingItem = $scope.currentPage * $scope.itemsPerPage;
    var lastPageLength = $scope.filtered.length % $scope.itemsPerPage;

    if($scope.currentPage >= 2)
    {
      startingItem = ($scope.currentPage-1) * $scope.itemsPerPage;    
    }

    if($scope.filtered.length < $scope.currentPage * $scope.itemsPerPage)
    {
      endingItem = (($scope.currentPage-1) * $scope.itemsPerPage) + lastPageLength;
    }

    if($scope.mainCheckbox)
    {
      for(var counter = startingItem; counter < endingItem; counter++)
      {
        $scope.reportList.report_id.push($scope.filtered[counter].report_id);
      }
    }
  }

  $scope.resetCheckbox = function ()
  {
    $scope.reportList.report_id = [];
    $scope.mainCheckbox = false;
  }

  $scope.updateSYList = function ()
  {
    $scope.reportList.report_id = [];
    $scope.mainCheckbox = false;
    $scope.subReports = $filter('filter')($scope.reports, {school_year: $scope.selectedSY});
    $scope.subReports = $filter('filter')($scope.subReports, {term: $scope.selectedTerm});
    $scope.subReports = $filter('orderBy')($scope.subReports, 'report_date', true);
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

  $scope.readReport = function(report)
  {
    var reportDetails =
    {
      'reportId' : $scope.selectedReport.report_id,
      'isRead': $scope.selectedReport.is_read,
      'email' : $scope.selectedReport.email
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/readReport',
      data: reportDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.getReportList();
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

  $scope.sendEmail = function(report, form)
  {
    if(form.$valid)
    {
      $scope.sendBtn = "Sending";
      $scope.isSending = true;

      var messageDetails =
      {
        'sender' : AuthService.getEmail(),
        'receiver': report.email,
        'messageBody': $scope.composeEmail,
        'messageSubj': $scope.subject,
        'reportId': report.report_id
      }
      $http({
        method: 'POST',
        url: config.apiUrl + '/auth/sendMessage',
        data: messageDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

        $scope.composeEmail = undefined;

        $scope.showCustomModal("SUCCESS", response.data.successMsg);
      },
      function(response)
      {
        $scope.showCustomModal("ERROR", response.data.errorMsg);
      })
      .finally(function()
      {
        $('#messageModal').modal('hide');
        $scope.sendBtn = "Send";
        $scope.isSending = false;
      });
    }
  }

  $scope.deleteReport = function(report_id)
  {
    $scope.deleteBtn = "Delete";

    BootstrapDialog.show({
      title: 'Delete Report',
      message: 'Are you sure you want to delete this report?',
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
          $interval.cancel($rootScope.notifPoll);
          $interval.cancel($scope.reportPoll);

          var reportDetails =
          {
            'userEmail' : AuthService.getEmail(),
            'reportList' : report_id
          }

          $http({
            method: 'POST',
            url: config.apiUrl + '/auth/deleteReport',
            data: reportDetails,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
          })
          .then(function(response)
          {
            //for checking
            console.log(response);

            $scope.getReportList();
            $rootScope.getNotif();

            $scope.showCustomModal("SUCCESS", response.data.successMsg);
          },
          function(response)
          {
            $scope.showCustomModal("ERROR", response.data.errorMsg);
          })
          .finally(function()
          {
            $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);
            $scope.reportPoll = $interval($scope.getReportList, 3000);

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

  $scope.deleteReportList = function()
  {
    $scope.deleteBtn = "Delete";

    BootstrapDialog.show({
      title: 'Delete Reports',
      message: 'Are you sure you want to delete selected reports?',
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
          $interval.cancel($rootScope.notifPoll);
          $interval.cancel($scope.reportPoll);

          var reportDetails =
          {
            'userEmail' : AuthService.getEmail(),
            'reportList' : $scope.reportList
          }

          $http({
            method: 'POST',
            url: config.apiUrl + '/auth/deleteReport',
            data: reportDetails,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
          })
          .then(function(response)
          {
            //for checking
            console.log(response);

            $scope.getReportList();
            $rootScope.getNotif();

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
            $scope.reportList.report_id = [];
            $scope.mainCheckbox = false;
            $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);
            $scope.reportPoll = $interval($scope.getReportList, 3000);

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

  $scope.markAsRead = function()
  {
    var reportDetails =
    {
      'reportList' : $scope.reportList,
      'email': AuthService.getEmail()
    };

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/markReport',
      data: reportDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })

    .then(function(response)
    {
      console.log(response);

      $scope.getReportList();
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
    });
  }

  $scope.markAsUnread = function()
  {
    var reportDetails =
    {
      'reportList' : $scope.reportList,
      'email': AuthService.getEmail()
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/markReportAsUnread',
      data: reportDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.getReportList();
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
    });
  }

  $scope.updateReportStatus = function(report, form)
  {
    if(form.$valid)
    {
      $scope.updateBtn = "Updating";
      $scope.disableUpdateBtn = true;

      $interval.cancel($rootScope.notifPoll);
      $interval.cancel($scope.reportPoll);

      var updateDetails =
      {
        'userEmail' : AuthService.getEmail(),
        'email' : report.email,
        'reportId' : report.report_id,
        'prevReportStatus' : report.report_status_id,
        'reportStatus' : parseInt($scope.status),
        'comment' : $scope.comment
      }

      $http({
        method: 'POST',
        url: config.apiUrl + '/auth/updateStatus',
        data: updateDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })

      .then(function(response)
      {
        console.log(response);

        $scope.getReportList();
        $rootScope.getNotif();

        $scope.showCustomModal("SUCCESS", response.data.successMsg);
      },
      function(response)
      {
        $scope.showCustomModal("ERROR", response.data.errorMsg);
      })
      .finally(function()
      {
        $('#noteModal').modal('hide');
        $scope.updateBtn = "Update";
        $scope.disableUpdateBtn = false;

        $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);
        $scope.reportPoll = $interval($scope.getReportList, 3000);
      });
    }
  }

  $scope.viewReport = function(report)
  {
    $scope.selectedReport = report;
    $scope.reasonList = report.report_reasons;
    $scope.readReport();

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
  }

  $scope.updateStatus = function(report)
  {
    $scope.selectedReport = report;
    $scope.status = report.report_status_id + "";

    if(report.counselor_note != null)
    {
      $scope.comment = report.counselor_note;
    }
    else
    {
      $scope.comment = "";
    }

    $scope.updateBtn = "Update";
    $scope.disableUpdateBtn = false;
  }

  $scope.createMessage = function(report)
  {
    $('#messageModal').modal('show');

    $scope.selectedReport = report;
    $scope.subject = "Referral for " + report.student_fullname + ": " + report.subject_name;
    $scope.receiver = report.faculty_fullname;
    $scope.composeEmail = "";
    $scope.sendBtn = "Send";
    $scope.disableSendBtn = false;
  }

  $scope.enableReferral = function(form)
  {
    if(form.$valid)
    {
      $scope.enableBtn = "Enabling";
      $scope.disableToggleBtn = true;

      var updateDetails =
      {
        'userEmail' : AuthService.getEmail(),
        'schoolYear' : $scope.schoolYear,
        'term' : $scope.term
      }

      $http({
        method: 'POST',
        url: config.apiUrl + '/auth/toggleReferral',
        data: updateDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })

      .then(function(response)
      {
        console.log(response);

        $scope.showCustomModal("SUCCESS", response.data.successMsg);
      },
      function(response)
      {
        $scope.showCustomModal("ERROR", response.data.errorMsg);
      })
      .finally(function()
      {
        $('#referralActivationModal').modal('hide');
        $scope.enableBtn = "Enable";
        $scope.disableToggleBtn = false;
      });
    }
  }

  $scope.disableReferral = function(form)
  {
    if(form.$valid)
    {
      $scope.disableBtn = "Disabling";
      $scope.disableToggleBtn = true;

      var updateDetails =
      {
        'userEmail' : AuthService.getEmail()
      }

      $http({
        method: 'POST',
        url: config.apiUrl + '/auth/disableReferral',
        data: updateDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })

      .then(function(response)
      {
        console.log(response);

        $scope.showCustomModal("SUCCESS", response.data.successMsg);
      },
      function(response)
      {
        $scope.showCustomModal("ERROR", response.data.errorMsg);
      })
      .finally(function()
      {
        $('#referralActivationModal').modal('hide');
        $scope.disableBtn = "Disable";
        $scope.disableToggleBtn = false;
      });
    }
  }

  $scope.showActivationModal = function ()
  {
    $scope.schoolYear = undefined;
    $scope.term = undefined;
    $scope.enableBtn = "Enable";
    $scope.disableBtn = "Disable";
    $scope.showEnable = false;

    var details =
    {
      'email' : AuthService.getEmail()
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/getCurrentReportInfo',
      data: details,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      console.log(response);

      if(response.data.schoolYear && response.data.schoolYear)
      {
        $scope.schoolYear = response.data.schoolYear;
        $scope.term = response.data.term + "";

        $scope.showEnable = true;
      }

      $('#referralActivationModal').modal('show');
    },
    function(response)
    {
      
    })
    .finally(function()
    {
            
    });
  }

  //export report to PDF
  $scope.exportReportPDF = function(report)
  {
    var doc = new jsPDF('portrait');

    var rowHeightData = 46;
    var rowHeightLabel = 38;

    doc.setFontSize(26);
    doc.setFont("helvetica");
    doc.setFontType("bold");
    doc.text('REPORT DETAILS', 68, 16);

    doc.setFontSize(15);
    doc.text('Date:', 15, rowHeightLabel);

    doc.setFontSize(12.5);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.report_date.toString('MM/dd/yy hh:mm tt'), 53);
    var lineNum1 = lines.length;
    doc.text(15, rowHeightData, lines);

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('School Year:', 70, rowHeightLabel);

    doc.setFontSize(12.5);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.school_year, 53);
    var lineNum2 = lines.length;
    doc.text(70, rowHeightData, lines);

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Term:', 130, rowHeightLabel);

    doc.setFontSize(12.5);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.term + "", 62);
    var lineNum3 = lines.length;
    doc.text(130, rowHeightData, lines);

    var rowLineNum = Math.max(lineNum1, lineNum2, lineNum3);

    rowHeightData = rowHeightData + 8 + (rowLineNum * 6);
    rowHeightLabel = rowHeightData + 8;

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Student ID:', 15, rowHeightData);

    doc.setFontSize(12.5);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.student_id, 53);
    var lineNum1 = lines.length;
    doc.text(15, rowHeightLabel, lines);

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Student Name:', 70, rowHeightData);

    doc.setFontSize(12.5);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.student_fullname, 53);
    var lineNum2 = lines.length;
    doc.text(70, rowHeightLabel, lines);

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Course:', 130, rowHeightData);

    doc.setFontSize(12.5);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.program, 62);
    var lineNum3 = lines.length;
    doc.text(130, rowHeightLabel, lines);

    var rowLineNum = Math.max(lineNum1, lineNum2, lineNum3);

    rowHeightData = rowHeightData + 16 + (rowLineNum * 6);
    rowHeightLabel = rowHeightData + 8;

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Level:', 15, rowHeightData);

    doc.setFontSize(12.5);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.level, 53);
    var lineNum1 = lines.length;
    doc.text(15, rowHeightLabel, lines);

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Referred by:', 70, rowHeightData);

    doc.setFontSize(12.5);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.faculty_fullname, 53);
    var lineNum2 = lines.length;
    doc.text(70, rowHeightLabel, lines);

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Subject:', 130, rowHeightData);

    doc.setFontSize(12.5);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.subject_name, 62);
    var lineNum3 = lines.length;
    doc.text(130, rowHeightLabel, lines);

    var rowLineNum = Math.max(lineNum1, lineNum2, lineNum3);

    rowHeightData = rowHeightData + 16 + (rowLineNum * 6);
    rowHeightLabel = rowHeightData + 8;

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Status:', 15, rowHeightData);

    doc.setFontSize(12.5);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.report_status, 53);
    var lineNum1 = lines.length;
    doc.text(15, rowHeightLabel, lines);

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Counselor\'s Note:', 70, rowHeightData);

    doc.setFontSize(12);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.counselor_note_view, 122);
    var lineNum2 = lines.length;
    doc.text(70, rowHeightLabel, lines);

    var rowLineNum = Math.max(lineNum1, lineNum2);

    rowHeightData = rowHeightData + 12 + (rowLineNum * 5);
    rowHeightLabel = rowHeightData + 8;

    doc.line(195, rowHeightData, 15, rowHeightData);

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Reason(s) for Referral:', 15, rowHeightData + 12);

    doc.setFontSize(11.3);
    doc.setFontType("normal");

    var lineCount = 0;

    for(var counter = 0; counter < report.report_reasons.length; counter++)
    {
      if(report.report_reasons[counter] == "N/A")
      {
        var lines = doc.splitTextToSize(report.report_reasons[counter], 181);
      }
      else
      {
        var lines = doc.splitTextToSize(counter + 1 + ". " + report.report_reasons[counter], 181);
      }

      var lineNum = lines.length;
      lineCount += lineNum;
      doc.text(15, rowHeightLabel + 12 + (counter * lineNum * 6), lines);
    }

    rowHeightData = rowHeightData + 16 + (lineCount * 6);
    rowHeightLabel = rowHeightData + 19;

    doc.setFontSize(15);
    doc.setFontType("bold");
    doc.text('Other Reasons:', 15, rowHeightData + 11);

    doc.setFontSize(11.3);
    doc.setFontType("normal");
    var lines = doc.splitTextToSize(report.referral_comment_view, 181);
    doc.text(15, rowHeightLabel, lines);

    doc.save(report.student_fullname.replace(/[^A-Z0-9]/ig, '').toLowerCase() + '_' + report.program.replace(/[^A-Z0-9]/ig, '').toLowerCase() + '_' + report.report_date.toString('MMddyy') + '.pdf');
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
