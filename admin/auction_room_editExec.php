<?php
	include("../assets/dbfuncs.php");
	checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

    use PeterBourneComms\CCA\AuctionRoom;
    
    if (isset($_POST['delete'])) {
        $delete = $_POST['delete'];
        $_SESSION['error'] = false;
    
        if ($delete == '1') {
            $ARO = new AuctionRoom(clean_int($_POST['ID']));
            $result = $ARO->deleteItem();
        
            if ($result === true) {
                $_SESSION['Message'] = array('Type' => 'warning', 'Message' => "Auction room has been deleted.");
            }
            header("Location:auction_room_list.php");
            exit;
        }
    }

//---------------------1. Collect variables -------------------------------->

	$ID							                = clean_int($_POST['ID']) ?? null;
	$Title						                = $_POST['Title'] ?? null;
	$Content					                = $_POST['Content'] ?? null;
	$OldImgFilename				                = $_POST['OldImgFilename'];

	$state = $_GET['state'];

    $_SESSION['PostedForm']                     = $_POST;


//-------------------------------------2. Check form for errors------------------------------------>

    if ( $Title == '' ) {
		if ($Title == '') { $_SESSION['titleerror'] = "<p class=\"error\">You need to supply a title for the auction room.</p>"; }

		$_SESSION['Message'] = array('Type'=>'alert','Message'=>"You have some errors in your information, please could you attend to any highlighted error messages.");
		$_SESSION['error'] = true;
		header("Location:auction_room_edit.php?state=$state&id=$ID");
		exit;
	}

//-------------------------------------3. insert into or update the database ------------------------------------>
    
    if ($ID <= 0) { $ID = null; }

    $ARO = new AuctionRoom($ID,600,600,USER_UPLOADS.'/images/auction-room-logos/');

    //Now populate the objct
    $ARO->setTitle($Title);
    $ARO->setContent($Content);
    
    //Image
    if (isset($_POST['ImgFile'])) {
        $ARO->uploadImage($_POST['ImgFile']);
    }
    
    //Now save the item
    $result = $ARO->saveItem();

//-------------------------------------4. Move on and clean up session vars ------------------------------------>

    if ($result === true) {
        unset($_SESSION['PostedForm']);

        $_SESSION['Message'] = array('Type'=>'success','Message'=>"Auction room details have been updated for $Title");
        header("Location: auction_room_list.php");
    } else {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: auction_room_edit.php?state=$state&id=".$ID);
    }