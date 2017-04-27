<?php
  require('../db/volunteerCheck.php');
  require('../errorReporter.php');
  require('../db/volunteerConnection.php');
  require('../retrieveColumns.php');

  if (!isset($_SESSION['message'])) $_SESSION['message'] = '';
  if (!isset($_SESSION['tableName'])) $_SESSION['tableName'] = '';
  if (!isset($_SESSION['error'])) $_SESSION['error'] = '';
?>

<!DOCTYPE HTML>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <?php header('X-UA-Compatible: IE=edge,chrome=1');?>
    <title>MGS Administrator</title>
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

        <h2 class="volunteerSpeal">Thank you volunteers for helping upload content to the MGS database.</h2>
        <p class="errorColor"><?= $_SESSION['error'] ?></p>
        <p class="msgColor"><?= $_SESSION['message'] ?></p>
        <?php $_SESSION['error'] = ""; ?>
        <?php $_SESSION['message'] = ""; ?>
        <?php $_SESSION['tableName'] = ""; ?>

        <h3>Search</h3>
        <form action="volunteerUpload.php" method="post">
          <select name="tableName" id="tableName">
            <?php $and = "AND TABLE_NAME NOT IN('Purchases', 'Category', 'Products', 'CemeteryTranscriptions')"; ?>
            <?php foreach (retrieveTableNames($conn, $and) as $table) : ?>
              <option value="<?= $table ?>"><?= $table ?></option>
            <?php endforeach; ?>
          </select>
          <input type="submit" Value="Search" />
        </form>
      </div>
    </div>
  </body>
</html>
