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


  //new
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

      if($role == 1)
      {
        $name = "Super Admin";
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
          'name' => $name
        ]
      ];

      $jwt = JWT::encode
      (
        $payload,
        $_ENV['KEY']->SECRET_KEY,
        'HS256'
      );

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

    $email = $resetDetails->email;

    $db = new DbOperation();

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
      $db->forgotPassword($email, $hashCode, $timestamp);

      $response = setSuccessResponse($response, 200);
    }
    return $response;
  });



  $app->get('/verifyToken', function(ServerRequestInterface $request, ResponseInterface $response)
  {
    $db = new DbOperation();

    if(isset($_GET['email'], $_GET['hashCode'])) //server side validation if may email and hashCode parameter sa url
    {
      $email = $_GET['email'];
      $hashCode = $_GET['hashCode'];

      if($db->isTokenExpired($email) == false && $db->isLinkValid($email, $hashCode) == true)
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

    if(isset($resetPassDetails->email, $resetPassDetails->hashCode))
    {
      $email = $resetPassDetails->email;
      $hashCode = $resetPassDetails->hashCode;
      $pword = $resetPassDetails->pword;

      $db = new DbOperation();

      if($db->isLinkValid($email, $hashCode) == true && $db->changePassword($email, $pword))
      {
        $response = setSuccessResponse($response, 200);
      }
      else
      {
        $responseBody = array('errMsg' => 'Password already set');
        $response = setResponse($response, 400, $responseBody);
      }
    }
    else
    {
      $responseBody = array('errMsg' => 'Password already set');
      $response = setResponse($response, 400, $responseBody);
    }
    return $response;
  });



  $app->post('/changePassword', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $changePassDetails = json_decode(file_get_contents("php://input"));

    $email = $changePassDetails->email;
    $pword = $changePassDetails->pword;

    $db = new DbOperation();

    if($db->changePassword($email, $pword))
    {
      $response = setSuccessResponse($response, 200);
    }
    else
    {
      $responseBody = array('errMsg' => 'Failed Change Password');
      $response = setResponse($response, 400, $responseBody);
    }
    return $response;
  });



  $app->post('/changePasswordInSettings', function (ServerRequestInterface $request, ResponseInterface $response)
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
      $response = setSuccessResponse($response, 200);
    }
    else if($db->isPasswordValid($email, $pword, $oldPword) == "Same Password")
    {
      $responseBody = array('errMsg' => 'Invalid new password');
      $response = setResponse($response, 400, $responseBody);
    }
    else
    {
      $responseBody = array('errMsg' => 'Invalid password');
      $response = setResponse($response, 400, $responseBody);
    }

    return $response;
  });



  $app->post('/changeContact', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $changeContactDetails = json_decode(file_get_contents("php://input"));

    $email = $changeContactDetails->email;
    $contactNum = $changeContactDetails->contactNum;

    $db = new DbOperation();

    if($db->changeContact($email, $contactNum))
    {
      $response = setSuccessResponse($response, 200);
    }
    else
    {
      $responseBody = array('errMsg' => 'Failed Change Contact');
      $response = setResponse($response, 400, $responseBody);
    }
    return $response;
  });



  $app->post('/getPwordLength', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $accountDetails = json_decode(file_get_contents("php://input"));

    $email = $accountDetails->email;
    $tempPword = "";

    $db = new DbOperation();

    if($db->getPwordLength($email))
    {
      for($counter=0; $counter < $db->getPwordLength($email); $counter++)
      {
        $tempPword .= "•";
      }
      $responseBody = array('pwordLength' => $tempPword);
      $response = setResponse($response, 200, $responseBody);
    }

    return $response;
  });



  $app->post('/getContactNum', function (ServerRequestInterface $request, ResponseInterface $response)
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



  $app->post('/getNotifList', function (ServerRequestInterface $request, ResponseInterface $response)
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



  //lists admin accounts
  $app->post('/listAdmin', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $db = new DbOperation();

    $userType = 2;
    $status= 1;

    $adminList = $db->listAccounts($userType, $status);

    for($counter=0; $counter < count($adminList); $counter++)
    {
      $departmentId = $db->getDepartment($adminList[$counter]['email']);
      $adminList[$counter]['department'] = $db->getDepartmentName($departmentId);
    }

    $responseBody = array('adminList' => json_encode($adminList));
    $response = setResponse($response, 200, $responseBody);

    return $response;
  });



  //lists faculty accounts
  $app->post('/listFaculty', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $adminDetails = json_decode(file_get_contents("php://input"));

    $email = $adminDetails->email;
    $faculty = 3;
    $status = 1;

    $db = new DbOperation();

    $facultyList = $db->listAccounts($faculty, $status);

    for($counter=0; $counter < count($facultyList); $counter++){

      $facultyList[$counter]['reported_count'] = $db->getReportCount($facultyList[$counter]['email']);
    }

    $responseBody = array('facultyList' => json_encode($facultyList));
    $response = setResponse($response, 200, $responseBody);

    //echo json_encode($db->showMessages($email));

    return $response;
  });



  //lists students
  $app->post('/listStudent', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $adminDetails = json_decode(file_get_contents("php://input"));

    $email = $adminDetails->email;
    $status = 1;

    $db = new DbOperation();
    $department = $db->getDepartment($email);


    if($department==1){
      $responseBody = array('studentList' => json_encode($db->listShsStudent($status)));

    } else {
      $responseBody = array('studentList' => json_encode($db->listCollegeStudent($status)));

    }

    //echo json_encode($db->showMessages($email));
    $response = setResponse($response, 200, $responseBody);
    return $response;
  });



  //typeahead (autocomplete)
  $app->post('/getStudentInfo', function (ServerRequestInterface $request, ResponseInterface $response)
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



  $app->post('/reports', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $reportDetails = json_decode(file_get_contents("php://input"));

    $db = new DbOperation();

    $status = 1;
    $email = $reportDetails->email;
    $department = $db->getDepartment($email);

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



  $app->post('/registerFaculty', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $registerDetails = json_decode(file_get_contents("php://input"));

    $email = $registerDetails->email;
    $fName = $registerDetails->fName;
    $lName = $registerDetails->lName;
    $status = 0;
    $userType = 3;

    $db = new DbOperation();

    if($db->emailExist($email) == true)
    {
      $responseBody = array('errMsg' => 'Email exist');
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
        $response = setSuccessResponse($response, 200);
      }
    }

    return $response;
  });



  $app->post('/registerAdmin', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $registerDetails = json_decode(file_get_contents("php://input"));

    $email = $registerDetails->email;
    $fName = $registerDetails->fName;
    $lName = $registerDetails->lName;
    $department = $registerDetails->department;
    $status = 0;
    $userType = 2;

    $db = new DbOperation();

    if($db->emailExist($email) == true)
    {
      $responseBody = array('errMsg' => 'Email exist');
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
        $response = setSuccessResponse($response, 200);
      }
    }

    return $response;
  });



  $app->post('/deleteAdmin', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $deleteAdminDetails = json_decode(file_get_contents("php://input"));

    $email = $deleteAdminDetails->adminList;
    $status = 0;

    $db = new DbOperation();

    if(is_object($email)){

      foreach($email->email as $user) {
        $db->deleteUser($user, $status);
      }

      $response = setSuccessResponse($response, 200);

    } else {

        $db->deleteUser($email, $status);
        $response = setSuccessResponse($response, 200);
    }

    return $response;
  });



  $app->post('/deleteFaculty', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $deleteFacultyAdminDetails = json_decode(file_get_contents("php://input"));

    $email = $deleteFacultyAdminDetails->facultyList;
    $status = 0;

    $db = new DbOperation();

    if(is_object($email)){

      foreach($email->email as $user) {
        $db->deleteUser($user, $status);
      }

      $response = setSuccessResponse($response, 200);

    } else {

        $db->deleteUser($email, $status);
        $response = setSuccessResponse($response, 200);
    }

    return $response;
  });


  $app->post('/deleteStudent', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $deleteStudentDetails = json_decode(file_get_contents("php://input"));

    $student= $deleteStudentDetails->studentList;
    $email = $deleteStudentDetails->email;
    $status = 0;

    $db = new DbOperation();

    $department = $db->getDepartment($email);

    if(is_object($student)){

      foreach($student->student_id as $student) {
        $db->deleteStudent($student, $department, $status);
      }

      $response = setSuccessResponse($response, 200);

    } else {

        $db->deleteStudent($student, $department, $status);
        $response = setSuccessResponse($response, 200);
    }

    return $response;
  });


  $app->post('/deleteReport', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $deleteReportDetails = json_decode(file_get_contents("php://input"));

    $reports = $deleteReportDetails->reportList;
    //print_r($reports);

    $db = new DbOperation();

    if(is_object($reports)){

      foreach($reports->report_id as $report) {
        $db->deleteReport($report);
      }

      $response = setSuccessResponse($response, 200);

    } else {

        $db->deleteReport($reports);
        $response = setSuccessResponse($response, 200);
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



  $app->post('/referralForm', function (ServerRequestInterface $request, ResponseInterface $response)
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

    if($reasons[6]->check == true)
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

    $db->insertReport($email, $studId, $department, $subjName, $schoolTerm, $schoolYear, $refComment, $reasons);
    $db->insertStudent($studId, $department, $studFName, $studLName, $course, $year);
    $db->updateReportCount($email);

    $emailList = $db->getAdminAccounts($department, $isActive);

    $subject = "ACE Submitted Report";
    $link = $_ENV['DOMAIN']->CLIENT_URL;
    $body =

      "Greetings, <br><br>" . $full_name . " submitted a referral!
      <br><br>To view the submitted report, login <a href=" . $link . ">here</a>.
      <br><br><br>Thank you.";

    sendEmail($emailList, $subject, $body);

    $response = setSuccessResponse($response, 200);

    return $response;
  });



  $app->post('/messages', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $messageDetails = json_decode(file_get_contents("php://input"));

    $email = $messageDetails->email;
    $status = 1;

    $db = new DbOperation();

    $messageList = $db->showMessages($email,$status);

    for($counter=0; $counter < count($messageList); $counter++){

      $messageList[$counter]['sender_fname'] = $db->getFirstName($messageList[$counter]['sender_email']);
      $messageList[$counter]['sender_lname'] = $db->getLastName($messageList[$counter]['sender_email']);
    }

    $responseBody = array('messageList' => json_encode($messageList));
    $response = setResponse($response, 200, $responseBody);

    return $response;
  });



  $app->post('/markAsRead', function (ServerRequestInterface $request, ResponseInterface $response)
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



  $app->post('/markAsUnread', function (ServerRequestInterface $request, ResponseInterface $response)
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



  $app->post('/readMessage', function (ServerRequestInterface $request, ResponseInterface $response)
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



  $app->post('/markReport', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $reportDetails = json_decode(file_get_contents("php://input"));

    $reportList = $reportDetails->reportList;
    //$email = $reportDetails->email;
    $isRead = 1;

    $db = new DbOperation();

    foreach ($reportList->report_id as $value)
    {
      $db->markReport($isRead, $value);
    }

    $response = setSuccessResponse($response, 200);

    return $response;
  });



  $app->post('/markReportAsUnread', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $reportDetails = json_decode(file_get_contents("php://input"));

    $reportList = $reportDetails->reportList;
    //$email = $reportDetails->email;
    $isRead = 0;

    $db = new DbOperation();

    foreach ($reportList->report_id as $value)
    {
      $db->markReportAsUnread($isRead, $value);
    }

    $response = setSuccessResponse($response, 200);

    return $response;
  });



  $app->post('/readReport', function (ServerRequestInterface $request, ResponseInterface $response)
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



  $app->post('/deleteMessage', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $messageDetails = json_decode(file_get_contents("php://input"));

    $messageList = $messageDetails->markMessageList;
    $email = $messageDetails->email;
    $status = 0;

    $db = new DbOperation();

    if(is_object($messageList)){

      foreach ($messageList->report_id as $value)
      {
        $db->deleteMessage($status, $value, $email);
      }

      $response = setSuccessResponse($response, 200);
    } else {

      $db->deleteMessage($status, $messageList, $email);
      $response = setSuccessResponse($response, 200);
    }

    return $response;
  });



  $app->post('/sendMessage', function (ServerRequestInterface $request, ResponseInterface $response)
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

        "Greetings, <br><br>" . $first_name . " " . $last_name . " sent you a message regarding the referral that you have submitted!
        <br><br>To view the message, login <a href=" . $link . ">here</a>.
        <br><br><br>Thank you.";

      //send Email
      sendEmail($receiver, $subject, $body);

      $response = setSuccessResponse($response, 200);
    }
    else
    {
      $responseBody = array('errMsg' => 'Failed to send message.');
      $response = setResponse($response, 400, $responseBody);
    }

    return $response;
  });



  $app->post('/databaseConfirm', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $databaseDetails = json_decode(file_get_contents("php://input"));

    $status = 1;
    $email = $databaseDetails->email;
    $password = $databaseDetails->password;

    $db = new DbOperation();

    if($db->loginUser($email, $password, $status))
    {
      databaseBackup();
      $response = setSuccessResponse($response, 200);
    }
    else
    {
      $responseBody = array('errMsg' => 'Incorrect Email or Password');
      $response = setResponse($response, 400, $responseBody);
    }

    return $response;
  });



  $app->post('/updateStudent', function (ServerRequestInterface $request, ResponseInterface $response)
  {
    $studentDetails = json_decode(file_get_contents("php://input"));

    $email = $studentDetails->email;
    $studentId = $studentDetails->studentId;
    $originalId = $studentDetails->originalId;
    $lastName = $studentDetails->lastName;
    $firstName = $studentDetails->firstName;
    $program = $studentDetails->program;
    $level = $studentDetails->level;
    //$status = 0;

    $db = new DbOperation();
    //print_r($studentDetails);

    $department = $db->getDepartment($email);

    if($department == 1){
      $db->updateShsStudent($studentId, $originalId, $lastName, $firstName, $program, $level);
      $response = setSuccessResponse($response, 200);
    } else {
      $db->updateCollegeStudent($studentId, $originalId, $lastName, $firstName, $program, $level);
      $response = setSuccessResponse($response, 200);
    }

    return $response;
  });


  $app->post('/getSYList', function (ServerRequestInterface $request, ResponseInterface $response)
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


$app->post('/getChartData', function (ServerRequestInterface $request, ResponseInterface $response)
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



  $app->post('/updateStatus', function (ServerRequestInterface $request, ResponseInterface $response)
  {
      $updateDetails = json_decode(file_get_contents("php://input"));

      $db = new DbOperation();

      $email = $updateDetails->email;
      $reportId = $updateDetails->reportId;
      $status = $updateDetails->prevReportStatus;
      $updateStatus = $updateDetails->reportStatus;
      $isUpdated = 1;

      if(isset($updateDetails->comment))
      {
        $comment = $updateDetails->comment;
      }
      else
      {
        $comment = null;
      }

      if($status != $updateStatus)
      {
        $db->updateStatus($reportId, $updateStatus, $comment, $isUpdated);

        $subject = "ACE Submitted Report Status";
        $link = $_ENV['DOMAIN']->CLIENT_URL;
        $body =
          "The administrator updated the status of your submitted report from " . $db->getReportStatusName($status) . " to " . $db->getReportStatusName($updateStatus) . ".
          <br><br>
          If you wish to submit another report, login <a href=" . $link . ">here</a>. Thank you.";

        //send Email
        sendEmail($email, $subject, $body);
      }

      $response = setSuccessResponse($response, 200);
      return $response;
  });

















// <------------------------------------------------------------------------------------------------------------------------->

    //for testing lang yung mga code sa baba
    $app->get('/users', function () {

        $status = 1;

        $db = new DbOperation();

        $response = $db->sampleFunc($status);

        $key = "example_key";
        $token = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => 1356999524,
            "nbf" => 1357000000
        );

        /**
         * IMPORTANT:
         * You must specify supported algorithms for your application. See
         * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
         * for a list of spec-compliant algorithms.
         */
        $jwt = JWT::encode($token, $key);
        $decoded_jwt = JWT::decode($jwt, $key, array('HS256'));

        //print_r($decoded_jwt);

        /*
         NOTE: This will now be an object instead of an associative array. To get
         an associative array, you will need to cast it as such:
        */

        //$decoded_array = (array) $decoded;

        header('Content-Type: application/json');
        echo json_encode($decoded_jwt);
        //echo $response;
    });



    $app->run();
