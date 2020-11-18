<?php
  require_once('connect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="styleForm.css">
    <link rel="shortcut icon" href="Images/LOGO-G.png">

    <title>Tạo lại mật khẩu</title>
</head>
<body class="rs-pw">
<?php
    $error = '';
    $email = '';
    $message = 'Enter your email address to continue';
    if (isset($_POST['email'])) 
    {
        $email = $_POST['email'];

        if (empty($email)) {
            $error = 'Please enter your email';
        }
        else if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
            $error = 'This is not a valid email address';
        }
        else {
            // reset password
            reset_password($email);
            $message = 'If your email exists in the database, you will receive an email to reset your password.';
        }
    }
?>

    <div class="forgot-pw">
        <form action="" class="form" method='post'>
          
          <h2>Khôi phục tài khoản</h2>
          <div class="input-group">
            <input type="email" name="email" id="email" required>
            <label for="email">Nhập Email</label>
          </div>
          <div class="form-group">
            <p><?= $message ?></P>
          </div>
          <div class="form-group">
            <?php
              if(!empty($error))
              {
                echo "<div class='alert alert-danger'>$error</div>";
              }
            ?>
            <input type="submit" value="Submit" class="submit-btn">
          </div>
        </form>
      </div>
</body>
</html>