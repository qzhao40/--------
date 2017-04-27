<?php

	ini_set('memory_limit', '-1'); //fixes a memory cap limit
	error_reporting(0);
	require('../db/storeConnection.php');
	require('../retrieveColumns.php');

	$name = isset($_GET['name'])? $_GET['name']: 'store';

    switch($name){
    	case 'login':
            require('../db/loginCheck.php');
            require('../db/memberConnection.php');
            require('../errorReporter.php');
            break;
        default:
            session_name($name);
            session_start();
    }

    if(!isset($_SESSION['message'])) $_SESSION['message'] = "";
	if(!isset($_SESSION['error'])) $_SESSION['error'] = "";
	if(!isset($_SESSION['values'])) $_SESSION['values'] = "";

	$sTable = "Products";
	$municipality = "";
	if(isset($_GET['municipality'])){
		$sTable = "CemeteryTranscriptions";
		$municipality = "&municipality=".$_GET['municipality'];
		$_SESSION['municipality'] = $_GET['municipality'];
		if($_SESSION['products'] === true){
			$_SESSION['values'] = "";
		}
		$_SESSION['products'] = false;
	} else {
		$_SESSION['products'] = true;
		if(isset($_SESSION['municipality']) && $_SESSION['municipality'] != "" && !empty($_SESSION['municipality'])){
			$_SESSION['values'] = "";
		}
		$_SESSION['municipality'] = "";
	}
	
	
	if(isset($_SESSION['values']) && $_SESSION['values'] != "" && !empty($_SESSION['values'])){
		$total = 0;
		$holdvalues = array();
		for($i = 0; $i < count($_SESSION['values']); $i++){
			$holdvalues[] = $_SESSION['values'][$i];
			$i++;
			$holdvalues[] = $_SESSION['values'][$i];
			$total += $_SESSION['values'][$i];
		}
		$holdvalues = implode(",", $holdvalues);
	}

	$and = "AND COLUMN_NAME NOT IN('ID', 'Shipping', 'Download', 'StatusCode')";
	$aCol = retrieveColumns($sTable, $and, $storeConn);
	$aCol[] = "Add Product";

	header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
?>
<!DOCTYPE HTML>
<html lang="en-US">
	<head>
    	<meta charset="utf-8">
    	 <?php header('X-UA-Compatible: IE=edge,chrome=1');?>
    	<title>MGS Store</title>
    	<meta name="description" content="">
    	<meta name="viewport" content="width=device-width">
    	<link rel="stylesheet" href="/css/normalize.css">
	    <link rel="stylesheet" href="/css/main.css">

	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.min.css">
	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables_themeroller.css">

	    <script src="/DataTables-1.10.6/media/js/jquery.js"></script>
	    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.min.js"></script>
	    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    	<?php require('storeJS.php'); ?>
		<style>
		tfoot{
			display: table-header-group;
		}
		</style>
	</head>
	<body>
		<div id="resultsbackground">
	    	<div id="container" class="home">
	    		<?php require('../header.php'); ?>
				<div id="head">
					<p class="successColor"><?= $_SESSION['message'] ?></p>
					<p class = "errorColor"><?= $_SESSION['error'] ?></p>
					<?php $_SESSION['message'] = ""; ?>
					<?php $_SESSION['error'] = ""; ?>
					<h2>MGS Store</h2>
				</div>
				<span id="message"></span>
				<div id="items">Items in Cart: <span id="cart">0</span></div>
				<form id="viewcart" method="post" action="shoppingCart.php?name=<?= $name ?>" onsubmit="return toCart()">
					<input type="hidden" id="finalvalues" name="finalvalues" value="" />
					<input type="hidden" id="values" name="values" value="" />
					<input type="submit" id="submit" name="submit" value="View Shopping Cart" />
				</form>
				<button name="add" class="addcart" onclick="addToShoppingCart()">Add to Cart</button>
				<table class="display" id="example">
					<thead>
					</thead>
					<tfoot>
						<tr>
							<?php
				                foreach($aCol as $col_data){
				                	if($col_data != "Add Product")
				                    	echo "<th><input type='text' name='search_" . $col_data . "' placeholder=\"" . $col_data . "\" class='search_init' /></th>";
				                }                            
				            ?>
				        </tr>
					</tfoot>
					<tbody>
					</tbody>
				</table>
				<br />
				<br />
				<button name="add" class="addcart" onclick="addToShoppingCart()">Add to Cart</button>
			</div>
		</div>
	</body>
</html>