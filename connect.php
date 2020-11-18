<?php

    // Import PHPMailer classes into the global namespace
    // These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    // Load Composer's autoloader
    require 'vendor/autoload.php';


    define('HOST', 'localhost');
    define('USER', 'root');
    define('PASS', '');
    define('DB', 'account_manager');
    
    function connection()
    {
        $conn = new mysqli(HOST, USER, PASS,DB);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
    

    function login($username, $password)
    {
        $sql = "select *from account where email = ?";
        $conn = connection();

        $stm = $conn->prepare($sql);
        $stm->bind_param('s',$username);
        if(!$stm->execute())
        {
            return array('code' => 1,'error'=>'cannot execute command');
        }

        $result = $stm->get_result();

        if($result->num_rows == 0)
        {
            return array('code' => 2,'error'=>'user does not exists');

        }

        $data = $result->fetch_assoc();

        $hashed_pass = $data['password'];

        if(!password_verify($password, $hashed_pass))
        {
            return array('code' => 3,'error'=>'Invalid password' );

        }
        else if($data['activate'] == 0)
        {
            return array('code' => 4,'error'=>'The account is not activated' );

        }
        else
        {
            return array('code' => 0,'error'=>'','data'=> $data );

        }

    }
    function email_exists($email)
    {
        $sql = "select *from account where email = ?";
        $conn = connection();

        $stm = $conn->prepare($sql);
        $stm->bind_param('s', $email);
        if(!$stm->execute())
        {
            die('Query error:' . $stm->error);
        }

        $result = $stm->get_result();
        if($result->num_rows > 0)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    function register($email,$pass, $first, $last)
    {
        if(email_exists($email))
        {
            return array('code' => 1,'error' => 'Email exists');
            
        }
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $rand = random_int(0, 1000);
        $token = md5($email .'+'. $rand);
        $sql = 'insert into account(firstname, lastname, email, password, activate_token) values(?,?,?,?,?)';

        $conn = connection();
        $stm = $conn->prepare($sql);
        $stm->bind_param('sssss',$first,$last,$email,$hash,$token);

        if(!$stm->execute())
        {
            return array('code' => 2, 'error' => 'execute failed: '. $stm->error);
        }

        sendActivationEmail($email,$token);
        //send verification emailemail
        return array('code' => 0, 'error' => 'create account successful');

    }

    function sendActivationEmail($email, $token)
    {

        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();      
            $mail->charSet = 'UTF-8';                                        // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'antranttl0303@gmail.com';                     // SMTP username
            $mail->Password   = 'kyxosocyovxowyfr';                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('antranttl0303@gmai.com', 'Admin');
            $mail->addAddress($email, 'Người nhận');     // Add a recipient
            /*$mail->addAddress('ellen@example.com');               // Name is optional
            $mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');*/

            // Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Verify your email';
            $mail->Body    = "Click <a href='http://localhost/activate.php?email=$email&token=$token'>here</a> to verify your email";
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    
    function sendResetPassword($email, $token)
    {

        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP(); 
            $mail->charSet = 'UTF-8';                                           // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'antranttl0303@gmail.com';                     // SMTP username
            $mail->Password   = 'kyxosocyovxowyfr';                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('antranttl0303@gmai.com', 'Admin');
            $mail->addAddress($email, 'Người nhận');     // Add a recipient
            /*$mail->addAddress('ellen@example.com');               // Name is optional
            $mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');*/

            // Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Reset your password';
            $mail->Body    = "Click <a href='http://localhost/reset_password.php?email=$email&token=$token'>here</a> to restore your password";
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function activeAccount($email, $token)
    {
        $sql = 'select *from account where email = ? and activate_token = ? and activate = 0';
        $conn = connection();
        $stm = $conn->prepare($sql);
        $stm->bind_param('ss',$email,$token);

        if(!$stm->execute())
        {
            return array('code'=> 1,'error'=>'Can not execute command');
        }

        $result = $stm->get_result();
        if($result->num_rows == 0)
        {
            return array('code'=> 2,'error' => 'Email address or token can not found');
        }

        $sql = "update account set activate = 1,activate_token='' where email = ?";
        $stm = $conn->prepare($sql);
        $stm->bind_param('s',$email);
        if(!$stm->execute())
        {
            return array('code'=>1,'error'=>'Can not execute command');
        }


        return array('code'=> 0,'message' => 'Account activated');
    }

    function reset_password($email)
    {
        if(!email_exists($email))
        {
            return array('code'=> 1, 'error'=> 'Email does not exists');
        }

        $token = md5($email .'+'. random_int(1000,2000));
        $sql = 'update reset_token set token = ? where email = ?';

        $conn = connection();

        $stm = $conn->prepare($sql);
        $stm->bind_param('ss',$token,$email);

        if(!$stm->execute())
        {
            return array('code'=> 2, 'error'=> 'Can not execute command');
        }

        if($stm->affected_rows == 0)
        {
            //chưa có dong nào cuar email này, mình sẽ thêm vào dòng mới.
            $exp = time() + 3600 * 24; //hết hạn sau 24h 

            $sql = 'insert into reset_token values(?,?,?)';
            $stm = $conn->prepare($sql);
            $stm->bind_param('ssi',$email,$token,$exp);

            if(!$stm->execute())
            {
                return array('code'=> 1, 'error'=> 'Can not execute command');
            }
        }

        //chèn thành công or update thành công token của dòng đã có
        // giờ gửi mail tới user.
        $success = sendResetPassword($email,$token);

        return array('code'=>0,'success'=> $success);
    }


    function update_password($email,$pass)
    {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        //$rand = random_int(0, 1000);
        //$token = md5($email .'+'. $rand);
        $sql = ' update account set password = ? where email = ?';

        $conn = connection();
        $stm = $conn->prepare($sql);
        $stm->bind_param('ss',$hash,$email);

        if(!$stm->execute())
        {
            return array('code' => 2, 'error' => 'execute failed: '. $stm->error);
        }
        return array('code' => 0, 'error' => 'create account successful');

    }
?>