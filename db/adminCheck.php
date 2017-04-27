<?php

	require('memberCheck.php');

	if(!in_array($_SESSION['access'], [
		5,
		3,
	]))
		header('location: /member/');

