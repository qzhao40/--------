<?php
  // This file is used to create connection object to the Accounts database

  // Setting server name and connection array to connect with
  $conn = sqlsrv_connect('(local)', array(
    'Database'=>'MGS',
    'UID'=>'MGS-Login',
    'PWD'=>'g70uU8GR'
  ));

  // Tests connectivity and exits if connection fails
  if ($conn === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
?>