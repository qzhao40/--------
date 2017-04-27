<?php
require('../db/memberCheck.php');
require('../errorReporter.php');
require('../db/mgsConnection.php');
require('../db/memberConnection.php');
require('../retrieveColumns.php');

$censusTables = array('Census1827', 'Census1831', 'Census1834', 'Census1870', 'Census1891', 'Census1901');
$mainTables = array('Births', 'CemeteryRecords', 'ObituariesRural', 'ObituariesWinPap', 'BookRecords');
$specialTables = array('Baptism', 'Burial', 'ServentRecords');
$marriageTables = array('Marriages', 'ChurchMarriages');

$researcher = call_user_func(function () use ($userConn) {

	$sql = "SELECT 1 FROM researchers WHERE membernum =
      (SELECT membernum FROM members WHERE username = ?)";
	$stmt = sqlsrv_query($userConn, $sql, array($_SESSION['uname']));
	if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	return sqlsrv_fetch_array($stmt) !== null;

});

// admins, volunteers, researchers, inhouse
if($_SESSION['access'] != 1 || strtolower($_SESSION['uname']) === 'inhouse' || $researcher) {
	$mainTables = array_merge($mainTables, $specialTables);
}

// this will be our static array which contains standardized date feilds on the tables
// each table will have one or more of these year feilds.
$yearSearchFields = array("Birth", "Death", "EventYear");

// force checking the years if this is false
$nullYears = (isset($_POST['nullyears']) && $_POST['nullyears'] === 'on');

$loose = (isset($_POST['search-option']) && $_POST['search-option'] === '1');
$soundex = (isset($_POST['search-option']) && $_POST['search-option'] === '3');
$difference = (int)(isset($_POST['name-range'])
&& is_numeric($_POST['name-range']) ? $_POST['name-range'] : 4);

// if there are no firstname or last name we must redirect back, one is required
if((!(isset($_POST['lname'])) && !(isset($_POST['fname']))) || ($_POST['lname'] == '' && $_POST['fname'] == '')) {
	$_SESSION['error'] = "Please enter a first or last name.";
	header("location: /member/");
	exit(0);
}
elseif(isset($_POST['lname']) && is_numeric($_POST['lname']) || isset($_POST['fname']) && is_numeric($_POST['fname'])) {
	$_SESSION['error'] = "The first and last name can't be a number.";
	header("location: /member/");
	exit(0);
}
else {
	//check and set last name
	if(isset ($_POST['lname'])) {
		$lname = $_POST['lname'];
	}
	else {
		$lname = "";
	}
	//check and set first name
	if(isset ($_POST['fname'])) {
		$fname = $_POST['fname'];
	}
	else {
		$fname = "";
	}
}

$startOfYearSearch = isset($_POST['start']) && is_numeric($_POST['start']) ?
	$_POST['start'] : ($nullYears ? null : 0);

// set the end year to the current year if it's not given.
$endOfYearSearch = isset($_POST['end']) && is_numeric($_POST['end']) ?
	$_POST['end'] : ($nullYears ? null : (int)date('Y'));

// swap the years around if the end year is not null and the start year
// is larger than the end year.
if($endOfYearSearch && ($startOfYearSearch > $endOfYearSearch))
	list($startOfYearSearch, $endOfYearSearch) = array($endOfYearSearch, $startOfYearSearch);

// this array will hold the queries we need for results
$sqlQueriesArray = array();
$values = array();

// only search the census data if the birth and death can be null
// because the census data has no birth or death dates.
if($nullYears) {
	// we want to loop through each census table and determine if the
	// year searched falls within the census to qry..
	foreach($censusTables as $censusTable) {
		// strip the year from the table, all tables are named "CensusYYYY" where YYYY = year
		$censusTableYear = substr($censusTable, 6, 4); //takes 6th spot over, 4 spots right to isolate year.
		$censusArray = array();

		// check to see if this year falls within our search
		if(($startOfYearSearch == null || $censusTableYear >= $startOfYearSearch)
			&& ($endOfYearSearch == null || $censusTableYear <= $endOfYearSearch)
		) {
			if($lname != '') {
				if($soundex) {
					$censusArray[] = 'difference(LastName, ?) >= ?';
					$values[] = $lname;
					$values[] = $difference;
				}
				elseif($loose) {
					$censusArray[] = 'LastName LIKE ?';
					$values[] = "%$lname%";
				}
				else {
					$censusArray[] = 'LastName = ?';
					$values[] = $lname;
				}
			}

			if($fname != '') {
				$censusArray[] = "FirstName LIKE ?";
				$values[] = "%$fname%";
			}

			$censusWhereClause = implode(' AND ', $censusArray);
			$sqlQueriesArray[] = "SELECT ID, LastName, FirstName, NULL as 'Birth', NULL as 'Death', $censusTableYear as 'EventYear', TypeCode, '$censusTable' AS 'TableName' FROM $censusTable WHERE $censusWhereClause";
		}
	}
}

// only search in marriage tables if the birth and death dates can be null
// because the marriage tables don't have birth or death dates.
if($nullYears) {
	$groomArray = array();
	$brideArray = array();
	$groomValues = array();
	$brideValues = array();

	foreach ($marriageTables as $marriageTable) {
		if($lname != '') {
			if($soundex) {
				$groomArray[] = 'difference(GroomLastName, ?) >= ?';
				$groomValues[] = $lname;
				$groomValues[] = $difference;
				$brideArray[] = 'difference(BrideLastName, ?) >= ?';
				$brideValues[] = $lname;
				$brideValues[] = $difference;
			}
			elseif($loose) {
				$groomArray[] = 'GroomLastName LIKE ?';
				$groomValues[] = "%$lname%";
				$brideArray[] = 'BrideLastName LIKE ?';
				$brideValues[] = "%$lname%";
			}
			else {
				$groomArray[] = 'GroomLastName = ?';
				$groomValues[] = $lname;
				$brideArray[] = 'BrideLastName = ?';
				$brideValues[] = $lname;
			}
		}

		if($fname != '') {
			$groomArray[] = 'GroomLastName LIKE ?';
			$groomValues[] = "%$fname%";
			$brideArray[] = 'BrideLastName LIKE ?';
			$brideValues[] = "%$fname%";
	}

		if($startOfYearSearch != null && $endOfYearSearch != null) {
			$groomArray[] = "EventYear BETWEEN ? AND ?";
			$groomValues[] = $startOfYearSearch;
			$groomValues[] = $endOfYearSearch;
			$brideArray[] = "EventYear BETWEEN ? AND ?";
			$brideValues[] = $startOfYearSearch;
			$brideValues[] = $endOfYearSearch;
	}

		$groomWhereClause = implode(' AND ', $groomArray);
		$sqlQueriesArray[] = "SELECT ID, GroomLastName as 'LastName', GroomFirstName as 'FirstName', NULL as 'Birth', NULL as 'Death', EventYear, TypeCode, 'Marriages' AS 'TableName' FROM $marriageTable WHERE $groomWhereClause";

		$brideWhereClause = implode(' AND ', $brideArray);
		$sqlQueriesArray[] = "SELECT ID, BrideLastName as 'LastName', BrideFirstName as 'FirstName', NULL as 'Birth', NULL as 'Death', EventYear, TypeCode, 'Marriages' AS 'TableName' FROM $marriageTable WHERE $brideWhereClause";

		$values = array_merge($values, $groomValues, $brideValues);
	}
	
}

// we want to loop through each of the other tables getting the queries required
// and these ones we have to check for the years inside the tables
foreach($mainTables as $mainTable) {
	$birthPresent = false;
	$deathPresent = false;
	$eventPresent = false;
	$tableArray = array();
	$yearValues = array();

	// we need to query for the columns of these tables
	$columnNames = retrieveColumns($mainTable, 0, $conn);

	// we want to check if any of these columns match our event years static array
	foreach($columnNames as $columnName) {
		// loop though the years search fields
		foreach($yearSearchFields as $field) {
			// if a columnName matches the search field
			if(strtolower($field) === strtolower($columnName)) {
				// set flag true so we know if its present
				switch($field) {
					case 'Birth':
						$birthPresent = true;
					break;
					case 'Death':
						$deathPresent = true;
					break;
					case 'EventYear':
						$eventPresent = true;
					break;
				}

				// we are guarenteed a start and end date aswell so we use that on the
				// hit searchable year field that has matched the column
				if(is_numeric($startOfYearSearch) && is_numeric($endOfYearSearch)) {
					$tableArray[] = "$field BETWEEN ? AND ?";
					$yearValues[] = $startOfYearSearch;
					$yearValues[] = $endOfYearSearch;
				}
			}
		}
	}

	$yearFields = implode(' OR ', $tableArray);
	$nameArray = array();

	if($nullYears || ($birthPresent && $deathPresent) && $yearFields) {
		if($lname != '') {
			if($soundex) {
				$nameArray[] = 'difference(LastName, ?) >= ?';
				$values[] = $lname;
				$values[] = $difference;
			}
			elseif($loose) {
				$nameArray[] = 'LastName LIKE ?';
				$values[] = "%$lname%";
			}
			else {
				$nameArray[] = 'LastName = ?';
				$values[] = $lname;
			}
		}

		if($fname != '') {
			$nameArray[] = 'FirstName LIKE ?';
			$values[] = "%$fname%";
		}
	}

	// here we want to determine which fields are present to generate the
	// correct SQL statements per table
	$whereClause = implode(' AND ', $nameArray);
	$yearSelects = implode(', ', array(
		$birthPresent ? 'Birth' : "NULL as 'Birth'",
		$deathPresent ? 'Death' : "NULL as 'Death'",
		$eventPresent ? 'EventYear' : "NULL as 'EventYear'"
	));

	// ignore tables that don't have the birth and death dates
	// and filter null values from tables that do have them.
	if($nullYears || ($birthPresent && $deathPresent)) {
		$excludeNull = ($nullYears ? '' : ($birthPresent && $deathPresent) ? 'and birth is not null and death is not null' : '');
		if($yearFields) {
			// after we finish all the column names we want to append the sql to its array
			$sqlQueriesArray[] = "SELECT ID, LastName, FirstName, $yearSelects, TypeCode, '$mainTable' AS 'TableName' FROM $mainTable WHERE $whereClause " . (($yearFields == "") ? "" : "AND ($yearFields) ") . "$excludeNull";
			$values = array_merge($values, $yearValues);
		}
		else {
			// after we finish all the column names we want to append the sql to its array
			$sqlQueriesArray[] = "SELECT ID, LastName, FirstName, $yearSelects, TypeCode, '$mainTable' AS 'TableName' FROM $mainTable WHERE $whereClause $excludeNull";
		}
	}
}

// generates one query from the array with all required queries
$qry = implode(' UNION ', $sqlQueriesArray);
// run the statement
$stmt = sqlsrv_query($conn, $qry, $values, array('Scrollable' => 'static'));
if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
/********************/

// builds the array for the JQry table
$tableRows = array();
while($row = sqlsrv_fetch_array($stmt)) {
	$tableRows[] = json_encode(array(
		$row['LastName'], $row['FirstName'],
		$row['Birth'], $row['Death'],
		$row['EventYear'], $row['TypeCode'],
		"<a href=singleRecord.php?tablename=" . $row['TableName'] . "&amp;id=" . $row['ID'] . " target=\"_blank\">Link</a>"
	));
}
?>

<!DOCTYPE html>
<html class="no-js">
<head>
	<meta charset="utf-8">

	<title>MGS <?= (isset($_SESSION['uname']) && strtolower($_SESSION['uname']) === 'inhouse')
			? 'Library' : 'Member' ?> </title>

	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">

	<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

	<link rel="stylesheet" href="/css/normalize.css">
	<link rel="stylesheet" href="/css/main.css">

	<link rel="stylesheet" href="/css/demo_table.css">
	<link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">

	<script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/jquery.js"></script>
	<script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>

	<script type="text/javascript">
		$(document).ready(function () {
			var calcDataTableHeight = function () {
				return $(window).height() * 55 / 100;
			};

			var asInitVals = [];
			var tableData = [];

			<?php foreach ($tableRows as $key => $row) : ?>
			tableData.push(JSON.parse('<?= addslashes($row) ?>').map(function (text) {
				return '' + text
			}));
			<?php endforeach ?>

			var oTable = $('#example').dataTable({
				"scrollY": calcDataTableHeight(),
				"scrollCollapse": true,
				"scrollX": true,
				"bProcessing": true,
				"bPaginate": true,
				"bsortClasses": false,
				"sPaginationType": 'full_numbers',
				"aLengthMenu": [ 10, 25, 50, 100, 500 ],
				"bFilter": true,
				"bInput": true,
				"aLengthMenu": [ 10, 25, 50, 100, 500 ],
				"fnInitComplete": function () {
					$('.dataTables_scrollFoot').insertAfter($('.dataTables_scrollHead'));
				},
				"aaData": tableData,
				"oLanguage": {"sSearch": "Search all columns:"},
				"aoColumns": [{"sTitle": "LastName"},
					{"sTitle": "FirstName"},
					{"sTitle": "Birth"},
					{"sTitle": "Death"},
					{"sTitle": "EventYear"},
					{"sTitle": "TypeCode"},
					{"sTitle": "SingleRecord"}]
			});

			$(window).resize(function () {
				var oSettings = oTable.fnSettings();
				oSettings.oScroll.sY = calcDataTableHeight();
				oTable.fnDraw();
			});

			/*
			 *  Found this function online http://www.hongkiat.com/blog/css-sticky-position/
			 */
			var stickyNavTop = $('#legend').offset().top;

			var stickyNav = function () {
				var scrollTop = $(window).scrollTop();

				if (scrollTop > stickyNavTop) {
					$('#legend').addClass('sticky');
					$('#absolute').addClass('absolute');
				} else {
					$('#legend').removeClass('sticky');
					$('#absolute').removeClass('absolute');
				}
			};

			stickyNav();

			$(window).scroll(function () {
				stickyNav();
			});

			$("tfoot input").keyup(function () {
				/* Filter on the column (the index) of this element */
				oTable.fnFilter(this.value, $("tfoot input").index(this));
			});

			/*
			 * Support functions to provide a little bit of 'user friendlyness' to the textboxes in
			 * the footer
			 */
			$("tfoot input").each(function (i) {
				asInitVals[i] = this.value;
			});

			$("tfoot input").focus(function () {
				if (this.className == "search_init") {
					this.className = "";
					this.value = "";
				}
			});

			$("tfoot input").blur(function (i) {
				if (this.value == "") {
					this.className = "search_init";
					this.value = asInitVals[$("tfoot input").index(this)];
				}
			});
		});
	</script>
	<style>
		tfoot {
			display: table-header-group;
		}
	</style>
</head>
<body>
<!--[if lt IE 7]>
<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade
	your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to
	improve your experience.</p>
<![endif]-->

<div id="resultsbackground">
	<div id="container" class="home">
		<div id="searchresults"><?php require('header.php'); ?></div>
		<div id="head"><p><?= $_SESSION['error']; ?></p></div>

		<div class='pageAlign'>
			<?php require('typelegend.php'); ?>

			<h3 id="h3searchresults">Search Results</h3>
			<table class="display" id="example">
				<thead>
				</thead>
				<tfoot>
				<tr>
					<th><input type='text' value='LastName' class='search_init'/></th>
					<th><input type='text' value='FirstName' class='search_init'/></th>
					<th><input type='text' value='Birth' class='search_init'/></th>
					<th><input type='text' value='Death' class='search_init'/></th>
					<th><input type='text' value='EventYear' class='search_init'/></th>
					<th><input type='text' value='TypeCode' class='search_init'/></th>
					<th></th>
				</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
</body>
</html>
