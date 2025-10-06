<?php
	include("../assets/dbfuncs.php");
	checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

    use PeterBourneComms\CCA\Auction;
    
//---------------------1. Collect variables -------------------------------->
 
	$AuctionRoomID = clean_int($_POST['AuctionRoomID'] ?? null);
	$StartDate = $_POST['StartDate'] ?? null;
	$StartTime = $_POST['StartTime'] ?? null;
	
    $_SESSION['PostedForm']                     = $_POST;
    
    //Derive AuctionStart
    $AuctionStart = convert_jquery_date($StartDate)." ".$StartTime;
    $_SESSION['PostedForm']['AuctionStart'] = $AuctionStart;


//-------------------------------------2. Check form for errors------------------------------------>

    if ( $AuctionRoomID <= 0 ||  $StartDate == '' || $StartTime == '') {
		if ($AuctionRoomID <= 0) { $_SESSION['auctionroomerror'] = "<p class='error'>You need to select an auction room.</p>"; }
		if ($StartDate == '' || $StartTime == '') { $_SESSION['dateerror'] = "<p class='error'>You need to enter the auction start and time.</p>"; }
		
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"You have some errors in your information, please could you attend to any highlighted error messages.");
		$_SESSION['error'] = true;
		header("Location:auction_new.php");
		exit;
	}

//-------------------------------------3. insert into or update the database ------------------------------------>
    
    $AO = new Auction();
    
    $Seller_Percent = $AO->getSellerPercent();
    $Seller_UptoMax = $AO->getSellerUptoMax();
    $Buyer_Percent = $AO->getBuyerPercent();
    $Buyer_UptoMax = $AO->getBuyerUptoMax();
    $BidExtensionTime = $AO->getBidExtensionTime();
    $LotMinimumLength = $AO->getLotMinimumLength();
    $LotBidIncrement = $AO->getLotBidIncrement();
    
    //Now populate the object
    $AO->setAuctionRoomId($AuctionRoomID);
    $AO->setAuctionStart($AuctionStart);
    $AO->setSellerPercent($Seller_Percent);
    $AO->setSellerUptoMax($Seller_UptoMax);
    $AO->setBuyerPercent($Buyer_Percent);
    $AO->setBuyerUptoMax($Buyer_UptoMax);
    $AO->setBidExtensionTime($BidExtensionTime);
    $AO->setLotMinimumLength($LotMinimumLength);
    $AO->setLotBidIncrement($LotBidIncrement);
    
    $result = $AO->saveItem();

    $newID = $AO->getId();
//-------------------------------------4. Move on and clean up session vars ------------------------------------>

    if ($result === true) {
        unset($_SESSION['PostedForm']);

        $_SESSION['Message'] = array('Type'=>'success','Message'=>"Auction has been created - now complete the information");
        header("Location: auction_edit.php?id=".$newID);
    } else {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: auction_new.php");
    }