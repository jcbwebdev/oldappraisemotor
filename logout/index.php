<?php
	include("../assets/dbfuncs.php");

	unset($_SESSION['UserDetails']);
	
    $_SESSION['Message'] = array('Type'=>'warning','Message'=>"You have successfully logged out of the website.");
	header("Location:/");