<?php
  header('X-UA-Compatible: IE=edge,chrome=1');
  
  session_name('register');
  session_start();

  require('../db/memberConnection.php');
  require('../errorReporter.php');

  if (isset($_SESSION['error'])) {
    // set the session error equal to error variable and then to null ("")
    $error = $_SESSION['error'];
    $_SESSION['error'] = '';
  }
  // if not, set the error variable to null ("")
  else {
    $error = '';
  }

  if (isset($_POST['uname']) && isset($_POST['password']) && isset($_POST['confirmpassword']) && isset($_POST['email'])){

    // set the counter variable to zero
    $counter = 0;

    // check if the username exists or no
    // select from members table where username entered is found
    $sql = "SELECT 1 FROM Members WHERE Username = ?";
    
    // execute the query
    $stmt = sqlsrv_query($userConn, $sql, array($_POST['uname']));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    // increment the counter variable, if row is found
    while (sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $counter++;

    // if counter is not equal to zero i.e. if a row exists
    if ($counter != 0) {
      //echo 'Username already Exists.<br />';

      // set the session error to an error message that username is already exists
      $_SESSION['error'] = 'Username Already Exists.';

      // locate to the register form page
      header("Location: index.php");

      // die or exit the current script
      die;
    }

    // compare the password and confirm password entered
    $test = strcmp($_POST['password'], $_POST['confirmpassword']);

    // checks to see if the password and confirm password match, if yes
    if ($test == 0) {
      // encrypt the entered password
      $_SESSION['encryptpw'] = hash("sha512", $_POST['password']);
      $_SESSION['email'] = $_POST['email'];
      $_SESSION['username'] = $_POST['uname'];
    } else { // if dont match
      // locate to the register form
      header("location: index.php");
      // set the session error to an error message
      $_SESSION['error'] = 'Passwords do not match.';
      // die or exit the script
      die;
    }

    if ($_POST['membership'] === 'no') {
      $_SESSION['existingAccount'] = FALSE;
    } else {
      $_SESSION['existingAccount'] = TRUE;
    }
  }
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <title>Member Registration</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>
        <?php if($_SESSION['existingAccount']) : ?>
          <div id="head">
            <h2>Link Account</h2>
          </div>
          <p>Please enter your full name and member number to link your new MANI login to your existing account.</p>
          <form action="register.php" method="post">
            <p class="errorColor"><?= $error ?></p>
            <label class="label" for="fname">First Name*</label>
            <input type="text" class="searching" name="fname" id="fname" placeholder="First Name" required autofocus><br/>
            <label class="label" for="lname">Last Name*</label>
            <input type="text" class="searching" name="lname" id="lname" placeholder="Last Name" required><br/>
            <label class="label" for="membernum">Member Number*</label>
            <input type="text" class="searching" name="membernum" id="membernum" placeholder="Member Number" required><br/>
            <input type="submit" class="submit" name="Submit" value="< Back" onClick="history.go(-1);return true;">
            <input type="reset" class="submit" name="reset" value="Reset">
            <input type="submit" class="submit" name="Submit" value="Register">
          </form>
        <?php else : ?>
          <p><b>Clicking Register will send you to PayPal. If you have an account you'll have the opportunity to log in. If you don't have an account you'll have the option to create an account or enter your information without one.</b></p>
          <p><b>We don't save your credit card information.</b></p>
          <form action="register.php" method="post">
            <p class="errorColor"><?= $error ?></p>
            <h3>Generations</h3>
            <p>A Manitoba Genealogical Society Membership comes with a free subscription to the Generations newsletter.</p> 
              <p>All NEW members will have it emailed. For renewing members, we strongly suggest you use this option as it is faster, more reliable, 
              full colour, and has interactive links. The emailed version my also contain addition content due to the page limit on the printed version.
              It's also more cost effective on our end meaning we have more money available to spend on other services for our members. However for existing members we will print and mail you a copy. Those who come to MGS on a regular basis can arrange to pick it up in person. You can also simply opt-out altogether. (though why would you want to?)</p>
            <input type="radio" class="gen" name="generations" value="2" checked> Emailed
            <input type="radio" class="gen" name="generations" value="1"> Mailed
            <input type="radio" class="gen" name="generations" value="3"> Printed
            <input type="radio" class="gen" name="generations" value="0"> Opt-out<br/>
            <h3>Associate Account</h3>
            <p>The standard membersip costs $50/year. However, if you live with someone who already has a standard membership you may become an associate of that member for only $20/year.
              If this is an associate account please enter the member number of the account that it is associated with. If this is to be a standard membership just leave this field blank.</p>
            <label class="label" for="associate">Member Number</label>
            <input type="text" class="searching" name="associate" placeholder="Member Number"><br/>
            <h3>Branch Membership</h3>
            <p>In addition to the standard MGS membership, you may also join a branch. With a branch membership you may attend meetings and recieve a branch-specific newsletter, among other benefits.
              You may join multiple branches, or even all of them if you want. You may also join a branch at a later time, there's no obligation to do it now.</p>
            <!--label class="branch" for="bplains">Beautiful Plains $10</label>
            <input type="checkbox" class="branchbox" name="branch[]" id="bplains" value="1">
            <label class="branch" for="dauphin">Dauphin $10</label>
            <input type="checkbox" class="branchbox" name="branch[]" id="dauphin" value="2">
            <label class="branch" for="southwest">Southwest $10 ($8 for associates)</label>
            <input type="checkbox" class="branchbox" name="branch[]" id="southwest" value="3">
            <label class="branch" for="swanvalley">Swan Valley $10</label>
            <input type="checkbox" class="branchbox" name="branch[]" id="swanvalley" value="4">
            <label class="branch" for="winnipeg">Winnipeg $12</label>
            <input type="checkbox" class="branchbox" name="branch[]" id="winnipeg" value="5"></br-->
            <?php
              $sql = "SELECT id, name, price FROM branch";
              $stmt = sqlsrv_query($userConn, $sql);
              while ($row = sqlsrv_fetch_array($stmt)) : ?>
                <label class="branch" for="<?= $row['name'] ?>">
                  <?= $row['name'] ?> <?= sprintf("$%.2f", $row['price']) ?>
                  <?php if ($row['id'] == 3) : ?>
                    (<?= sprintf("$%.2f", $row['price'] * 0.80) ?> for associates)
                  <?php endif ?>
                </label>
                <input type="checkbox" name="branch[]" class="branchbox" value="<?= $row['id'] ?>" id="<?= $row['name'] ?>">
                <br><br>
            <?php endwhile ?>
            <input type="submit" class="submit" name="Submit" value="< Back" onClick="history.go(-1);return true;">
            <input type="reset" class="submit" name="reset" value="Reset">
            <input type="submit" class="submit" name="Submit" value="Register">
          </form>
        <?php endif ?>
        <p>* required</p>
      </div>
    </div>
  </body>
</html>