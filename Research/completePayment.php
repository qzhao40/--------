<?php
    header('X-UA-Compatible: IE=edge,chrome=1');
    if(isset($_GET['name']) && $_GET['name'] === "login"){
        require('../db/loginCheck.php');
        require('../db/memberConnection.php');
    }
?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>MGS Member</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    </script>
</head>
<body>
    <div id="resultsbackground">
        <div id="container" class="home">
            <div id="searchresults">
      <?php require('../header.php'); ?>
            </div>
        </div>
        <?php if($_GET['confirm'] === "true"): ?>
            <p>You have successfully purchased the research package.</p>
        <?php else: ?>
            <p>You have chosen not to purchase the research package.</p>
        <?php endif; ?>
    </div>
</body>
</html>