<?php
	include("../assets/dbfuncs.php");
	checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

    use PeterBourneComms\CMS\HomePage;
    
	$_SESSION['error'] = false;

//---------------------1. Collect form variables -------------------------------->
	$Title						= $_POST['Title'];
	$SubTitle					= $_POST['SubTitle'];
	$Content					= $_POST['Content'];
	//$Col2Content				= $_POST['Col2Content'];

	$MetaDesc					= $_POST['MetaDesc'];
	$MetaKey					= $_POST['MetaKey'];
	$MetaTitle					= $_POST['MetaTitle'];

    //$state = $_GET['state'];
    $_SESSION['PostedForm']     = $_POST;

//-------------------------------------2. Check form for errors------------------------------------>

	/*if ( $Title == '' || $Content == '' ) // || $Error == true)
	{
		if ( $Title == '' ) { $_SESSION['titleerror'] = "<p class=\"error\">Please enter the content title:</p>"; }
		#if ( $StrapLine == '' ) { $_SESSION['straperror'] = "<p class=\"error\">Please enter the strapline for this page:</p>"; }
		if ( $Content == '' ) { $_SESSION['contenterror'] = "<p class=\"error\">Please enter some content:</p>"; }
		#if ( $Error == true ) { $_SESSION['imageerror'] = "<p class=\"error\">".$ImageError."</p>"; }
		#if ( !CheckURLText($URLText,$ID) ) { $_SESSION['urlerror'] = "<p class=\"error\">Please enter a unique URL to use:</p>"; }
		$_SESSION['Message'] = array('Type'=>'error','Message'=>"You have some errors in your information, please could you attend to any highlighted error messages.");
		$_SESSION['error'] = true;
		header("Location:homepage.php");
		exit;
	}*/

//-------------------------------------3. Update object and database  ------------------------------------>

    //Populate some defaults - if nothing supplied
    if ($MetaTitle == '') { $MetaTitle = $Title; }
    //if ($ID <= 0) { $ID = null; }

    //Create a new Content object to populate and update/save
    $ContentObj = new HomePage();

    //Now populate the items
    $ContentObj->setTitle($Title);
    $ContentObj->setSubTitle($SubTitle);
    $ContentObj->setContent($Content);
    //$ContentObj->setCol2Content($Col2Content);
    //$ContentObj->setDateDisplay(date('Y-m-d',time()));
    $ContentObj->setMetaDesc($MetaDesc);
    $ContentObj->setMetaKey($MetaKey);
    $ContentObj->setMetaTitle($MetaTitle);

    //Now the author details
    $ContentObj->setAuthorID($_SESSION['UserDetails']['ID']);
    $ContentObj->setAuthorName($_SESSION['UserDetails']['FullName']);

    //Now save the item
    $result = $ContentObj->saveItem();


//-------------------------------------4. Move on and clean up session vars ------------------------------------>

    if ($result === true)
    {
        unset($_SESSION['PostedForm']);

        $_SESSION['Message'] = array('Type'=>'success','Message'=>"Home page has been updated");
        header("Location: /admin/");
    }
    else
    {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: homepage.php");
    }