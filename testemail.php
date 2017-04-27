<?php
	ini_set('max_execution_time', -1);
	require('errorReporter.php');
	require('db/memberConnection.php');
require('config_mail.php');
	//Date stuff
	$today = date('j F, Y');
	$thisYear = date('Y');
	$thisMonth = date('m');
	$nextMonth = date('m', strtotime('+1 month', strtotime($thisYear.'-'.$thisMonth)));
	$nextMonthLastDay = date('t F, Y', strtotime($thisYear.'-'.$nextMonth));
	$memberNums = array();

	//email stuff
	$defaultEmail = "contact@mbgenealogy.com";
	$filename = "MGS_Brochure.pdf";
	$file = "./PDF/".$filename;
    $file_size = filesize($file);
    $handle = fopen($file, "r");
    $content = fread($handle, $file_size);
    fclose($handle);
    $content = chunk_split(base64_encode($content));

    // a random hash will be necessary to send mixed content
    $separator = md5(time());

    // carriage return type (we use a PHP end of line constant)
     // carriage return type (we use a PHP end of line constant)
    $eol = PHP_EOL;


		// subject
		$subject = 'Your Membership Has Expired';
		// message
		$message = "
		<html>
			<head>
				<meta charset='utf-8'>
			  <title>Membership Expiry</title>
			</head>
			<body>
				<header>
					<img src='http://mani.mbgenealogy.com/img/MGS_logo1.png' style='float:left;'>
					<h1 style='text-align:center;'>Manitoba Genealogical Society Inc.</h1>
		   			<h4 style='text-align:center;'>Unit E – 1045 St. James Street, Winnipeg, MB Canada   R3H 1B1</h4>
		   			<h4 style='text-align:center;'>Phone: 204-783-9139   www.mbgenealogy.com</h4>
		   		</header>
		   		<div style='clear:both;'></div>
		   		<p>To: mike</p>
				<p>sdlkfjsdlkf</p><br/>
				<p>This notice is to inform you that your membership in the Manitoba Genealogical Society expired as of sdfsdf.</p> 
				<p>If you wish to renew, you can renew online at <a href='http://mani.mbgenealogy.com'>mani.mbgenealogy.com</a> and use PayPal. You can find directions at <a href='http://www.mbgenealogy.com'>www.mbgenealogy.com</a> and click on the MANI link in the menu on the left.</p>
				<p>You can also mail it in. A renewal form is attached. Please fill it in and send it along with your membership fee to the address at the top of this page.</p>
				<p>MGS Membership fees are $50.00 for Individual and $20.00 for Associate members. Thank you for your attention to this matter. Please ignore this notice if you have recently sent in your renewal.</p>
				<p>Yours truly,</p>
				<img height='50' width='400' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'>
				<p>Susan Thorpe - MGS Membership Committee</p>
			</body>
		</html>
		";

	

		$headers = "MIME-Version: 1.0" . $eol;
	    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol . $eol;
	    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
	    $headers .= "This is a MIME encoded message." . $eol . $eol;

	    // message
	    $headers .= "--" . $separator . $eol;
	    $headers .= "Content-Type: text/html; charset=iso-8859-1" . $eol;
	    $headers .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
	    $headers .= $message . $eol . $eol;

	    // attachment
	    $headers .= "--" . $separator . $eol;
	    $headers .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
	    $headers .= "Content-Transfer-Encoding: base64" . $eol;
	    $headers .= "Content-Disposition: attachment" . $eol . $eol;
	    $headers .= $content . $eol . $eol;
	    $headers .= "--" . $separator . "--";

		$message = Swift_Message
			::newInstance($subject, $headers )
			->setFrom($defaultEmail)
			->setTo('mbreiter@mts.net')
			->setContentType('text/html')
		;

		$result = $mailer->send($message);

		$sent = true;
		if ($sent) {
			$error = 'Message has been sent';
			$colour = 'green';
		} else {
			$error = 'Message has NOT been sent';
			$colour = 'red';
		}

?>