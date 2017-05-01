<?php


    function setResponse($response, $statusCode, $responseBody)
    {
      header('Content-Type: application/json');
      header("Access-Control-Allow-Origin: *");

      return $response->withStatus($statusCode)->withJson($responseBody);
    }


    function setSuccessResponse($response, $statusCode)
    {
      header('Content-Type: application/json');
      header("Access-Control-Allow-Origin: *");

      return $response->withStatus($statusCode);
    }


    function sendEmail($recipient, $subject, $body)
    {
      $mail = new PHPMailer;

      if(is_array($recipient))
      {
        for($counter=0; $counter < count($recipient); $counter++)
        {
          $email = $recipient[$counter]['email'];
          $mail->AddAddress($email);
        }
      }
      else
      {
        $mail->AddAddress($recipient);
      }

      $mail->isSMTP();                                                      // Set mailer to use SMTP
      $mail->SMTPAuth = true;                                               // Enable SMTP authentication
      $mail->Host = $_ENV['GMAIL']->GMAIL_HOST;
      $mail->Username = $_ENV['GMAIL']->GMAIL_EMAIL;                        // SMTP username
      $mail->Password = $_ENV['GMAIL']->GMAIL_PWORD;                        // SMTP password
      $mail->SMTPSecure = 'tls';                                            // Enable TLS encryption, `ssl` also accepted
      $mail->Port = 587;                                                    // TCP port to connect to
      //$mail->addReplyTo('ace@iacademy.com', 'ACE');
      $mail->SetFrom($_ENV['GMAIL']->GMAIL_EMAIL, $_ENV['GMAIL']->GMAIL_SENDER_NAME);
      $mail->Subject = $subject;
      $mail->Body = $body;
      $mail->IsHTML(true);

      return $mail->Send();
    }


    function randStrGen()
    {
      $len = 5;
      $result = "";
      $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
      $charArray = str_split($chars);

      for($i = 0; $i < $len; $i++)
      {
        $randItem = array_rand($charArray);
        $result .= "".$charArray[$randItem];
      }

      return $result;
    }


    function getTimestamp()
    {
      $date = new DateTime();
      $date->setTimezone(new DateTimeZone('Asia/Manila'));

      return $date;
    }


    function backupDatabase($backupFile)
    {
      $dbName = $_ENV['DB']->DB_NAME;
      $dbHost = $_ENV['DB']->DB_HOST;
      $dbUsername = $_ENV['DB']->DB_USERNAME;
      $command = $_ENV['PATH']->COMMAND_PATH_BACKUP . "--opt -h $dbHost -u $dbUsername $dbName > $backupFile";

      exec($command, $output, $return);

      return $return;
    }

    function logToFile($email, $msg)
    {
      $db = new DbOperation();

      $timestamp = getTimestamp()->format('Y-m-d H:i:s');
      $role = $db->getAccountRole($email);

      if($role == 1)
      {
        $name = "Super Admin";
      }
      else
      {
        $name = $db->getFirstName($email) . " " . $db->getLastName($email);
      }

      $fh = fopen('log.txt', 'a');
      fwrite($fh, "[" . $timestamp . "]" . " " . $name . " " . $msg . PHP_EOL);
      fclose($fh);
    }


?>
