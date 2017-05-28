<?php

  require __DIR__ . '/../bootstrap/app.php';

  use Psr\Http\Message\ServerRequestInterface;
  use Psr\Http\Message\ResponseInterface;
  use \Firebase\JWT\JWT;//php-jwt dependecy



  $app->add(new \Slim\Middleware\JwtAuthentication([
    "path" => "/auth",
    "secure" => false,
    "secret" => $_ENV['KEY']->SECRET_KEY
  ]));



  $app->post('/auth', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    return $response->withStatus(200);
  });


  //----------------------------------------- COMMON FUNCTIONALITIES ----------------------------------------------------//

  $app->post('/login', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $loginDetails = json_decode(file_get_contents("php://input")); //php://input is a read-only stream that allows you to read raw data from the request body. json_decode function takes a JSON string and converts it into a PHP array or object

    $db = new DbOperation();

    $status = 1;
    $email = $loginDetails->email;
    $pword = $loginDetails->pword;


    if($db->loginUser($email, $pword, $status)) // if true yung e rereturn ng function na "loginUser($email, $pword)", magiging true yung condition. Thus, e execute nya yung if block. At the same time, siniset nya rin yung value ng "$response['isValidAccount']" sa kung ano ang erereturn ng function na "loginUser($email, $pword)"
    {
      $tokenId    = base64_encode(mcrypt_create_iv(32));
      $issuedAt   = time();
      $serverName = "ACE";
      $role = $db->getAccountRole($email);
      $department = "";

      if($role == 1)
      {
        $name = "Super Admin";
      }
      else if($role == 2)
      {
        $name = $db->getFirstName($email) . " " . $db->getLastName($email);
        $departmentId = $db->getDepartment($email);
        $department = $db->getDepartmentName($departmentId);
      }
      else
      {
        $name = $db->getFirstName($email) . " " . $db->getLastName($email);
      }

      //$notBefore  = $issuedAt + 10;             //Adding 10 seconds
      //$expire     = $notBefore + 60;            // Adding 60 seconds

      //Create the token as an array
      $payload =
      [
        'iat'  => $issuedAt,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => $serverName,       // Issuer
        'data' =>
        [                            // Data related to the signer user
          'email' => $email,         // User name
          'role' => $role,
          'name' => $name,
          'department' => $department
        ]
      ];

      $jwt = JWT::encode
      (
        $payload,
        $_ENV['KEY']->SECRET_KEY,
        'HS256'
      );

      logToFile($email, "logged in to the system");

      $responseBody = array('token' => $jwt);
      $response = setResponse($response, 200, $responseBody);
    }
    else
    {
      $responseBody = array('errMsg' => 'Incorrect Email or Password');
      $response = setResponse($response, 400, $responseBody);
    }
    return $response;
  });



  $app->post('/forgotPassword', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $resetDetails = json_decode(file_get_contents("php://input"));

    $db = new DbOperation();

    $email = $resetDetails->email;
    $role = $db->getAccountRole($email);

    //check kung 1 yung status ng account
    if($db->emailExist($email) == false)
    {
      $responseBody = array('errMsg' => 'Incorrect Email');
      $response = setResponse($response, 400, $responseBody);
    }
    else
    {
      $result = randStrGen();
      $hashCode = hash('sha256', $result);

      $date = getTimestamp();
      $date->modify('+1440 minutes');
      $timestamp = $date->format('Y-m-d H:i:s');

      $subject = "ACE Account Password Reset";
      $link = $_ENV['DOMAIN']->CLIENT_URL . "/resetpassword?email=" . $email . "&hashcode=" . $hashCode;
      $body =

      "Hi there, <br><br>it seems like someone requested to reset the password on your ACE Online Referral System account.
      <br><br>To reset your password, click this <a href=" . $link . ">link</a>. This link is only valid for 24 hours.
      <br>Ignore this message to keep your current password.
      <br><br><br>Thank you.";

      //send Email
      sendEmail($email, $subject, $body);

      //UPDATE HASH, TOKEN_EXP
      $db->forgotPassword($email, $hashCode, $timestamp, $role);

      $response = setSuccessResponse($response, 200);
    }
    return $response;
  });



  $app->post('/verifyToken', function(ServerRequestInterface $request, ResponseInterface $response)
  {
    $requestObj = json_decode(file_get_contents("php://input"));

    $email = $requestObj->email;
    $hashCode = $requestObj->hashcode;

    $db = new DbOperation();

    if(isset($email, $hashCode)) //server side validation if may email and hashCode parameter sa url
    {
      if($db->isTokenExpired($email) == false && $db->isLinkValid($email, $hashCode))
      {
        $response = setSuccessResponse($response, 200);
      }
      else
      {
        $responseBody = array('errMsg' => 'Invalid URL');
        $response = setResponse($response, 400, $responseBody);
      }
    }
    else
    {
      $responseBody = array('errMsg' => 'Invalid URL');
      $response = setResponse($response, 400, $responseBody);
    }
    return $response;
  });



  $app->post('/resetPassword', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $resetPassDetails = json_decode(file_get_contents("php://input"));

    $db = new DbOperation();

    $email = $resetPassDetails->email;
    $hashCode = $resetPassDetails->hashcode;
    $pword = $resetPassDetails->pword;
    $role = $db->getAccountRole($email);

    if(isset($email, $hashCode))
    {
      if($db->isLinkValid($email, $hashCode))
      {
        if( $db->changePassword($email, $pword, $role))
        {
          $response = setSuccessResponse($response, 200);
        }
        else
        {
          $responseBody = array('errMsg' => 'Failed to change password');
          $response = setResponse($response, 400, $responseBody);
        }
      }
      else
      {
        $responseBody = array('errMsg' => 'Password already set');
        $response = setResponse($response, 400, $responseBody);
      }
    }
    else
    {
      $responseBody = array('errMsg' => 'Failed to change password');
      $response = setResponse($response, 400, $responseBody);
    }
    return $response;
  });



  $app->get('/verify', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $db = new DbOperation();

    if(isset($_GET['email'], $_GET['hashCode'])) //server side validation if may email and hashCode parameter sa url
    {
      $email = $_GET['email'];
      $hashCode = $_GET['hashCode'];

      if($db->isLinkValid($email, $hashCode))
      {
        $response = setSuccessResponse($response, 200);
      }
      else
      {
        $responseBody = array('errMsg' => 'Invalid Link');
        $response = setResponse($response, 400, $responseBody);
      }
    }
    else
    {
      $responseBody = array('errMsg' => 'Invalid Link');
      $response = setResponse($response, 400, $responseBody);
    }

    return $response;
  });



  $app->post('/accountSetup', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $accountDetails = json_decode(file_get_contents("php://input"));

    if(isset($accountDetails->email, $accountDetails->hashCode))
    {
      $email = $accountDetails->email;
      $hashCode = $accountDetails->hashCode;
      $contactNumber = $accountDetails->contactNumber;
      $pword = $accountDetails->pword;
      $status = 1;

      if(isset($accountDetails->contactNumber))
      {
        $contactNumber = $accountDetails->contactNumber;
      }
      else
      {
        $contactNumber = NULL;
      }

      $db = new DbOperation();

      if($db->isLinkValid($email, $hashCode)) // if this returns true, perform the if statement
      {
        $db->setupAccountDetails($email, $hashCode, $contactNumber, $pword, $status);

        $response = setSuccessResponse($response, 200);
      }
      else
      {
        $responseBody = array('errMsg' => 'Account already activated');
        $response = setResponse($response, 400, $responseBody);
      }
    }
    else
    {
      $responseBody = array('errMsg' => 'Account already activated');
      $response = setResponse($response, 400, $responseBody);
    }

    return $response;
  });



  //------------ Messages not applicable for Super Admin

  $app->post('/auth/messages', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $messageDetails = json_decode(file_get_contents("php://input"));

    $email = $messageDetails->email;
    $status = 1;

    $db = new DbOperation();

    $messageList = $db->showMessages($email,$status);

    for($counter=0; $counter < count($messageList); $counter++)
    {
      $messageList[$counter]['sender_fname'] = $db->getFirstName($messageList[$counter]['sender_email']);
      $messageList[$counter]['sender_lname'] = $db->getLastName($messageList[$counter]['sender_email']);
      $messageList[$counter]['receiver_fname'] = $db->getFirstName($messageList[$counter]['receiver_email']);
      $messageList[$counter]['receiver_lname'] = $db->getLastName($messageList[$counter]['receiver_email']);


      if($messageList[$counter]['sender_email'] == $email)
      {
        $messageList[$counter]['sender_fullName'] = "Me";
      }
      else
      {
        $messageList[$counter]['sender_fullName'] = $messageList[$counter]['sender_fname'] . " " . $messageList[$counter]['sender_lname'];
      }

      if($messageList[$counter]['message_subject'] == null)
      {
        $messageList[$counter]['message_subject'] = "(No Subject)";
      }
    }

    $responseBody = array('messageList' => json_encode($messageList));
    $response = setResponse($response, 200, $responseBody);

    return $response;
  });


  $app->post('/auth/markAsRead', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $messageDetails = json_decode(file_get_contents("php://input"));

    $messageList = $messageDetails->markMessageList;
    $email = $messageDetails->email;
    $isRead = 1;

    $db = new DbOperation();

    foreach ($messageList->report_id as $value)
    {
      $db->markMessage($isRead, $value, $email);
    }

    $response = setSuccessResponse($response, 200);

    return $response;
  });



  $app->post('/auth/markAsUnread', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $messageDetails = json_decode(file_get_contents("php://input"));

    $messageList = $messageDetails->markMessageList;
    $email = $messageDetails->email;
    $isRead = 0;

    $db = new DbOperation();

    foreach ($messageList->report_id as $value)
    {
      $db->markMessage($isRead, $value, $email);
    }

    $response = setSuccessResponse($response, 200);

    return $response;
  });



  $app->post('/auth/readMessage', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $messageDetails = json_decode(file_get_contents("php://input"));

    $reportId = $messageDetails->reportId;
    $email = $messageDetails->email;
    $isRead = 1;

    $db = new DbOperation();

    $db->markMessage($isRead, $reportId, $email);

    $response = setSuccessResponse($response, 200);

    return $response;
  });


  $app->post('/auth/deleteMessage', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $messageDetails = json_decode(file_get_contents("php://input"));

    $messageList = $messageDetails->markMessageList;
    $email = $messageDetails->email;
    $status = 0;
    $deleteSuccess = false;

    $db = new DbOperation();

    if(is_object($messageList))
    {
      foreach ($messageList->report_id as $value)
      {
        $deleteSuccess = $db->deleteMessage($status, $value, $email);
      }
    }
    else
    {
      $deleteSuccess = $db->deleteMessage($status, $messageList, $email);
    }

    if($deleteSuccess)
    {
      $responseBody = array('successMsg' => 'Message(s) successfully deleted');
      $response = setResponse($response, 200, $responseBody);
    }
    else
    {
      $responseBody = array('errorMsg' => 'Failed to delete message(s)');
      $response = setResponse($response, 400, $responseBody);
    }

    return $response;
  });



  $app->post('/auth/changePasswordInSettings', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $changePassDetails = json_decode(file_get_contents("php://input"));

    $db = new DbOperation();

    $email = $changePassDetails->email;
    $pword = $changePassDetails->pword;
    $oldPword = $changePassDetails->oldPword;
    $role = $db->getAccountRole($email);

    if($db->isPasswordValid($email, $pword, $oldPword) == "Valid Password")
    {
      $db->changePassword($email, $pword, $role);

      $responseBody = array('successMsg' => 'Password successfully updated');
      $response = setResponse($response, 200, $responseBody);
    }
    else if($db->isPasswordValid($email, $pword, $oldPword) == "Same Password")
    {
      $responseBody = array('errorMsg' => 'Invalid new password');
      $response = setResponse($response, 400, $responseBody);
    }
    else
    {
      $responseBody = array('errorMsg' => 'Invalid password');
      $response = setResponse($response, 400, $responseBody);
    }

    return $response;
  });

  //------- Change Contact not applicable for Super Administrator

  $app->post('/auth/changeContact', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $changeContactDetails = json_decode(file_get_contents("php://input"));

    $email = $changeContactDetails->email;
    $contactNum = $changeContactDetails->contactNum;

    $db = new DbOperation();

    if($db->changeContact($email, $contactNum))
    {
      $responseBody = array('successMsg' => 'Contact number successfully updated');
      $response = setResponse($response, 200, $responseBody);
    }
    else
    {
      $responseBody = array('errMsg' => 'Failed Change Contact');
      $response = setResponse($response, 400, $responseBody);
    }
    return $response;
  });


  //------- Get Contact Number not applicable for Super Administrator Module

  $app->post('/auth/getContactNum', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $accountDetails = json_decode(file_get_contents("php://input"));

    $email = $accountDetails->email;

    $db = new DbOperation();

    if($db->getContactNum($email))
    {
      $responseBody = array('contactNum' => $db->getContactNum($email));
      $response = setResponse($response, 200, $responseBody);
    }

    return $response;
  });

  /*$app->post('/auth/getUserInfo', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $accountDetails = json_decode(file_get_contents("php://input"));

    $email = $accountDetails->email;

    $db = new DbOperation();

    if($db->getUserInfo($email))
    {
      $responseBody = array('contactNum' => $db->getContactNum($email));
      $response = setResponse($response, 200, $responseBody);
    }

    return $response;
  });*/



  //used to edit faculty or administrator's information (ex. name)
  $app->post('/auth/updateAccount', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $accountDetails = json_decode(file_get_contents("php://input"));

    $userEmail = $accountDetails->userEmail;
    $email = $accountDetails->email;
    $lastName = $accountDetails->lastName;
    $firstName = $accountDetails->firstName;
    $userType = $accountDetails->userType;
    $department = $accountDetails->department;

    $updateSuccess = false;
    //$updateSuccess2 = true;

    $db = new DbOperation();

    $updateSuccess = $db->updateAccount($email, $lastName, $firstName);

    if($userType == 2)
    {
      $updateSuccess2 = $db->updateDepartment($email, $department);
    }

    //echo $updateSuccess2;

    if($updateSuccess && $updateSuccess2)
    {
      if($userType == 2)
      {
        $message = 'Administrator profile successfully updated';
        $logMessage = "has successfully updated administrator profile.";
      }
      else
      {
        $message = 'Faculty profile successfully updated';
        $logMessage = "has successfully updated faculty profile.";
      }

      logToFile($userEmail, $logMessage);

      $responseBody = array('successMsg' => $message );
      $response = setResponse($response, 200, $responseBody);
    }
    else
    {
      if($userType == 2)
      {
        $message = 'Failed to update administrator profile';
      }
      else
      {
          $message = 'Failed to update faculty profile';
      }
      $responseBody = array('errorMsg' => $message );

      $response = setResponse($response, 400, $responseBody);
    }

    return $response;
  });



  $app->get('/downloadUserManual/{role}', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $role = $request->getAttribute('role');

    if($role == "1")
    {
      $file = $_ENV['PATH']->USER_MANUAL_PATH . 'superadmin_user_manual.pdf';
    }
    else if($role == "2")
    {
      $file = $_ENV['PATH']->USER_MANUAL_PATH . 'admin_user_manual.pdf';
    }
    else if($role == "3")
    {
      $file = $_ENV['PATH']->USER_MANUAL_PATH . 'faculty_user_manual.pdf';
    }

    $response = $response->withHeader('Content-Description', 'File Transfer')
    ->withHeader('Content-Type', 'application/pdf')
    ->withHeader('Content-Disposition', 'attachment;filename="'.basename($file).'"')
    ->withHeader('Expires', '0')
    ->withHeader('Cache-Control', 'must-revalidate')
    ->withHeader('Pragma', 'public')
    ->withHeader('Content-Length', filesize($file));

    readfile($file);

    return $response;
  });

    //----------------------------------------- FACULTY FUNCTIONALITIES  ----------------------------------------------------//
    //---------------------------- functionalities that use the faculty controller ------------------------------------------//

    $app->post('/auth/getNotifList', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $accountDetails = json_decode(file_get_contents("php://input"));

      $email = $accountDetails->email;

      $db = new DbOperation();

      $referralUpdateCount = $db->getReferralUpdateCount($email);
      $newMessageCount = $db->getNewMessageCount($email);

      $responseBody = array('referralUpdateCount' => $referralUpdateCount, 'newMessageCount' => $newMessageCount);
      $response = setResponse($response, 200, $responseBody);

      return $response;
    });


    //typeahead (autocomplete)
    $app->post('/auth/getStudentInfo', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $studentInfo = json_decode(file_get_contents("php://input"));

      $studId = $studentInfo->studId;

      $db = new DbOperation();

      $studInfoList = $db->getStudentInfo($studId);

      for($counter=0; $counter < count($studInfoList); $counter++)
      {
        $studInfoList[$counter]['level'] = $db->getStudentLevel($studInfoList[$counter]['student_id'], $studInfoList[$counter]['department_id']);
        $studInfoList[$counter]['program'] = $db->getStudentProgram($studInfoList[$counter]['student_id'], $studInfoList[$counter]['department_id']);
      }

      $responseBody = array('studInfoList' => json_encode($studInfoList));
      $response = setResponse($response, 200, $responseBody);

      return $response;
    });



    $app->post('/auth/referralForm', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $reportDetails = json_decode(file_get_contents("php://input"));

      $email = $reportDetails->email;
      $studId = $reportDetails->studId;
      $department = $reportDetails->department;
      $studFName = $reportDetails->studFName;
      $studLName = $reportDetails->studLName;
      $subjName = $reportDetails->subjName;
      $schoolTerm = $reportDetails->schoolTerm;
      $schoolYear = $reportDetails->schoolYear;
      $course = $reportDetails->course;
      $year = $reportDetails->year;
      $reasons = $reportDetails->reason;
      $isActive = 1;

      if($reasons[6]->check && isset($reasons[6]->value))
      {
        $refComment = $reasons[6]->value;
      }
      else
      {
        $refComment = NULL;
      }

      $db = new DbOperation();

      $last_name = $db->getFirstName($email);
      $first_name = $db->getLastName($email);
      $full_name = $first_name . "  " .$last_name;

      if($db->insertStudent($studId, $department, $studFName, $studLName, $course, $year) && $db->insertReport($email, $studId, $department, $subjName, $schoolTerm, $schoolYear, $refComment, $reasons) && $db->updateReportCount($email))
      {
        $emailList = $db->getAdminAccounts($department, $isActive);

        $subject = "ACE Submitted Report";
        $link = $_ENV['DOMAIN']->CLIENT_URL;
        $body =

          "Greetings, <br><br>" . $full_name . " submitted a referral!
          <br><br>To view the submitted report, login <a href=" . $link . ">here</a>.
          <br><br><br>Thank you.";

        sendEmail($emailList, $subject, $body);

        logToFile($email, "has successfully submitted a referral.");

        $responseBody = array('successMsg' => "Referral form successfully submitted");
        $response = setResponse($response, 200, $responseBody);
      }
      else
      {
        $responseBody = array('errorMsg' => "Failed to submit the referral form");
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });


    $app->post('/auth/referralHistory', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $reportDetails = json_decode(file_get_contents("php://input"));

      $db = new DbOperation();

      $email = $reportDetails->email;

      $reportsList = $db->getFacultyReferral($email);

      for($counter=0; $counter<count($reportsList); $counter++)
      {
        $reportsList[$counter]['student_fullname'] = $reportsList[$counter]['student_fname'] . " " . $reportsList[$counter]['student_lname'];
        $reportsList[$counter]['is_updated'] = $db->getUpdateStatus($reportsList[$counter]['report_id']);

        $reasonArr = $db->getReferralReasons($reportsList[$counter]['report_id']);

        for($ctr=0; $ctr<count($reasonArr); $ctr++)
        {
          $reportsList[$counter]['report_reasons'][$ctr] = $reasonArr[$ctr]['referral_reason'];
        }
      }

      $responseBody = array('reportsList' => json_encode($reportsList));
      $response = setResponse($response, 200, $responseBody);

      return $response;
    });



    $app->post('/auth/markFacultyReport', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $reportDetails = json_decode(file_get_contents("php://input"));

      $reportList = $reportDetails->reportId;
      $status = 0;

      $db = new DbOperation();

      if(is_object($reportList))
      {
        foreach ($reportList->report_id as $value)
        {
          $db->markUpdatedReport($status, $value);
        }
      }
      else
      {
        $db->markUpdatedReport($status, $reportList);
      }

      $response = setSuccessResponse($response, 200);

      return $response;
    });



    $app->post('/auth/markFacultyReportAsUnread', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $reportDetails = json_decode(file_get_contents("php://input"));

      $reportList = $reportDetails->reportId;
      $status = 1;

      $db = new DbOperation();

      if(is_object($reportList))
      {
        foreach ($reportList->report_id as $value)
        {
          $db->markUpdatedReport($status, $value);
        }
      }
      else
      {
        $db->markUpdatedReport($status, $reportList);
      }

      $response = setSuccessResponse($response, 200);

      return $response;
    });


    //----------------------------------------- ADMINISTRATOR FUNCTIONALITIES -----------------------------------------------//
    //---------------------------- functionalities that use the administrator controller ------------------------------------//

    $app->post('/auth/getAdminNotifList', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $accountDetails = json_decode(file_get_contents("php://input"));

      $db = new DbOperation();

      $email = $accountDetails->email;
      $department = $db->getDepartment($email);

      $uncounseledReportCount = $db->getUncounseledReportCount($department);
      $newMessageCount = $db->getNewMessageCount($email);

      $responseBody = array('uncounseledReportCount' => $uncounseledReportCount, 'newMessageCount' => $newMessageCount);
      $response = setResponse($response, 200, $responseBody);

      return $response;
    });


    //lists faculty accounts
    $app->post('/auth/listFaculty', function (ServerRequestInterface $request, ResponseInterface $response)
    {

      $db = new DbOperation();

      $userType = 3;

      $facultyList = $db->listAccounts($userType);

      for($counter=0; $counter < count($facultyList); $counter++){

        $facultyList[$counter]['reported_count'] = $db->getReportCount($facultyList[$counter]['email']);

        if($facultyList[$counter]['contact_number'] == null)
        {
          $facultyList[$counter]['contact_number'] = "N/A";
        }

        if($db->isAccountActive($facultyList[$counter]['email']))
        {
          $facultyList[$counter]['status'] = "ACTIVE";
        }
        else
        {
          $facultyList[$counter]['status'] = "PENDING";
        }
      }

      $responseBody = array('facultyList' => json_encode($facultyList));
      $response = setResponse($response, 200, $responseBody);

      return $response;
    });


    //lists students
    $app->post('/auth/listStudent', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $adminDetails = json_decode(file_get_contents("php://input"));

      $db = new DbOperation();

      $email = $adminDetails->email;
      $status = 1;
      $department = $db->getDepartment($email);

      if($department==1)
      {
        $studentList = $db->listShsStudent($status);
      }
      else
      {
        $studentList = $db->listCollegeStudent($status);
      }

      for($counter=0; $counter < count($studentList); $counter++)
      {
        $studentList[$counter]['department_id'] = $department;
        $studentList[$counter]['student_name'] = $studentList[$counter]['first_name'] . " " . $studentList[$counter]['last_name'];
      }

      $responseBody = array('studentList' => json_encode($studentList));
      $response = setResponse($response, 200, $responseBody);

      return $response;
    });


    $app->post('/auth/deleteStudent', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $deleteStudentDetails = json_decode(file_get_contents("php://input"));

      $student = $deleteStudentDetails->studentList;
      $email = $deleteStudentDetails->email;
      $status = 0;
      $deleteSuccess = false;

      $db = new DbOperation();

      $department = $db->getDepartment($email);

      if(is_object($student))
      {
        foreach($student->student_id as $student)
        {
          $deleteSuccess = $db->deleteStudent($student, $department, $status);
        }
      }
      else
      {
        $deleteSuccess = $db->deleteStudent($student, $department, $status);
      }

      if($deleteSuccess)
      {

        logToFile($email, "has  successfully deleted student record/s.");

        $responseBody = array('successMsg' => 'Student(s) successfully deleted');
        $response = setResponse($response, 200, $responseBody);
      }
      else
      {
        $responseBody = array('errorMsg' => 'Failed to delete student(s)');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });



    $app->post('/auth/updateStudent', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $studentDetails = json_decode(file_get_contents("php://input"));

      $email = $studentDetails->email;
      $studentId = $studentDetails->studentId;
      $originalId = $studentDetails->originalId;
      $lastName = $studentDetails->lastName;
      $firstName = $studentDetails->firstName;
      $program = $studentDetails->program;
      $level = $studentDetails->level;
      $updateSuccess = false;

      $db = new DbOperation();

      $department = $db->getDepartment($email);

      if($department == 1)
      {
        $updateSuccess = $db->updateShsStudent($studentId, $originalId, $lastName, $firstName, $program, $level);
      }
      else
      {
        $updateSuccess = $db->updateCollegeStudent($studentId, $originalId, $lastName, $firstName, $program, $level);
      }

      if($updateSuccess)
      {
        logToFile($email, "has successfully updated student record/s.");

        $responseBody = array('successMsg' => 'Student profile successfully updated');
        $response = setResponse($response, 200, $responseBody);
      }
      else
      {
        $responseBody = array('errorMsg' => 'Failed to update student profile');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });



    $app->post('/auth/reports', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $reportDetails = json_decode(file_get_contents("php://input"));

      $db = new DbOperation();

      $email = $reportDetails->email;
      $department = $db->getDepartment($email);
      $status = 1;

      if($department == 1)
      {
        $reportsList = $db->listShsReports($status);
      }
      else
      {
        $reportsList = $db->listCollegeReports($status);
      }

      for($counter=0; $counter<count($reportsList); $counter++)
      {
        $reportsList[$counter]['faculty_fullname'] = $reportsList[$counter]['faculty_fname'] . " " . $reportsList[$counter]['faculty_lname'];
        $reportsList[$counter]['student_fullname'] = $reportsList[$counter]['student_fname'] . " " . $reportsList[$counter]['student_lname'];

        $reasonArr = $db->getReferralReasons($reportsList[$counter]['report_id']);

        for($ctr=0; $ctr<count($reasonArr); $ctr++)
        {
          $reportsList[$counter]['report_reasons'][$ctr] = $reasonArr[$ctr]['referral_reason'];
        }
      }

      $responseBody = array('reportsList' => json_encode($reportsList));
      $response = setResponse($response, 200, $responseBody);

      return $response;
    });


    $app->post('/auth/updateStatus', function (ServerRequestInterface $request, ResponseInterface $response)
    {
        $updateDetails = json_decode(file_get_contents("php://input"));

        $db = new DbOperation();

        $userEmail = $updateDetails->userEmail;
        $email = $updateDetails->email;
        $reportId = $updateDetails->reportId;
        $status = $updateDetails->prevReportStatus;
        $updateStatus = $updateDetails->reportStatus;
        $isUpdated = 1;

        if(isset($updateDetails->comment) && $updateDetails->comment != "")
        {
          $comment = $updateDetails->comment;
        }
        else
        {
          $comment = null;
        }

        if($db->updateStatus($reportId, $updateStatus, $comment))
        {
          if($status != $updateStatus)
          {
            $db->setReporAsUpdated($reportId, $isUpdated);

            $subject = "ACE Submitted Report Status";
            $link = $_ENV['DOMAIN']->CLIENT_URL;
            $body =
              "The administrator updated the status of your submitted report from " . $db->getReportStatusName($status) . " to " . $db->getReportStatusName($updateStatus) . ".
              <br><br>
              If you wish to submit another report, login <a href=" . $link . ">here</a>. Thank you.";

            //send Email
            sendEmail($email, $subject, $body);
          }

          logToFile($userEmail, "has successfully updated the status of a report.");

          $responseBody = array('successMsg' => 'Report status successfully updated');
          $response = setResponse($response, 200, $responseBody);
        }
        else
        {
          $responseBody = array('errorMsg' => 'Report status failed to update');
          $response = setResponse($response, 400, $responseBody);
        }

        return $response;
    });


    $app->post('/auth/sendMessage', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $messageDetails = json_decode(file_get_contents("php://input"));

      $report_id = $messageDetails->reportId;
      $sender = $messageDetails->sender;
      $receiver = $messageDetails->receiver;
      $message = $messageDetails->messageBody;
      $subject = $messageDetails->messageSubj;
      $isRead = 0;
      $status = 1;

      $date = getTimestamp();
      $timestamp = $date->format('Y-m-d H:i:s');

      $db = new DbOperation();

      $last_name = $db->getFirstName($sender);
      $first_name = $db->getLastName($sender);

      if($db->insertMessage($report_id, $sender, $receiver, $message, $subject, $isRead, $status, $timestamp))
      {
        $subject = "ACE Message";
        $link = $_ENV['DOMAIN']->CLIENT_URL;
        $body =

          "Greetings, <br><br>" . $first_name . " " . $last_name . " sent you a message regarding the referral that have been submitted!
          <br><br>To view the message, login <a href=" . $link . ">here</a>.
          <br><br><br>Thank you.";

        //send Email
        sendEmail($receiver, $subject, $body);

        logToFile($sender, "has successfully sent a message.");

        $responseBody = array('successMsg' => 'Message sent');
        $response = setResponse($response, 200, $responseBody);
      }
      else
      {
        $responseBody = array('errorMsg' => 'Message sending failed');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });



    $app->post('/auth/markReport', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $reportDetails = json_decode(file_get_contents("php://input"));

      $reportList = $reportDetails->reportList;
      $isRead = 1;

      $db = new DbOperation();

      foreach ($reportList->report_id as $value)
      {
        $db->markReport($isRead, $value);
      }

      $response = setSuccessResponse($response, 200);

      return $response;
    });



    $app->post('/auth/markReportAsUnread', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $reportDetails = json_decode(file_get_contents("php://input"));

      $reportList = $reportDetails->reportList;
      $isRead = 0;

      $db = new DbOperation();

      foreach ($reportList->report_id as $value)
      {
        $db->markReport($isRead, $value);
      }

      $response = setSuccessResponse($response, 200);

      return $response;
    });



    $app->post('/auth/readReport', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $reportDetails = json_decode(file_get_contents("php://input"));

      $reportId = $reportDetails->reportId;
      $email = $reportDetails->email;
      $isRead = $reportDetails->isRead;
      $status = 1;

      $db = new DbOperation();

      if($isRead == 0)
      {
        $subject = "ACE Submitted Report Status";
        $link = $_ENV['DOMAIN']->CLIENT_URL;
        $body =
          "The report you submitted has been read by the administrator.
          <br><br>
          If you wish to submit another report, login <a href=" . $link . ">here</a>. Thank you.";

        sendEmail($email, $subject, $body);

        $db->markReport($status, $reportId);
      }

      $response = setSuccessResponse($response, 200);

      return $response;
    });


    $app->post('/auth/deleteReport', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $deleteReportDetails = json_decode(file_get_contents("php://input"));

      $userEmail = $deleteReportDetails->userEmail;
      $reports = $deleteReportDetails->reportList;
      $deleteSuccess = false;

      $db = new DbOperation();

      if(is_object($reports))
      {
        foreach($reports->report_id as $report)
        {
          $deleteSuccess = $db->deleteReport($report);
        }
      }
      else
      {
        $deleteSuccess = $db->deleteReport($reports);
      }

      if($deleteSuccess)
      {
        logToFile($userEmail, "has successfully deleted report/s.");

        $responseBody = array('successMsg' => 'Report(s) successfully deleted');
        $response = setResponse($response, 200, $responseBody);
      }
      else
      {
        $responseBody = array('errorMsg' => 'Failed to delete report(s)');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });


    $app->post('/auth/registerFaculty', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $registerDetails = json_decode(file_get_contents("php://input"));

      $userEmail = $registerDetails->userEmail;
      $email = $registerDetails->email;
      $fName = $registerDetails->fName;
      $lName = $registerDetails->lName;
      $status = 0;
      $userType = 3;

      $db = new DbOperation();

      if($db->emailExist($email))
      {
        $responseBody = array('errorMsg' => 'emailExist');
        $response = setResponse($response, 400, $responseBody);
      }
      else
      {
        $result = randStrGen();
        $hashCode = hash('sha256', $result);

        $subject = "Verify your ACE Program Account";
        $link = $_ENV['DOMAIN']->CLIENT_URL . "/accountsetup?email=" . $email . "&hashcode=" . $hashCode;
        $body =

        "Greetings! <br><br>An ACE Online Referral System account was created by the Administrator.
        <br><br>Click <a href=" . $link . ">here</a> to set your password and contact number.
        <br><br><br>Thank you.";

        if($db->registerFaculty($email, $fName, $lName, $status, $userType, $hashCode))
        {
          sendEmail($email, $subject, $body);

          logToFile($userEmail, "has successfully created a faculty account.");

          $responseBody = array('successMsg' => 'Account successfully created');
          $response = setResponse($response, 200, $responseBody);
        }
      }

      return $response;
    });


    $app->post('/auth/deleteFaculty', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $deleteFacultyDetails = json_decode(file_get_contents("php://input"));

      $userEmail = $deleteFacultyDetails->userEmail;
      $email = $deleteFacultyDetails->facultyList;
      $status = 0;
      $deleteSuccess = false;

      $db = new DbOperation();

      if(is_object($email))
      {
        foreach($email->email as $user)
        {
          $deleteSuccess = $db->deleteUser($user, $status);
        }
      }
      else
      {
        $deleteSuccess = $db->deleteUser($email, $status);
      }

      if($deleteSuccess)
      {

        logToFile($userEmail, "has successfully deleted faculty account/s.");

        $responseBody = array('successMsg' => 'Account(s) successfully deleted');
        $response = setResponse($response, 200, $responseBody);
      }
      else
      {
        $responseBody = array('errorMsg' => 'Failed to delete account(s)');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });




    $app->post('/auth/verifyAdminAccount', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $databaseDetails = json_decode(file_get_contents("php://input"));

      $status = 1;
      $email = $databaseDetails->email;
      $password = $databaseDetails->password;

      $db = new DbOperation();

      if($db->verifyAdminAccount($email, $password, $status))
      {
        $response = setSuccessResponse($response, 200);
      }
      else
      {
        $responseBody = array('errorMsg' => 'Incorrect Password');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });



    $app->post('/auth/downloadBackup', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $databaseDetails = json_decode(file_get_contents("php://input"));

      $status = 1;
      $email = $databaseDetails->email;
      $password = $databaseDetails->password;

      $db = new DbOperation();

      if($db->verifyAdminAccount($email, $password, $status))
      {
        $timestamp = getTimeStamp()->format('m-d-y-H-i');
        $backupFile = $_ENV['PATH']->BACKUP_PATH . "ace_backup_" . $timestamp . ".sql";

        $backupFailed = backupDatabase($backupFile);

        if (!$backupFailed)
        {
          $response = $response->withHeader('Content-Description', 'File Transfer')
          ->withHeader('Content-Type', 'application/octet-stream')
          ->withHeader('Content-Disposition', 'attachment;filename="'.basename($backupFile).'"')
          ->withHeader('Expires', '0')
          ->withHeader('Cache-Control', 'must-revalidate')
          ->withHeader('Pragma', 'public')
          ->withHeader('Content-Length', filesize($backupFile));

          readfile($backupFile);

          unlink($backupFile);

          logToFile($email, "has successfully download a copy of the database");
        }
        else
        {
          $responseBody = array('errorMsg' => 'Failed to create a backup file');
          $response = setResponse($response, 400, $responseBody);
        }
      }
      else
      {
        $responseBody = array('errorMsg' => 'Incorrect Password');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });



    $app->post('/auth/resetDatabase', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $databaseDetails = json_decode(file_get_contents("php://input"));

      $status = 1;
      $email = $databaseDetails->email;
      $password = $databaseDetails->password;

      $db = new DbOperation();

      if($db->verifyAdminAccount($email, $password, $status))
      {
        $timestamp = getTimeStamp()->format('m-d-y-H-i');
        $backupFile = $_ENV['PATH']->BACKUP_PATH . "ace_backup_" . $timestamp . ".sql";

        $backupFailed = backupDatabase($backupFile);

        if($db->resetDatabase())
        {
          logToFile($email, "has successfully reset the database");

          $responseBody = array('successMsg' => 'Database successfully reset');
          $response = setResponse($response, 200, $responseBody);
        }
        else
        {
          $responseBody = array('errorMsg' => 'Failed to reset the database');
          $response = setResponse($response, 400, $responseBody);
        }
      }
      else
      {
        $responseBody = array('errorMsg' => 'Incorrect Password');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });



    $app->post('/auth/restoreBackup', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $status = 1;
      $email = $_POST['email'];
      $password = $_POST['password'];
      $restoreFile = $_FILES["file"]["tmp_name"];

      $db = new DbOperation();

      if($db->verifyAdminAccount($email, $password, $status))
      {
        if(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION) == "sql")
        {
          if($_FILES["file"]["size"] == 0)
          {
            $responseBody = array('errorMsg' => 'Empty backup file');
            $response = setResponse($response, 400, $responseBody);
          }
          else
          {
            $dbName = $_ENV['DB']->DB_NAME;
            $dbUsername = $_ENV['DB']->DB_USERNAME;
            $dbPword = $_ENV['DB']->DB_PASSWORD;
            $timestamp = getTimeStamp()->format('m-d-y-H-i');
            $backupFile = $_ENV['PATH']->BACKUP_PATH . "ace_backup_" . $timestamp . ".sql";

            $backupFailed = backupDatabase($backupFile);

            $command = $_ENV['PATH']->COMMAND_PATH_RESTORE . "-u $dbUsername --password=$dbPword $dbName < $restoreFile";
            exec($command, $output, $restoreFailed);

            if (!$restoreFailed)
            {
              logToFile($email, "has successfully restored the database");

              $responseBody = array('successMsg' => "Backup file successfully restored");
              $response = setResponse($response, 200, $responseBody);
            }
            else
            {
              $responseBody = array('errorMsg' => 'Failed to restore backup file');
              $response = setResponse($response, 400, $responseBody);
            }
          }
        }
        else
        {
          $responseBody = array('errorMsg' => 'Invalid backup file');
          $response = setResponse($response, 400, $responseBody);
        }
      }
      else
      {
        $responseBody = array('errorMsg' => 'Authentication failed');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });



    //------------------- GET SUMMARY -----------//

    $app->post('/auth/getSYList', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $accountDetails = json_decode(file_get_contents("php://input"));

      $db = new DbOperation();

      $email = $accountDetails->email;
      $department = $db->getDepartment($email);
      $status = 1;

      $responseBody = array('SYList' => json_encode($db->getSYList($department, $status)));
      $response = setResponse($response, 200, $responseBody);

      return $response;
    });



    $app->post('/auth/getChartData', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $accountDetails = json_decode(file_get_contents("php://input"));

      $db = new DbOperation();

      $email = $accountDetails->email;
      $schoolYear = $accountDetails->schoolYear;
      $department = $db->getDepartment($email);
      $status = 1;

      $responseBody = array('department' => $department, 'termData' => json_encode($db->getTermData($department, $status, $schoolYear)), 'programData' => json_encode($db->getProgramData($department, $status, $schoolYear)), 'levelData' => json_encode($db->getLevelData($department, $status, $schoolYear)), 'reasonData' => json_encode($db->getReasonData($department, $status, $schoolYear)), 'statusData' => json_encode($db->getStatusData($department, $status, $schoolYear)));

      $response = setResponse($response, 200, $responseBody);
      return $response;
    });



    $app->post('/auth/broadcastEmail', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $messageDetails = json_decode(file_get_contents("php://input"));

      $userEmail = $messageDetails->userEmail;
      $body = $messageDetails->messageBody;
      $userType = 3;
      $status = 1;

      if(isset($messageDetails->messageSubj))
      {
        $subject = $messageDetails->messageSubj;
      }
      else
      {
        $subject = "";
      }

      $db = new DbOperation();

      $receiver = $db->getFacultyAccounts($userType, $status);

      //send Email
      if(sendEmail($receiver, $subject, $body))
      {

        logToFile($userEmail, "has successfully sent a broadcast email to all faculty.");

        $responseBody = array('successMsg' => 'Email sent');
        $response = setResponse($response, 200, $responseBody);
      }
      else
      {
        $responseBody = array('errorMsg' => 'Email sending failed');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });


    //----------------------------------------- SUPER ADMINISTRATOR FUNCTIONALITIES -----------------------------------------//
    //---------------------------- functionalities that use the super administrator controller -----------------------------//

    //lists admin accounts
    $app->post('/auth/listAdmin', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $db = new DbOperation();

      $userType = 2;

      $adminList = $db->listAccounts($userType);

      for($counter=0; $counter < count($adminList); $counter++)
      {
        $departmentId = $db->getDepartment($adminList[$counter]['email']);
        $adminList[$counter]['department_id'] = $departmentId;
        $adminList[$counter]['department'] = $db->getDepartmentName($departmentId);

        if($adminList[$counter]['contact_number'] == null)
        {
          $adminList[$counter]['contact_number'] = "N/A";
        }

        if($db->isAccountActive($adminList[$counter]['email']))
        {
          $adminList[$counter]['status'] = "ACTIVE";
        }
        else
        {
          $adminList[$counter]['status'] = "PENDING";
        }
      }

      $responseBody = array('adminList' => json_encode($adminList));
      $response = setResponse($response, 200, $responseBody);

      return $response;
    });


    $app->post('/auth/registerAdmin', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $registerDetails = json_decode(file_get_contents("php://input"));

      $userEmail = $registerDetails->userEmail;
      $email = $registerDetails->email;
      $fName = $registerDetails->fName;
      $lName = $registerDetails->lName;
      $department = $registerDetails->department;
      $status = 0;
      $userType = 2;

      $db = new DbOperation();

      if($db->emailExist($email))
      {
        $responseBody = array('errorMsg' => 'emailExist');
        $response = setResponse($response, 400, $responseBody);
      }
      else
      {
        $result = randStrGen();
        $hashCode = hash('sha256', $result);

        $subject = "Verify your ACE Program Account";
        $link = $_ENV['DOMAIN']->CLIENT_URL . "/accountsetup?email=" . $email . "&hashcode=" . $hashCode;
        $body =

        "Greetings! <br><br>An ACE Online Referral System account was created by the Super Administrator.
        <br><br>Click <a href=" . $link . ">here</a> to set your password and contact number.
        <br><br><br>Thank you.";

        if($db->registerAdmin($email, $fName, $lName, $status, $userType, $hashCode, $department))
        {
          sendEmail($email, $subject, $body);

          logToFile($userEmail, "has successfully created an administrator account.");

          $responseBody = array('successMsg' => 'Account successfully created');
          $response = setResponse($response, 200, $responseBody);
        }
      }

      return $response;
    });



    $app->post('/auth/deleteAdmin', function (ServerRequestInterface $request, ResponseInterface $response)
    {
      $deleteAdminDetails = json_decode(file_get_contents("php://input"));

      $userEmail = $deleteAdminDetails->userEmail;
      $email = $deleteAdminDetails->adminList;
      $status = 0;
      $deleteSuccess = false;

      $db = new DbOperation();

      if(is_object($email))
      {
        foreach($email->email as $user)
        {
          $deleteSuccess = $db->deleteUser($user, $status);
        }
      }
      else
      {
        $deleteSuccess = $db->deleteUser($email, $status);
      }

      if($deleteSuccess)
      {
        logToFile($userEmail, "has successfully deleted administrator account/s.");

        $responseBody = array('successMsg' => 'Account(s) successfully deleted');
        $response = setResponse($response, 200, $responseBody);
      }
      else
      {
        $responseBody = array('errorMsg' => 'Failed to delete account(s)');
        $response = setResponse($response, 400, $responseBody);
      }

      return $response;
    });



    $app->run();
