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
        /*
        $hash = hash('sha256', $password1);

        FUNCTION createSalt()
        {
        $text = md5(uniqid(rand(), TRUE));
        RETURN substr($text, 0, 3);
        }

        $salt = createSalt();
        $password = hash('sha256', $salt . $hash);
        */

        $stmt = $this->con->prepare("SELECT email FROM user WHERE email=? and hash=? and status=? UNION SELECT email FROM superadmin_account WHERE email=? and hash=?");
        $stmt->bind_param("ssiss", $email, $pword, $status, $email, $pword);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows>0;
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
        $stmt = $this->con->prepare("SELECT * FROM user WHERE email=? and hashCode=?");
        $stmt->bind_param("ss", $email, $hashCode);
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
        $stmt = $this->con->prepare("SELECT status FROM user WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $active = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if($active['status'] == 0)
        {
          return false;
        }
        else
        {
          return true;
        }
    }


    public function forgotPassword($email, $hashCode, $timestamp)
    {
        $stmt = $this->con->prepare("UPDATE user SET hashcode =?, token_exp=? WHERE email=?");
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

        $stmt = $this->con->prepare("SELECT token_exp FROM user where email=?");
        $stmt->bind_param("s", $email);
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
        if($role == 1)
        {
            $stmt = $this->con->prepare("UPDATE superadmin_account SET hash=? WHERE email=?");
            $stmt->bind_param("ss", $password, $email);
            $result = $stmt->execute();
            $stmt->close();
        }
        else
        {
            $stmt = $this->con->prepare("UPDATE user SET hashcode=NULL, token_exp=NULL, hash=? WHERE email=?");
            $stmt->bind_param("ss", $password, $email);
            $result = $stmt->execute();
            $stmt->close();
        }
        
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


    public function getPwordLength($email)
    {
        $stmt = $this->con->prepare("SELECT hash FROM user WHERE email=? UNION SELECT hash FROM superadmin_account WHERE email=?");
        $stmt->bind_param("ss",$email,$email);
        $stmt->execute();
        $accountDetails = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return strlen($accountDetails['hash']);
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
          if($reason->check == true && $reason->value != 0)
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


    public function deleteUser($email)
    {
        $stmt = $this->con->prepare("UPDATE user SET status=0 WHERE email=?");
        $stmt->bind_param("s",$email);
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
    public function listAccounts($role, $status)
    {

        $stmt = $this->con->prepare("SELECT * FROM user WHERE user_type_id=? and status=?");
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


    //checks if old password is same as the new password
    //checks if the input for the old password is different from the password
      //in the db
    public function isPasswordValid($email, $password, $oldPassword)
    {
      $stmt = $this->con->prepare("SELECT hash FROM user WHERE email=? UNION SELECT hash FROM superadmin_account WHERE email=?");
      $stmt->bind_param("ss", $email, $email);
      $stmt->execute();
      $hash = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if($hash['hash'] != $password && $hash['hash'] == $oldPassword)
      {
        return "Valid Password";
      }
      else if($hash['hash'] == $password && $hash['hash'] == $oldPassword)
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
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if($result)
        {
            return true;
        }
        return false;
    }


    //check if password and email matches
    public function confirmPassword($email, $password)
    {
      $stmt = $this->con->prepare("SELECT * FROM user WHERE email=? AND hash=?");
      $stmt->bind_param("ss", $email, $password);
      $stmt->execute();
      $stmt->store_result();
      $num_rows = $stmt->num_rows;
      $stmt->close();
      return $num_rows>0;
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



    public function listShsReports($status)
    {
        $stmt = $this->con->prepare("SELECT * FROM shs_report WHERE status=?");
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


    public function listCollegeReports($status)
    {
        $stmt = $this->con->prepare("SELECT * FROM college_report WHERE status=?");
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

    public function updateCollegeStudent($studentId, $orig_studentId,  $lastName, $firstName, $program, $level)
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
        if($result){
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


    public function getSYList($department)
    {
      $stmt = $this->con->prepare("SELECT school_year FROM report WHERE department_id=? GROUP BY school_year ORDER BY school_year ASC");
      $stmt->bind_param("i", $department);
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








    //test code BELOW!!!
    public function sampleFunc($status)
    {
        $stmt = $this->con->prepare("SELECT * FROM user WHERE status=?");
        $stmt->bind_param("i",$status);
        $stmt->execute();
        $result = $stmt->get_result();

        while($row = $result->fetch_assoc())
        {
           $rows [] = $row;
        }

        $stmt->free_result();
        $stmt->close();

        return $rows;

    }


    //Method to register a new student
    public function createStudent($name,$username,$pass){
        if (!$this->isStudentExists($username)) {
            $password = md5($pass);
            $apikey = $this->generateApiKey();
            $stmt = $this->con->prepare("INSERT INTO students(name, username, password, api_key) values(?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $username, $password, $apikey);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return 0;
            } else {
                return 1;
            }
        } else {
            return 2;
        }
    }

    //Method to let a student log in
    public function studentLogin($username,$pass){
        $password = md5($pass);
        $stmt = $this->con->prepare("SELECT * FROM students WHERE username=? and password=?");
        $stmt->bind_param("ss",$username,$password);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows>0;
    }


    //method to register a new facultly
    public function createFaculty($name,$username,$pass,$subject){
        if (!$this->isFacultyExists($username)) {
            $password = md5($pass);
            $apikey = $this->generateApiKey();
            $stmt = $this->con->prepare("INSERT INTO faculties(name, username, password, subject, api_key) values(?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $username, $password, $subject, $apikey);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return 0;
            } else {
                return 1;
            }
        } else {
            return 2;
        }
    }

    //method to let a faculty log in
    public function facultyLogin($username, $pass){
        $password = md5($pass);
        $stmt = $this->con->prepare("SELECT * FROM faculties WHERE username=? and password =?");
        $stmt->bind_param("ss",$username,$password);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows>0;
    }

    //Method to create a new assignment
    public function createAssignment($name,$detail,$facultyid,$studentid){
        $stmt = $this->con->prepare("INSERT INTO assignments (name,details,faculties_id,students_id) VALUES (?,?,?,?)");
        $stmt->bind_param("ssii",$name,$detail,$facultyid,$studentid);
        $result = $stmt->execute();
        $stmt->close();
        if($result){
            return true;
        }
        return false;
    }

    //Method to update assignment status
    public function updateAssignment($id){
        $stmt = $this->con->prepare("UPDATE assignments SET completed = 1 WHERE id=?");
        $stmt->bind_param("i",$id);
        $result = $stmt->execute();
        $stmt->close();
        if($result){
            return true;
        }
        return false;
    }

    //Method to get all the assignments of a particular student
    public function getAssignments($studentid){
        $stmt = $this->con->prepare("SELECT * FROM assignments WHERE students_id=?");
        $stmt->bind_param("i",$studentid);
        $stmt->execute();
        $assignments = $stmt->get_result();
        $stmt->close();
        return $assignments;
    }

    //Method to get student details
    public function getStudent($username){
        $stmt = $this->con->prepare("SELECT * FROM students WHERE username=?");
        $stmt->bind_param("s",$username);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $student;
    }

    //Method to fetch all students from database
    public function getAllStudents(){
        $stmt = $this->con->prepare("SELECT * FROM students");
        $stmt->execute();
        $students = $stmt->get_result();
        $stmt->close();
        return $students;
    }

    //Method to get faculy details by username
    public function getFaculty($username){
        $stmt = $this->con->prepare("SELECT * FROM faculties WHERE username=?");
        $stmt->bind_param("s",$username);
        $stmt->execute();
        $faculty = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $faculty;
    }

    //Method to get faculty name by id
    public function getFacultyName($id){
        $stmt = $this->con->prepare("SELECT name FROM faculties WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $faculty = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $faculty['name'];
    }

    //Method to check the student username already exist or not
    private function isStudentExists($username) {
        $stmt = $this->con->prepare("SELECT id from students WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //Method to check the faculty username already exist or not
    private function isFacultyExists($username) {
        $stmt = $this->con->prepare("SELECT id from faculties WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //Checking the student is valid or not by api key
    public function isValidStudent($api_key) {
        $stmt = $this->con->prepare("SELECT id from students WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //Checking the faculty is valid or not by api key
    public function isValidFaculty($api_key){
        $stmt = $this->con->prepare("SELECT id from faculties WHERE api_key=?");
        $stmt->bind_param("s",$api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows>0;
    }

    //Method to generate a unique api key every time
    private function generateApiKey(){
        return md5(uniqid(rand(), true));
    }
}
