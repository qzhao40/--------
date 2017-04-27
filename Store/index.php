<?php
	// if(!isset($_GET['test'])){
	// 	header("Location: notFound.php");
	// }
	$name = isset($_GET['name'])? $_GET['name']: 'store';
	$active = "";
    switch($name){
        case 'login':
            require('../db/loginCheck.php');
            require('../db/memberConnection.php');
            require('../errorReporter.php');
            $username = $_SESSION['uname'];
            $qry = "SELECT Expiry, YearJoined, FirstName, LastName FROM Members 
            		LEFT JOIN Membership ON Members.MemberNum = Membership.MemberNum
            		LEFT JOIN MemberInfo ON Members.MemberNum = MemberInfo.MemberNum
            		WHERE Username = ?";
            $stmt = sqlsrv_query($userConn, $qry, array($username));
            if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
            $row = sqlsrv_fetch_array($stmt);
            $active = $row['YearJoined'];
            $expiry = $row['Expiry'];
            break;
        default:
            session_name($name);
            session_start();
    }
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
	    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	</head>
	<body>
		<div id="resultsbackground">
	    	<div id="container" class="home">
	    		<?php require('../header.php'); ?>
				<div id="head">
					<h2>Welcome to the MGS e-Store. This site is in test mode and all features work except for the final purchase step (PayPal) that has been disabled.</h2>
				</div>
				<div id="content">
					<?php if($name === 'login') : ?>
						<p>Welcome <?= $row['FirstName']." ".$row['LastName']; ?>!</p>
					<?php else : ?>
						<p>Welcome Guest! Would you like to <a href="../login.php">log yourself in</a>? This store will allow you to purchase items without being a member or signing in as a member. If you are a member and you sign in to your MGS Member account, you will be able to access the MANI database and other member-only features.</p>
					<?php endif ?>
					<h3 class="h3store">The MGS e-Store</h3>
					<p>The e-Store currently offers books, CDs, and electronic downloads for the family historian and genealogist. Initially, online items will be limited but the offering will grow as we prepare and upload additional items ( there are over 1,500 Cemetery Transcriptions that will be added in 2017).</p>
					<h3 class="h3store">Pay-Per-View (This feature will be added in 2017) </h3>
					<p>Log in to MANI to use the Pay Per View (PPV) feature to search our database. Many items will have links to PPV copies of records that may help you in your research.</p>
					<?php if($active != ""): ?>
					<h3 class="h3store">MGS Member Status</h3>
					<p>Your membership expires <?= $expiry->format('Y-m-d') ?>. Follow the link below to gain access to MANI the MGS member area. <a href="http://mani.mbgenealogy.com/member/">mani.mbgenealogy.com</a></p>
					<?php endif; ?>
					<hr />
					<p>Browse through our <a href="store.php?name=<?= $name ?>">products</a>. We will be adding additional items for sale.</p>
					<p>If you wish to order publications by mail, please print our <a href="http://mbgenealogy.com/wp-content/uploads/2016/04/Publications-Order-Form3.pdf">order form</a>.</p>
					<p>Please <a href="http://www.mbgenealogy.com/contact-us">contact us</a> if you have any suggestions about how we can improve our service.</p>
					<p>See the <a href="http://www.mbgenealogy.com/conditions-of-use">Conditions of Use</a> page on the right menu for several Frequently Asked Questions.</p>
				</div>
			</div>
		</div>
	</body>
</html>