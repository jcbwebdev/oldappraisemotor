<?php
	include("../assets/dbfuncs.php");
	checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

    use PeterBourneComms\CCA\Auction;
    
    if (isset($_POST['delete'])) {
        $delete = $_POST['delete'];
        $_SESSION['error'] = false;
    
        if ($delete == '1') {
            $AO = new Auction(clean_int($_POST['ID']));
            $result = $AO->deleteItem();
        
            if ($result === true) {
                $_SESSION['Message'] = array('Type' => 'warning', 'Message' => "Auction has been deleted.");
            }
            header("Location:auction_list.php");
            exit;
        }
    }

//---------------------1. Collect variables -------------------------------->

	$ID = clean_int($_POST['ID'] ?? null);
    if ($ID <= 0) { die(); }
	$AuctionRoomID = clean_int($_POST['AuctionRoomID'] ?? null);
	$StartDate = $_POST['StartDate'] ?? null;
	$StartTime = $_POST['StartTime'] ?? null;
	$Seller_Percent = $_POST['Seller_Percent']/100 ?? null;
	$Seller_UptoMax = clean_int($_POST['Seller_UptoMax'] ?? null);
	$Seller_Fixed = clean_int($_POST['Seller_Fixed'] ?? null);
	$Buyer_Percent = $_POST['Buyer_Percent']/100 ?? null;
	$Buyer_UptoMax = clean_int($_POST['Buyer_UptoMax'] ?? null);
	$Buyer_Fixed = clean_int($_POST['Buyer_Fixed'] ?? null);
	$BidExtensionTime = clean_int($_POST['BidExtensionTime'] ?? null);
	$LotMinimumLength = clean_int($_POST['LotMinimumLength'] ?? null);
	$LotBidIncrement = clean_int($_POST['LotBidIncrement'] ?? null);
    
    $_SESSION['PostedForm']                     = $_POST;
    
    //Derive AuctionStart
    $AuctionStart = convert_jquery_date($StartDate)." ".$StartTime;
    $_SESSION['PostedForm']['AuctionStart'] = $AuctionStart;


//-------------------------------------2. Check form for errors------------------------------------>

    //Error check on fees
    if (($Seller_Percent > 0 && $Seller_UptoMax <= 0) || ($Seller_Percent > 0 && $Seller_Fixed > 0)) {
        $_SESSION['sfeeserror'] = "<p class='error'>You cannot set a % figure AND a fixed figure - and make sure if you set a % figure that you also set an upto max figure.</p>";
    } else {
        $_SESSION['sfeeserror'] = "";
    }
    
    if (($Buyer_Percent > 0 && $Buyer_UptoMax <= 0) || ($Buyer_Percent > 0 && $Buyer_Fixed > 0)) {
        $_SESSION['bfeeserror'] = "<p class='error'>You cannot set a % figure AND a fixed figure - and make sure if you set a % figure that you also set an upto max figure.</p>";
    } else {
        $_SESSION['bfeeserror'] = "";
    }
    
    
    if ( $AuctionRoomID <= 0 ||  $StartDate == '' || $StartTime == '' || ($BidExtensionTime <= 0 || $LotMinimumLength <= 0 || $LotBidIncrement <= 0) || $_SESSION['sfeeserror'] != '' || $_SESSION['bfeeserror'] != '') {
		if ($AuctionRoomID <= 0) { $_SESSION['auctionroomerror'] = "<p class='error'>You need to select an auction room.</p>"; }
		if ($StartDate == '' || $StartTime == '') { $_SESSION['dateerror'] = "<p class='error'>You need to enter the auction start and time.</p>"; }
		if ($BidExtensionTime <= 0 || $LotMinimumLength <= 0 || $LotBidIncrement <= 0) { $_SESSION['settingserror'] = "<p class='error'>You need to enter the bidding extension time (after a bid), the minimum lot duration and lot bid increment:</p>"; }

		$_SESSION['Message'] = array('Type'=>'alert','Message'=>"You have some errors in your information, please could you attend to any highlighted error messages.");
		$_SESSION['error'] = true;
		header("Location:auction_edit.php?id=$ID");
		exit;
	}

//-------------------------------------3. insert into or update the database ------------------------------------>
    
    $AO = new Auction($ID);

    //Now populate the object
    $AO->setAuctionRoomId($AuctionRoomID);
    $AO->setAuctionStart($AuctionStart);
    $AO->setSellerPercent($Seller_Percent);
    $AO->setSellerUptoMax($Seller_UptoMax);
    $AO->setSellerFixed($Seller_Fixed);
    $AO->setBuyerPercent($Buyer_Percent);
    $AO->setBuyerUptoMax($Buyer_UptoMax);
    $AO->setBuyerFixed($Buyer_Fixed);
    $AO->setBidExtensionTime($BidExtensionTime);
    $AO->setLotMinimumLength($LotMinimumLength);
    $AO->setLotBidIncrement($LotBidIncrement);
    
    $result = $AO->saveItem();

//-------------------------------------4. Move on and clean up session vars ------------------------------------>

    if ($result === true) {
        unset($_SESSION['PostedForm']);

        $_SESSION['Message'] = array('Type'=>'success','Message'=>"Auction details have been updated");
        header("Location: auction_list.php");
    } else {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: auction_edit.php?id=".$ID);
    }