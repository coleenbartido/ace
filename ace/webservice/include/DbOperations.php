<?php

class DbOperation
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect();
        $this->con = $db->connect();
    }


    public function loginUser($email, $pword, $status)
    {
        $stmt = $this->con->prepare("SELECT hash FROM user WHERE email=? and status=? UNION SELECT hash FROM superadmin_account WHERE email=?");
        $stmt->bind_param("sis", $email, $status, $email);
        $stmt->execute();
        $account = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if(password_verify($pword, $account['hash']))
        {
            return true;
        }
        return false;
    }


    public function getAccountRole($email)
    {
        $stmt = $this->con->prepare("SELECT user_type_id FROM user WHERE email=? UNION SELECT user_type_id FROM superadmin_account WHERE email=?");
        $stmt->bind_param("ss",$email,$email);
        $stmt->execute();
        $accountRole = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $accountRole['user_type_id'];
    }


    public function isLinkValid($email, $hashCode)
    {
        $stmt = $this->con->prepare("SELECT email FROM user WHERE email=? AND hashcode=? UNION SELECT email FROM superadmin_account WHERE email=? AND hashcode=?");
        $stmt->bind_param("ssss", $email, $hashCode, $email, $hashCode);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows>0;
    }


    public function emailExist($email)
    {
        $status = 1;

        $stmt = $this->con->prepare("SELECT email FROM user WHERE email=? and status=? UNION SELECT email FROM superadmin_account WHERE email=?");
        $stmt->bind_param("sis",$email, $status, $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows>0;
    }


    // not used
    public function isAccountActive($email)
    {
        $stmt = $this->con->prepare("SELECT * FROM user WHERE email=? AND hash IS NOT NULL");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows>0;
    }


    public function forgotPassword($email, $hashCode, $timestamp, $role)
    {
        if($role == 1)
        {
            $stmt = $this->con->prepare("UPDATE superadmin_account SET hashcode =?, token_exp=? WHERE email=?");
        }
        else
        {
            $stmt = $this->con->prepare("UPDATE user SET hashcode =?, token_exp=? WHERE email=?");
        }
        
        $stmt->bind_param("sss",$hashCode, $timestamp, $email);
        $result = $stmt->execute();
        $stmt->close();

        if($result)
        {
            return true;
        }
        return false;
    }


    //check if token is expired
    public function isTokenExpired($email)
    {
        $date = getTimestamp();
        $currentDate= $date->format('Y-m-d H:i:s');

        $stmt = $this->con->prepare("SELECT token_exp FROM user where email=? UNION SELECT token_exp FROM superadmin_account where email=?");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $token = $stmt->get_result()->fetch_assoc();

        $token_exp = strtotime($token['token_exp']);
        $expiryDate = date('Y-m-d H:i:s', $token_exp);

        if($currentDate > $expiryDate)
        {
            return true;
        }
        else
        {
            return false;
        }

        $stmt->close;
    }


    //used in RESET and CHANGE password
    public function changePassword($email, $password, $role)
    {
        $password = password_hash($password, PASSWORD_DEFAULT);

        if($role == 1)
        {
            $stmt = $this->con->prepare("UPDATE superadmin_account SET hashcode=NULL, token_exp=NULL, hash=? WHERE email=?");
        }
        else
        {
            $stmt = $this->con->prepare("UPDATE user SET hashcode=NULL, token_exp=NULL, hash=? WHERE email=?");           
        }

        $stmt->bind_param("ss", $password, $email);
        $result = $stmt->execute();
        $stmt->close();

        if($result)
        {
            return true;
        }
        return false;
    }


    public function getReferralUpdateCount($email)
    {
        $status = 1;
        $stmt = $this->con->prepare("SELECT * FROM report WHERE email=? and is_updated=?");
        $stmt->bind_param("si", $email, $status);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows;
    }


    public function getNewMessageCount($email)
    {
        $status = 1;
        $isRead = 0;
        $stmt = $this->con->prepare("SELECT DISTINCT report_id FROM message WHERE ((receiver_email=? and is_read=? and receiver_status=?) or (sender_email=? and is_read_sender=? and sender_status=?)) ");
        $stmt->bind_param("siisii", $email, $isRead, $status, $email, $isRead, $status);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows;
    }


    public function getUncounseledReportCount($department)
    {
        $status = 1;
        $stmt = $this->con->prepare("SELECT * FROM report WHERE report_status_id=? and status=? and department_id=?");
        $stmt->bind_param("iii", $status, $status, $department);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows;
    }


    //EDIT TODAY
    public function changeContact($email, $contactNum)
    {
        $stmt = $this->con->prepare("UPDATE user SET hashcode=NULL, token_exp=NULL, contact_number=? WHERE email=?");
        $stmt->bind_param("ss", $contactNum, $email);
        $result = $stmt->execute();
        $stmt->close();
        if($result)
        {
            return true;
        }
        return false;
    }


    public function getContactNum($email)
    {
        $stmt = $this->con->prepare("SELECT contact_number FROM user WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $accountDetails = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $accountDetails['contact_number'];
    }


    public function registerFaculty($email, $fname, $lname, $status, $userType, $hashCode)
    {
        //$password = md5($pass);
        $reportCount = 0;

        $stmt = $this->con->prepare("INSERT INTO user(email, first_name, last_name, status, user_type_id, hashcode) values(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE first_name=?, last_name=?, status=?, user_type_id=?, hashcode=?");
        $stmt->bind_param("sssiisssiis", $email, $fname, $lname, $status, $userType, $hashCode, $fname, $lname, $status, $userType, $hashCode);
        $result = $stmt->execute();
        $stmt->close();

        $stmt2 = $this->con->prepare("INSERT INTO faculty_account(email) values(?) ON DUPLICATE KEY UPDATE reported_count=?");
        $stmt2->bind_param("si", $email, $reportCount);
        $result2 = $stmt2->execute();
        $stmt2->close();

        if($result && $result2)
        {
          return true;
        }
        return false;
    }


    public function registerAdmin($email, $fname, $lname, $status, $userType, $hashCode, $department)
    {
        //$password = md5($pass);
        $stmt = $this->con->prepare("INSERT INTO user(email, first_name, last_name, status, user_type_id, hashcode) values(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE first_name=?, last_name=?, status=?, user_type_id=?, hashcode=?");
        $stmt->bind_param("sssiisssiis", $email, $fname, $lname, $status, $userType, $hashCode, $fname, $lname, $status, $userType, $hashCode);
        $result = $stmt->execute();
        $stmt->close();

        $stmt2 = $this->con->prepare("INSERT INTO admin_account(email, department_id) values(?, ?) ON DUPLICATE KEY UPDATE department_id=?");
        $stmt2->bind_param("sii", $email, $department, $department);
        $result2 = $stmt2->execute();
        $stmt2->close();

        if($result && $result2)
        {
        return true;
        }
        return false;
    }


    public function setupAccountDetails($email, $hashCode, $contactNumber, $pword, $status)
    {
        $pword = password_hash($pword, PASSWORD_DEFAULT);

        $stmt = $this->con->prepare("UPDATE user SET contact_number=?, hash=?, status=?, hashcode=NULL WHERE email=? and hashcode=?");
        $stmt->bind_param("ssiss", $contactNumber, $pword, $status, $email, $hashCode);
        $result = $stmt->execute();
        $stmt->close();

        if($result)
        {
            return true;
        }
        return false;
    }


    //--------------------------------------REFERRAL FORM-------------------------------------------------


    //Inserting report to database
    public function insertReport($email, $studId, $department, $subjName, $schoolTerm, $schoolYear, $refComment, $reasons)
    {
        $stmt = $this->con->prepare("INSERT INTO report(email, student_id, department_id, subject_name, term, school_year, referral_comment) VALUES(?,?,?,?,?,?,?)");
        $stmt->bind_param("ssisiss", $email, $studId, $department, $subjName, $schoolTerm, $schoolYear, $refComment);
        $result = $stmt->execute();
        $report_id = $stmt->insert_id;
        $stmt->close();

        foreach ($reasons as $reason)
        {
          if($reason->check == true && isset($reason->value))
          {
            $stmt2 = $this->con->prepare("INSERT INTO reason(report_id, referral_reason_id) values(?, ?)");
            $stmt2->bind_param("ii", $report_id, $reason->value);
            $result2 = $stmt2->execute();
            $stmt2->close();
          }
        }

        if($result && $result2)
        {
           return true;
        }
        return false;
    }


    //insert student in student table
    public function insertStudent($studId, $department, $studFName, $studLName, $course, $year)
    {
        $stmt = $this->con->prepare("INSERT INTO student(student_id, department_id, first_name, last_name) values(?, ?, ?, ?) ON DUPLICATE KEY UPDATE department_id=?, first_name=?, last_name=?");
        $stmt->bind_param("sississ", $studId, $department, $studFName, $studLName, $department, $studFName, $studLName);
        $result = $stmt->execute();
        $stmt->close();

        if($department==1)
        {
          $stmt2 = $this->con->prepare("INSERT INTO shs_student(student_id, program_id, level) values(?, ?, ?) ON DUPLICATE KEY UPDATE program_id=?, level=?, report_count=report_count+1");
        }
        else
        {
          $stmt2 = $this->con->prepare("INSERT INTO college_student(student_id, program_id, level) values(?, ?, ?) ON DUPLICATE KEY UPDATE program_id=?, level=?, report_count=report_count+1");
        }

        $stmt2->bind_param("siiii", $studId, $course, $year, $course, $year);
        $result2 = $stmt2->execute();
        $stmt2->close();


        if($result && $result2)
        {
           return true;
        }
        return false;
    }


    public function updateReportCount($email)
    {
        $stmt = $this->con->prepare("UPDATE faculty_account SET reported_count=reported_count+1 WHERE email=?");
        $stmt->bind_param("s",$email);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }


    //---------------------------------------------------------------------------------------


    public function getFirstName($email)
    {
      $stmt = $this->con->prepare("SELECT first_name FROM user WHERE email=?");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $user = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if($user == null){
        $user['first_name'] = "Super";
      }

      return $user['first_name'];
    }


    public function getLastName($email)
    {

      $stmt = $this->con->prepare("SELECT last_name FROM user WHERE email=?");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $user = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if($user == null){
        $user['last_name'] = "Admin";
      }

      return $user['last_name'];
    }


    //list reports for admin
    public function listReports($report_status_id, $status, $department)
    {
        $stmt = $this->con->prepare("SELECT * FROM report WHERE report_status_id=? AND status=? AND department_id=?");
        $stmt->bind_param("iii",$report_status_id, $status, $department);
        $stmt->execute();
        $result = $stmt->get_result();
        $arrResult = array();
        while ($myrow = $result->fetch_assoc())
        {
            $arrResult[] = $myrow;
        }
        $stmt->close();

        return $arrResult;
    }


    public function deleteUser($email, $status)
    {
        $stmt = $this->con->prepare("UPDATE user SET status=?, hashcode = NULL WHERE email=?");
        $stmt->bind_param("is", $status, $email);
        $result = $stmt->execute();
        $stmt->close();
        if($result)
        {
            return true;
        }
        return false;
    }


    public function deleteStudent($studentId, $department, $status)
    {
        if($department == 1){
          $stmt = $this->con->prepare("UPDATE shs_student SET status=? WHERE student_id=?");

        }
        else
        {
          $stmt = $this->con->prepare("UPDATE college_student SET status=? WHERE student_id=?");

        }

        $stmt->bind_param("is",$status,$studentId);
        $result = $stmt->execute();
        $stmt->close();

        if($result){
            return true;
          }
        return false;
    }


    //checks if user is admin
    public function isAdmin($email)
    {
        $stmt = $this->con->prepare("SELECT user_type_id FROM user WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if($admin['user_type_id'] == 2)
        {
          return true;
        }
        else
        {
          return false;
        }
    }


    //checks if user is faculty
    public function isFaculty($email)
    {
        $stmt = $this->con->prepare("SELECT user_type_id FROM user WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $faculty = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if($faculty['user_type_id'] == 1)
        {
          return true;
        }
        else
        {
          return false;
        }
    }


    //lists accounts
    public function listAccounts($role)
    {
        $status = 1;
        $stmt = $this->con->prepare("SELECT email, user_type_id, first_name, last_name, contact_number FROM user WHERE user_type_id=? AND (hashcode IS NOT NULL OR status=?)");
        $stmt->bind_param("ii",$role, $status);
        $stmt->execute();
        $result= $stmt->get_result();
        $arrResult = array();
        while ($myrow = $result->fetch_assoc())
        {
            $arrResult[] = $myrow;
        }
        $stmt->close();

        return $arrResult;
    }


    public function isPasswordValid($email, $password, $oldPassword)
    {
      $stmt = $this->con->prepare("SELECT hash FROM user WHERE email=? UNION SELECT hash FROM superadmin_account WHERE email=?");
      $stmt->bind_param("ss", $email, $email);
      $stmt->execute();
      $account = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if(password_verify($password, $account['hash']) == false && password_verify($oldPassword, $account['hash']) == true)
      {
        return "Valid Password";
      }
      else if(password_verify($password, $account['hash']) == true && password_verify($oldPassword, $account['hash']) == true)
      {
        return "Same Password";
      }
      else
      {
        return "Invalid Password";
      }
    }


    //message list of logged in users
    public function showMessages($email, $status)
    {
        $stmt = $this->con->prepare("SELECT * FROM message WHERE (receiver_email=? AND receiver_status=?) OR (sender_email=? AND sender_status=?)");
        $stmt->bind_param("sisi",$email, $status, $email, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $arrResult = array();
        while ($myrow = $result->fetch_assoc())
        {
            $arrResult[] = $myrow;
        }
        $stmt->close();

        return $arrResult;
    }


    public function markMessage($isRead, $reportId, $email)
    {
        $stmt = $this->con->prepare("UPDATE message SET is_read=? WHERE report_id=? AND receiver_email=?");
        $stmt->bind_param("iis", $isRead, $reportId, $email);
        $result = $stmt->execute();
        $stmt->close();

        $stmt2 = $this->con->prepare("UPDATE message SET is_read_sender=? WHERE report_id=? AND sender_email=?");
        $stmt2->bind_param("iis", $isRead, $reportId, $email);
        $result2 = $stmt2->execute();
        $stmt2->close();

        if($result && $result2)
        {
            return true;
        }
        return false;
    }


    public function deleteMessage($status, $reportId, $email)
    {
      $stmt = $this->con->prepare("UPDATE message SET receiver_status=? WHERE report_id=? AND receiver_email=?");
      $stmt->bind_param("iis",$status, $reportId, $email);
      $result = $stmt->execute();
      $stmt->close();

      $stmt2 = $this->con->prepare("UPDATE message SET sender_status=? WHERE report_id=? AND sender_email=?");
      $stmt2->bind_param("iis",$status, $reportId, $email);
      $result2 = $stmt2->execute();
      $stmt2->close();

      if($result && $result2)
      {
        return true;
      }
      return false;
    }


    //insert message to DB
    public function insertMessage($report_id, $sender, $receiver, $message, $subject, $isRead, $status, $timestamp)
    {
        $isReadSender = 1;

        $stmt = $this->con->prepare("INSERT INTO message(report_id, sender_email, receiver_email, message_body, message_subject, is_read, is_read_sender, receiver_status, sender_status, message_date) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssiiiis", $report_id, $sender, $receiver, $message, $subject, $isRead, $isReadSender, $status, $status, $timestamp);
        $result = $stmt->execute();
        $stmt->close();

        if($result)
        {
            return true;
        }
        return false;
    }


    //get admin accounts for a department
    public function getAdminAccounts($department, $status)
    {
      $stmt = $this->con->prepare("SELECT email FROM admin_view WHERE department_id=? AND status=?");
      $stmt->bind_param("ii",$department, $status);
      $stmt->execute();
      $result = $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
          $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;
    }


    //get faculty accounts
    public function getFacultyAccounts($userType, $status)
    {
      $stmt = $this->con->prepare("SELECT email FROM user WHERE user_type_id=? AND status=?");
      $stmt->bind_param("ii",$userType, $status);
      $stmt->execute();
      $result = $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
          $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;
    }


    //lists students
    public function listStudent($department)
    {

        $stmt = $this->con->prepare("SELECT * FROM student WHERE department_id=?");
        $stmt->bind_param("i",$department);
        $stmt->execute();
        $result= $stmt->get_result();
        $arrResult = array();
        while ($myrow = $result->fetch_assoc())
        {
            $arrResult[] = $myrow;
        }
        $stmt->close();

        return $arrResult;

    }


    public function getDepartment($email)
    {
        $stmt = $this->con->prepare("SELECT department_id FROM admin_account WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $accountRole = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $accountRole['department_id'];
    }


    public function getDepartmentName($departmentId)
    {
        $stmt = $this->con->prepare("SELECT department_name FROM department WHERE department_id=?");
        $stmt->bind_param("i", $departmentId);
        $stmt->execute();
        $accountRole = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $accountRole['department_name'];
    }


    public function listShsReports($status, $schoolYear)
    {
        $stmt = $this->con->prepare("SELECT * FROM shs_report WHERE status=? AND school_year=?");
        $stmt->bind_param("is", $status, $schoolYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $arrResult = array();
        while ($myrow = $result->fetch_assoc())
        {
            $arrResult[] = $myrow;
        }
        $stmt->close();

        return $arrResult;
    }


    public function listShsStudent($status)
    {
      $stmt = $this->con->prepare("SELECT * FROM shs_view WHERE status=?");
      $stmt->bind_param("i",$status);
      $stmt->execute();
      $result= $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
          $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;
    }

    public function updateShsStudent($studentId, $originalId, $lastName, $firstName, $program, $level)
    {
        $stmt = $this->con->prepare("UPDATE student SET student_id=?, last_name=?, first_name=? WHERE student_id=?");
        $stmt->bind_param("ssss",$studentId, $lastName, $firstName, $originalId);
        $result = $stmt->execute();
        $stmt->close();

        $stmt2 = $this->con->prepare("UPDATE shs_student SET student_id=?, level=?, program_id=? WHERE student_id=?");
        $stmt2->bind_param("siis",$studentId, $level, $program, $originalId);
        $result2 = $stmt2->execute();
        $stmt2->close();

        if($result && $result2)
        {
            return true;
        }
        return false;
    }


    public function listCollegeReports($status, $schoolYear)
    {
        $stmt = $this->con->prepare("SELECT * FROM college_report WHERE status=? AND school_year=?");
        $stmt->bind_param("is", $status, $schoolYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $arrResult = array();
        while ($myrow = $result->fetch_assoc())
        {
            $arrResult[] = $myrow;
        }
        $stmt->close();

        return $arrResult;
    }

    public function listCollegeStudent($status)
    {
      $stmt = $this->con->prepare("SELECT * FROM college_view WHERE status=?");
      $stmt->bind_param("i",$status);
      $stmt->execute();
      $result= $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
          $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;
    }

    public function updateCollegeStudent($studentId, $originalId,  $lastName, $firstName, $program, $level)
    {
      $stmt = $this->con->prepare("UPDATE student SET student_id=?, last_name=?, first_name=? WHERE student_id=?");
      $stmt->bind_param("ssss",$studentId, $lastName, $firstName, $originalId);
      $result = $stmt->execute();
      $stmt->close();

      $stmt2 = $this->con->prepare("UPDATE college_student SET student_id=?, level=?, program_id=? WHERE student_id=?");
      $stmt2->bind_param("siis",$studentId, $level, $program, $originalId);
      $result2 = $stmt2->execute();
      $stmt2->close();

      if($result && $result2)
      {
          return true;
      }
      return false;
    }


    public function getReportCount($email)
    {
        $stmt = $this->con->prepare("SELECT reported_count FROM faculty_account WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $report_count = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $report_count['reported_count'];
    }


    public function deleteReport($report_id)
    {
        $stmt = $this->con->prepare("UPDATE report SET status=0 WHERE report_id=?");
        $stmt->bind_param("i",$report_id);
        $result = $stmt->execute();
        $stmt->close();

        if($result)
        {
            return true;
        }
        return false;
    }


    public function markReport($isRead, $reportId)
    {
        $stmt = $this->con->prepare("UPDATE report SET is_read=? WHERE report_id=?");
        $stmt->bind_param("ii", $isRead, $reportId);
        $result = $stmt->execute();
        $stmt->close();

        if($result)
        {
            return true;
        }
        return false;
    }

    public function markReportAsUnread($isRead, $reportId)
    {
        $stmt = $this->con->prepare("UPDATE report SET is_read=? WHERE report_id=?");
        $stmt->bind_param("ii", $isRead, $reportId);
        $result = $stmt->execute();
        $stmt->close();

        if($result)
        {
            return true;
        }
        return false;
    }



    public function getSYList($department, $status)
    {
      $stmt = $this->con->prepare("SELECT school_year FROM report WHERE department_id=? AND status=? GROUP BY school_year ORDER BY school_year ASC");
      $stmt->bind_param("ii", $department, $status);
      $stmt->execute();
      $result= $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
        $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;

    }


    public function getTermData($department, $status, $schoolYear)
    {
      $terms = array(1, 2, 3);

      $stmt = $this->con->prepare("SELECT (SELECT COUNT(*) FROM report WHERE term=? AND department_id=? AND status=? AND school_year=?) as firstTermCount, (SELECT COUNT(*) FROM report WHERE term=? AND department_id=? AND status=? AND school_year=?) as secondTermCount, (SELECT COUNT(*) FROM report WHERE term=? AND department_id=? AND status=? AND school_year=?) as thirdTermCount");
      $stmt->bind_param("iiisiiisiiis", $terms[0], $department, $status, $schoolYear, $terms[1], $department, $status, $schoolYear, $terms[2], $department, $status, $schoolYear);
      $stmt->execute();
      $result= $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
        $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;
    }


    public function getProgramData($department, $status, $schoolYear)
    {
      $programId = array(1, 2, 3, 4, 5, 6, 7, 8);

      if($department == 1)
      {
        $stmt = $this->con->prepare("SELECT (SELECT COUNT(a.report_count) FROM report c INNER JOIN shs_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as HSSCount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN shs_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as ABMCount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN shs_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as CPCount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN shs_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as AniCount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN shs_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as FDCount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN shs_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as MACount");

        $stmt->bind_param("iisiiisiiisiiisiiisiiisi", $department, $status, $schoolYear, $programId[0],
                                                      $department, $status, $schoolYear, $programId[1],
                                                      $department, $status, $schoolYear, $programId[2],
                                                      $department, $status, $schoolYear, $programId[3],
                                                      $department, $status, $schoolYear, $programId[4],
                                                      $department, $status, $schoolYear, $programId[5]);
      }
      else
      {
        $stmt = $this->con->prepare("SELECT (SELECT COUNT(a.report_count) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as SECount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as GDCount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as WDCount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as AniCount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as MMACount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as FDCount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as REMCount,
                                            (SELECT COUNT(a.report_count) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.program_id=?) as BACount");

        $stmt->bind_param("iisiiisiiisiiisiiisiiisiiisiiisi", $department, $status, $schoolYear, $programId[0],
                                                              $department, $status, $schoolYear, $programId[1],
                                                              $department, $status, $schoolYear, $programId[2],
                                                              $department, $status, $schoolYear, $programId[3],
                                                              $department, $status, $schoolYear, $programId[4],
                                                              $department, $status, $schoolYear, $programId[5],
                                                              $department, $status, $schoolYear, $programId[6],
                                                              $department, $status, $schoolYear, $programId[7]);
      }

      $stmt->execute();
      $result= $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
        $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;
    }


    public function getLevelData($department, $status, $schoolYear)
    {
      $level = array(1, 2, 3, 4);

      if($department == 1)
      {
        $stmt = $this->con->prepare("SELECT (SELECT COUNT(*) FROM report c INNER JOIN shs_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.level=?) as gradeElevenCount,
                                            (SELECT COUNT(*) FROM report c INNER JOIN shs_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.level=?) as gradeTwelveCount");

        $stmt->bind_param("iisiiisi", $department, $status, $schoolYear, $level[0], $department, $status, $schoolYear, $level[1]);
      }
      else
      {
        $stmt = $this->con->prepare("SELECT (SELECT COUNT(*) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.level=?) as firstYearCount,
                                            (SELECT COUNT(*) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.level=?) as secondYearCount,
                                            (SELECT COUNT(*) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.level=?) as thirdYearCount,
                                            (SELECT COUNT(*) FROM report c INNER JOIN college_student a ON c.student_id = a.student_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.level=?) as fourthYearCount");

        $stmt->bind_param("iisiiisiiisiiisi", $department, $status, $schoolYear, $level[0], $department, $status, $schoolYear, $level[1], $department, $status, $schoolYear, $level[2], $department, $status, $schoolYear, $level[3]);
      }

      $stmt->execute();
      $result= $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
        $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;
    }


    public function getReasonData($department, $status, $schoolYear)
    {
      $refReason = array(1, 2, 3, 4, 5, 6);


      $stmt = $this->con->prepare("SELECT (SELECT COUNT(*) FROM report c INNER JOIN reason a ON c.report_id = a.report_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.referral_reason_id=?) as reasonOneCount,
                                          (SELECT COUNT(*) FROM report c INNER JOIN reason a ON c.report_id = a.report_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.referral_reason_id=?) as reasonTwoCount,
                                          (SELECT COUNT(*) FROM report c INNER JOIN reason a ON c.report_id = a.report_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.referral_reason_id=?) as reasonThreeCount,
                                          (SELECT COUNT(*) FROM report c INNER JOIN reason a ON c.report_id = a.report_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.referral_reason_id=?) as reasonFourCount,
                                          (SELECT COUNT(*) FROM report c INNER JOIN reason a ON c.report_id = a.report_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.referral_reason_id=?) as reasonFiveCount,
                                          (SELECT COUNT(*) FROM report c INNER JOIN reason a ON c.report_id = a.report_id WHERE c.department_id=? AND c.status=? AND c.school_year=? AND a.referral_reason_id=?) as reasonSixCount,
                                          (SELECT COUNT(*) FROM report WHERE department_id=? AND status=? AND school_year=? AND referral_comment IS NOT NULL) as otherReasonCount");

      $stmt->bind_param("iisiiisiiisiiisiiisiiisiiis", $department, $status, $schoolYear, $refReason[0], $department, $status, $schoolYear, $refReason[1], $department, $status, $schoolYear, $refReason[2], $department, $status, $schoolYear, $refReason[3], $department, $status, $schoolYear, $refReason[4], $department, $status, $schoolYear, $refReason[5], $department, $status, $schoolYear);
      $stmt->execute();
      $result= $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
        $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;
    }


    public function getStatusData($department, $status, $schoolYear)
    {
      $reportStatus = array(1, 2, 3);

      $stmt = $this->con->prepare("SELECT (SELECT COUNT(*) FROM report WHERE report_status_id=? AND department_id=? AND status=? AND school_year=?) as uncounseledCount, (SELECT COUNT(*) FROM report WHERE report_status_id=? AND department_id=? AND status=? AND school_year=?) as inProgressCount, (SELECT COUNT(*) FROM report WHERE report_status_id=? AND department_id=? AND status=? AND school_year=?) as counseledCount");
      $stmt->bind_param("iiisiiisiiis", $reportStatus[0], $department, $status, $schoolYear, $reportStatus[1], $department, $status, $schoolYear, $reportStatus[2], $department, $status, $schoolYear);
      $stmt->execute();
      $result= $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
        $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;
    }


    public function getReferralReasons($reportId)
    {
        $stmt = $this->con->prepare("SELECT referral_reason FROM ((report INNER JOIN reason ON report.report_id=reason.report_id)
            INNER JOIN report_reason ON reason.referral_reason_id=report_reason.referral_reason_id) WHERE report.report_id=?");

        $stmt->bind_param("i",$reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_rows = $stmt->num_rows;
        $arrResult = array();

        if($result->fetch_assoc())
        {
            while ($myrow = $result->fetch_assoc())
            {
                $arrResult[] = $myrow;
            } 
        }
        else
        {
            $arrResult[]['referral_reason'] = "N/A";
        }  

        $stmt->close();

        return $arrResult;
    }


    public function updateStatus($reportId, $updateStatus, $comment)
    {
      $stmt = $this->con->prepare("UPDATE report SET report_status_id=?, counselor_note=? WHERE report_id=?");
      $stmt->bind_param("isi", $updateStatus, $comment, $reportId);
      $result = $stmt->execute();
      $stmt->close();

      if($result)
      {
          return true;
      }
      return false;
    }


    public function setReporAsUpdated($reportId, $isUpdated)
    {
      $stmt = $this->con->prepare("UPDATE report SET is_updated=? WHERE report_id=?");
      $stmt->bind_param("ii", $isUpdated, $reportId);
      $result = $stmt->execute();
      $stmt->close();

      if($result)
      {
          return true;
      }
      return false;
    }


    public function getReportStatusName($reportStatus)
    {

      $stmt = $this->con->prepare("SELECT report_status FROM report_status WHERE report_status_id=?");
      $stmt->bind_param("i", $reportStatus);
      $stmt->execute();
      $report = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      return $report['report_status'];
    }


    public function getStudentInfo($studId)
    {
      $studId = $studId . "%";
      $limit = 5;

      $stmt = $this->con->prepare("SELECT * FROM student WHERE student_id LIKE ? ORDER BY student_id DESC LIMIT ?");
      $stmt->bind_param("si", $studId, $limit);
      $stmt->execute();
      $result= $stmt->get_result();
      $arrResult = array();
      while ($myrow = $result->fetch_assoc())
      {
        $arrResult[] = $myrow;
      }
      $stmt->close();

      return $arrResult;
    }


    public function getStudentLevel($studId, $department)
    {
      if($department == 1)
      {
        $stmt = $this->con->prepare("SELECT level FROM shs_student WHERE student_id=?");
      }
      else
      {
        $stmt = $this->con->prepare("SELECT level FROM college_student WHERE student_id=?");
      }

      $stmt->bind_param("s", $studId);
      $stmt->execute();
      $user = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      return $user['level'];
    }


    public function getStudentProgram($studId, $department)
    {
      if($department == 1)
      {
        $stmt = $this->con->prepare("SELECT program_id FROM shs_student WHERE student_id=?");
      }
      else
      {
        $stmt = $this->con->prepare("SELECT program_id FROM college_student WHERE student_id=?");
      }

      $stmt->bind_param("s", $studId);
      $stmt->execute();
      $user = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      return $user['program_id'];
    }


    public function getStudentName($reportId)
    {
        $stmt = $this->con->prepare("SELECT * FROM report WHERE status=?");
        $stmt->bind_param("i",$status);
        $stmt->execute();
        $result = $stmt->get_result();
        $arrResult = array();
        while ($myrow = $result->fetch_assoc())
        {
            $arrResult[] = $myrow;
        }
        $stmt->close();

        return $arrResult;
    }










}
