<?php
  // This file will be used to connect to the error form database

  // connect to the server
  $conn = sqlsrv_connect('(local)', array(
    'Database' => 'ErrorForm',
    'UID'      => 'ErrorForm-Login',
    'PWD'      => 'g70uU8GR'
  ));

  // test if the connection fails
  if ($conn === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
?>
