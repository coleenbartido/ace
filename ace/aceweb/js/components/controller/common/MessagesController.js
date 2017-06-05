angular.module('aceWeb')


.controller('MessagesController', function(config, $scope, $http, $state, $filter, $localStorage, AuthService, $interval, $filter, $rootScope)
{

  $scope.getMessageList = function()
  {
    var messageDetails =
    {
      'email' : AuthService.getEmail()
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/messages',
      data: messageDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $scope.messages = JSON.parse(response.data.messageList);

      for(var counter=0; counter < $scope.messages.length; counter++)
      {
        //convert string date into javascript date object
        strDate = $scope.messages[counter].message_date.replace(/-/g,'/');
        $scope.messages[counter].message_date = new Date(strDate);
      }

      $scope.uniqueMessages = $filter('orderBy')($scope.messages, 'message_date', true);
      $scope.uniqueMessages = $filter('unique')($scope.uniqueMessages, 'report_id');

      for(var counter=0; counter < $scope.uniqueMessages.length; counter++)
      {
        if($scope.uniqueMessages[counter].sender_email == AuthService.getEmail())
        {
          $scope.uniqueMessages[counter].is_read = $scope.uniqueMessages[counter].is_read_sender;
        }
      }
    },
    function(response)
    {
      //for checking
      console.log(response);

    })
    .finally(function()
    {

    });
  } //scope getMessageList

  $scope.initScope = function()
  {
    $scope.sendBtn = "Send";
    $scope.userEmail = AuthService.getEmail();
    $scope.searchBox = undefined;
    $scope.markMessageList = {};
    $scope.markMessageList.report_id = [];
    $scope.mainCheckbox = false;

    //for pagination
    $scope.maxSize = 5;
    $scope.currentPage = 1;
    $scope.itemsPerPage = 8;

    $scope.getMessageList();
  } //scope initScope

  $scope.initScope();

  $scope.msgPoll = $interval($scope.getMessageList, 3000);

  $scope.$on('$destroy',function()
  {
    if($scope.msgPoll)
    {
      $interval.cancel($scope.msgPoll);
    }
  })

  $scope.$watch("markMessageList.report_id", function()
  {
    $scope.mainCheckbox = false;

    if($scope.messages && $scope.filtered.length == $scope.markMessageList.report_id.length)
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
    $scope.markMessageList.report_id = [];
    $scope.mainCheckbox = false;

    if($scope.messages && $scope.filtered.length == $scope.markMessageList.report_id.length && $scope.filtered.length != 0)
    {
      $scope.mainCheckbox = true;
    }
    else
    {
      $scope.mainCheckbox = false;
    }
  }, true);

  $scope.disableActionBtn = function ()
  {
    if($scope.markMessageList.report_id == undefined || $scope.markMessageList.report_id.length == 0)
    {
      return true;
    }
    return false;
  }

  $scope.controlCheckbox = function ()
  {
    $scope.markMessageList.report_id = [];
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
        $scope.markMessageList.report_id.push($scope.filtered[counter].report_id);
      }
    }
  }

  $scope.selecAllRead = function ()
  {
    $scope.markMessageList.report_id = [];

    for(var counter=0; counter < $scope.filtered.length; counter++)
    {
      if($scope.filtered[counter].is_read == 1)
      {
        $scope.markMessageList.report_id.push($scope.filtered[counter].report_id);
      }
    }
  }

  $scope.selecAllUnread = function ()
  {
    $scope.markMessageList.report_id = [];

    for(var counter=0; counter < $scope.filtered.length; counter++)
    {
      if($scope.filtered[counter].is_read == 0)
      {
        $scope.markMessageList.report_id.push($scope.filtered[counter].report_id);
      }
    }
  }

  $scope.markAsRead = function()
  {
    $interval.cancel($rootScope.notifPoll);
    $interval.cancel($scope.msgPoll);

    var messageDetails =
    {
      'markMessageList' : $scope.markMessageList,
      'email': AuthService.getEmail()
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/markAsRead',
      data: messageDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $rootScope.getNotif();
      $scope.getMessageList();
    },
    function(response)
    {
      //for checking
      console.log(response);

    })
    .finally(function()
    {
      $scope.markMessageList.report_id = [];
      $scope.mainCheckbox = false;
      $scope.msgPoll = $interval($scope.getMessageList, 3000);
      $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);
    });
  }

  $scope.markAsUnread = function()
  {
    $interval.cancel($rootScope.notifPoll);
    $interval.cancel($scope.msgPoll);

    var messageDetails =
    {
      'markMessageList' : $scope.markMessageList,
      'email': AuthService.getEmail()
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/markAsUnread',
      data: messageDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $rootScope.getNotif();
      $scope.getMessageList();
    },
    function(response)
    {
      //for checking
      console.log(response);

    })
    .finally(function()
    {
      $scope.markMessageList.report_id = [];
      $scope.mainCheckbox = false;
      $scope.msgPoll = $interval($scope.getMessageList, 3000);
      $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);
    });
  }

  $scope.deleteMessageList = function()
  {
    $scope.deleteBtn = "Delete";

    BootstrapDialog.show({
      title: 'Delete Message',
      message: 'Are you sure you want to delete these messages?',
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
          $interval.cancel($scope.msgPoll);

          var messageDetails =
          {
            'markMessageList' : $scope.markMessageList,
            'email': AuthService.getEmail()
          }

          $http({
            method: 'POST',
            url: config.apiUrl + '/auth/deleteMessage',
            data: messageDetails,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
          })
          .then(function(response)
          {
            //for checking
            console.log(response);

            $rootScope.getNotif();
            $scope.getMessageList();

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
            $scope.markMessageList.report_id = [];
            $scope.mainCheckbox = false;
            $scope.msgPoll = $interval($scope.getMessageList, 3000);
            $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);

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

  $scope.deleteMessage = function(message)
  {
    $scope.deleteBtn = "Delete";

    BootstrapDialog.show({
      title: 'Delete Message',
      message: 'Are you sure you want to delete this message?',
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
          $interval.cancel($scope.msgPoll);

          var messageDetails =
          {
            'markMessageList' : message.report_id,
            'email': AuthService.getEmail()
          }

          $http({
            method: 'POST',
            url: config.apiUrl + '/auth/deleteMessage',
            data: messageDetails,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
          })
          .then(function(response)
          {
            //for checking
            console.log(response);

            $rootScope.getNotif();
            $scope.getMessageList();

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
            $scope.msgPoll = $interval($scope.getMessageList, 3000);
            $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);

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

  $scope.readMessage = function()
  {
    $interval.cancel($rootScope.notifPoll);
    $interval.cancel($scope.msgPoll);

    var messageDetails =
    {
      'reportId' : $scope.selectedMessage.report_id,
      'email': AuthService.getEmail()
    }

    $http({
      method: 'POST',
      url: config.apiUrl + '/auth/readMessage',
      data: messageDetails,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(function(response)
    {
      //for checking
      console.log(response);

      $rootScope.getNotif();
      $scope.getMessageList();
    },
    function(response)
    {
      //for checking
      console.log(response);

    })
    .finally(function()
    {
      $scope.msgPoll = $interval($scope.getMessageList, 3000);
      $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);
    });
  }

  $scope.showPopup = function(message)
  {
    $scope.selectedMessage = message;
    $scope.composeEmail = undefined;
    $scope.scrollOnTop = false;
    $scope.showLimit = -4;

    $scope.readMessage();
  }

  $scope.showBroadcastModal = function()
  {
    $scope.broadcastEmailForm.$setPristine();
    $scope.composeBroadcastEmail = "";
    $scope.subject = undefined;
  }

  $('#myModal').on('shown.bs.modal', function ()
  {
    $("#customTextArea").focus();
    $('#scrollableDiv').scrollTop($('#scrollableDiv')[0].scrollHeight);
  })

  $('#myModal').on('hide.bs.modal', function ()
  {
    $('#scrollableDiv').scrollTop($('#scrollableDiv')[0].scrollHeight);
  })

  $scope.incrementShowLimit = function()
  {
    $scope.showLimit -= 2;
  }

  $scope.sendEmail = function()
  {
    if($scope.composeEmail != "" && $scope.composeEmail != undefined)
    {
      $scope.sendBtn = "Sending";
      $scope.isSending = true;
      $interval.cancel($rootScope.notifPoll);
      $interval.cancel($scope.msgPoll);

      if($scope.selectedMessage.receiver_email == AuthService.getEmail())
      {
        $scope.sender = $scope.selectedMessage.receiver_email;
        $scope.receiver = $scope.selectedMessage.sender_email;
      }
      else
      {
        $scope.sender = $scope.selectedMessage.sender_email;
        $scope.receiver = $scope.selectedMessage.receiver_email;
      }

      var messageDetails =
      {
        'sender' : $scope.sender,
        'receiver': $scope.receiver,
        'messageBody': $scope.composeEmail,
        'messageSubj': $scope.selectedMessage.message_subject,
        'reportId': $scope.selectedMessage.report_id
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

        $scope.composeEmail = "";
        $scope.showLimit -= 1;
        $rootScope.getNotif();
        $scope.getMessageList();
      },
      function(response)
      {
        //for checking
        console.log(response);

      })
      .finally(function()
      {
        $scope.sendBtn = "Send";
        $scope.isSending = false;
        $scope.msgPoll = $interval($scope.getMessageList, 3000);
        $rootScope.notifPoll = $interval($rootScope.getNotif, 3000);
      });
    }
  }

  $scope.broadcastEmail = function(form)
  {
    if(form.$valid)
    {
      $scope.sendBtn = "Sending";
      $scope.isSending = true;

      var str =  $scope.composeBroadcastEmail;
      var emailBody = str.replace(new RegExp('\r?\n','g'), '<br />');

      var messageDetails =
      {
        'userEmail' : AuthService.getEmail(),
        'messageBody': emailBody,
        'messageSubj': $scope.subject
      }

      $http({
        method: 'POST',
        url: config.apiUrl + '/auth/broadcastEmail',
        data: messageDetails,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
      .then(function(response)
      {
        //for checking
        console.log(response);

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
        $('#broadcastModal').modal('hide');
        $scope.sendBtn = "Send";
        $scope.isSending = false;
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
