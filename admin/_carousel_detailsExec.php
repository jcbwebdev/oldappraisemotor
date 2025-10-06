<?php
	include("../assets/dbfuncs.php");
	checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

    use PeterBourneComms\CMS\Carousel;
    
    if (isset($_POST['delete'])) {
        $delete = $_POST['delete'];
        $_SESSION['error'] = false;
    
        if ($delete == '1') {
            $Carousel = new Carousel(clean_int($_POST['ID']));
            $result = $Carousel->deleteItem();
        
            if ($result === true) {
                $_SESSION['Message'] = array('Type' => 'warning', 'Message' => "Carousel image has been deleted.");
            }
            header("Location:carousel_list.php");
            exit;
        }
    }


//---------------------1. Collect variables -------------------------------->

	$ID							                = clean_int($_POST['ID']);
	$Title						                = $_POST['Title'];
	$Content					                = $_POST['Content'];
	$CTALink					                = $_POST['CTALink'];
	//$CTALabel					                = $_POST['CTALabel'];
    $DisplayOrder				                = $_POST['DisplayOrder'];

	$OldImgFilename				                = $_POST['OldImgFilename'];

	$state = $_GET['state'];

    $_SESSION['PostedForm']                     = $_POST;


//-------------------------------------2. Check form for errors------------------------------------>

    if ( $Title == '' && ($state == 'edit' && $OldImgFilename == ''))
	{
		if ($Title == '' && $OldImgFilename == '') { $_SESSION['titleerror'] = "<p class=\"error\">You need to supply a title OR an image for the carousel item.</p>"; }

		$_SESSION['Message'] = array('Type'=>'alert','Message'=>"You have some errors in your information, please could you attend to any highlighted error messages.");
		$_SESSION['error'] = true;
		header("Location:carousel_details.php?state=$state&id=$ID");
		exit;
	}

//-------------------------------------3. insert into or update the database ------------------------------------>

	if (clean_int($DisplayOrder) <= 0) { $DisplayOrder = 1000; }
    if ($ID <= 0) { $ID = null; }

    $Carousel = new Carousel($ID,1600,700,USER_UPLOADS.'/images/carousel/');

    //Now populate the objct
    $Carousel->setTitle($Title);
    $Carousel->setContent($Content);
    //$Carousel->setCTALabel($CTALabel);
    $Carousel->setCTALink($CTALink);
    $Carousel->setDisplayOrder($DisplayOrder);

    //Image
    if (isset($_POST['ImgFile']))
    {
        $Carousel->uploadImage($_POST['ImgFile']);
    }

    //Now the author details
    $Carousel->setAuthorID($_SESSION['UserDetails']['ID']);
    $Carousel->setAuthorName($_SESSION['UserDetails']['FullName']);

    //Now save the item
    $result = $Carousel->saveItem();

//-------------------------------------4. Move on and clean up session vars ------------------------------------>

    if ($result === true)
    {
        unset($_SESSION['PostedForm']);

        $_SESSION['Message'] = array('Type'=>'success','Message'=>"Carousel slide details have been updated for $Title");
        header("Location: carousel_list.php");
    }
    else
    {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: carousel_details.php?state=$state&id=".$ID);
    }