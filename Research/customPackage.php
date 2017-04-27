<?php
	$params = "";
	if(isset($_GET['name']) && $_GET['name'] === "login"){
		require('../db/loginCheck.php');
		require('../db/memberConnection.php');
		require('../errorReporter.php');
		$qry = "SELECT MemberNum, Verified FROM Members WHERE Username = ?";
		$stmt = sqlsrv_query($userConn, $qry, array($_SESSION['uname']));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		while($row = sqlsrv_fetch_array($stmt)){
			$memberNum = $row['MemberNum'];
			$verified = $row['Verified'];
		}

		$qry = "SELECT Expiry FROM Membership WHERE MemberNum = ?";
		$stmt = sqlsrv_query($userConn, $qry, array($memberNum));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		$expiry = sqlsrv_fetch_array($stmt)['Expiry']->format("Y-m-d");

		if($expiry < date("Y-m-d") && $verified != 1){
			$_SESSION['error'] = "You must renew your account before accessing this page.";
			header("location: /myAccount/");
		}

		$params = "?name=login";
	}
?>
<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
    	<title>Manitoba Genealogical Society</title>
	    <meta name="description" content="">
	    <meta name="viewport" content="width=device-width">
	    <link rel="stylesheet" href="/css/normalize.css">
	    <link rel="stylesheet" href="/css/main.css">
	</head>
	<body>
		<div id="resultsbackground">

        <div id="container" class="home">
		<?php require('../header.php'); ?>
		<div class="content">
			<h1>MGS Custom Research Package</h1>
			<form method="post" action="customCheckout.php<?= $params ?>">
				<h2>Search Information</h2>
				<label for="surname">Surname:</label>
				<input type="text" id="surname" name="surname" />
				<label for="givenName">Given name(s):</label>
				<input type="text" id="givenName" name="givenName" />
				<label for="description">Provide as much pertinent information about this person that you know of (eg. birth date, marriage, death, locations, religion, etc.) Be specific about what information you are searching for and the resources you have already checked. We have resources from many countries but our main resources and collections are those about the province of Manitoba.</label>
            	<textarea rows="5" cols="100" id="description" name="description" placeholder="Enter description here."></textarea>
				<br />
				<label for="researchlocations">Please select the place you want MGS to search:</label>
				<input type="checkbox" name="researchlocations[]" value="CemeteryIndex">Cemetery Index <br />
				<input type="checkbox" name="researchlocations[]" value="CrownLandsRegistyIndex">Crown Lands Registry Index<br />
				<input type="checkbox" name="researchlocations[]" value="ObituaryIndex">Obituary Index (Winnipeg and some rural) <br />
				<input type="checkbox" name="researchlocations[]" value="LocalHistoryBooks">Local History Books <br />
				<input type="checkbox" name="researchlocations[]" value="Census">Census (pre 1881, 1881, 1891, 1906, 1911) <br />
				<input type="checkbox" name="researchlocations[]" value="HendersonDirectories">Henderson Directories <br />
				<input type="checkbox" name="researchlocations[]" value="TelephoneDirectories">Telephone Directories <br />
				<input type="checkbox" name="researchlocations[]" value="AnglicanMarriages">Anglican Marriages and Baptism Registers <br />
				<input type="checkbox" name="researchlocations[]" value="CatholicMarriages">Catholic Marriages and Baptism Registers <br />
				<input type="checkbox" name="researchlocations[]" value="UnitedChurch">United Church Archives - Baptisms, marriages, burials <br />
				<br />
				<input type="submit" name="formSubmit" value="Checkout"/>
			</form>
		 </div>
	</div>
	</body>
</html>