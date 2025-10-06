<?php
    include("../assets/dbfuncs.php");

    use PeterBourneComms\CCA\User;
    use PeterBourneComms\CCA\Customer;
    
    //DELETE
    if (isset($_POST['delete'])) {
        $delete = $_POST['delete'];
        $_SESSION['error'] = false;
        
        if ($delete == '1') {
            $CustO = new Customer(clean_int($_POST['ID']));
            $result = $CustO->deleteItem();
            
            if ($result === true) {
                $_SESSION['Message'] = array('Type' => 'warning', 'Message' => "Customer and users have been deleted.");
            }
            header("Location:customer_awaiting_approval_list.php");
            exit;
        }
    }

    //Deal with any referrer information
    $ID = $_POST['ID'];
    $Title = $_POST['Title'] ?? null;
    $Firstname = $_POST['Firstname'] ?? null;
    $Surname = $_POST['Surname'] ?? null;
    $Mobile = $_POST['Mobile'] ?? null;
    $Email = $_POST['Email'] ?? null;
    $CompanyEmail = $_POST['CompanyEmail'] ?? null;
    $Company = $_POST['Company'] ?? null;
    $Address1 = $_POST['Address1'] ?? null;
    $Address2 = $_POST['Address2'] ?? null;
    $Address3 = $_POST['Address3'] ?? null;
    $Town = $_POST['Town'] ?? null;
    $County = $_POST['County'] ?? null;
    $Postcode = $_POST['Postcode'] ?? null;
    $Tel = $_POST['Tel'] ?? null;
    $UserID = $_POST['UserID'] ?? null;
    $Status = $_POST['Status'] ?? null;
    
    
    $RemoveOldCustomer = $_POST['RemoveOldCustomer'] ?? null;
    $OldCustomerID = $_POST['OldCustomerID'] ?? null;
    
    
    
    //Also the Auction Rooms
    $posted = $_POST;
    reset($posted);
    $AuctionRooms = array();
    for ($i=1; $i <= count($posted); $i++)
    {
        if (stristr(key($posted), "Room"))
        {
            //this is a checkbox - now just need to add its current value to $messages
            $AuctionRooms[] = current($posted);
        }
        next($posted);
    }
    
    $_SESSION['PostedForm']     = $_POST;
    
    $CO = new Customer();
    $UO = new User();
    
    if (!is_object($UO) || !is_object($CO)) {
        die();
    }  
        
    $email_exists = $UO->emailExists($Email, $UserID);
    
    //Check that some data was passed
    if ($Title == '' || $Firstname == '' || $Surname == '' || $Mobile == '' || $email_exists || $Address1 == '' || $Town == '' || $Postcode == '' || $Tel == '') {
        if ( $Title == '' ) { $_SESSION['titleerror'] = "<p class='error'>Please select your title:</p>"; }
        if ( $Firstname == '' ) { $_SESSION['firstnameerror'] = "<p class='error'>Please enter your firstname:</p>"; }
        if ( $Surname == '' ) { $_SESSION['surnameerror'] = "<p class='error'>Please enter your surname:</p>"; }
        if ( $Mobile == '' ) { $_SESSION['mobileerror'] = "<p class='error'>Please enter your mobile number:</p>"; }
        if ( $email_exists ) { $_SESSION['emailerror'] = "<p class='error'>Sorry that email is either invalid - or it already exists in the system. please check that you don't already have an account. You can reset your password on the login screen.</p>"; }
        if ( $Address1 == '' ||  $Town == '' || $Postcode == '') { $_SESSION['addresserror'] = "<p class='error'>Please enter the first line of your address, the town and your postcode:</p>"; }
        if ( $Tel == '') { $_SESSION['telerror'] = "<p class='error'>Please enter the telephone number of your accounts department:</p>"; }
        
        $_SESSION['error'] = true;
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Please check your details - we need the following information:");
        header("Location:./customer_approval.php?state=edit&id=".$ID);
        exit;
    }
    
    
    //Store Customer details
    $CO->getItemById($ID);
    
    $CO->setCompany($Company);
    $CO->setAddress1($Address1);
    $CO->setAddress2($Address2);
    $CO->setAddress3($Address3);
    $CO->setTown($Town);
    $CO->setCounty($County);
    $CO->setPostcode($Postcode);
    $CO->setEmail($Email);
    $CO->setTel($Tel);
    $CO->setMobile($Mobile);
    $CO->setStatus($Status);
    $CO->setDateRegistered(date('Y-m-d H:i:s', time()));
    
    //Auction Rooms
    $CO->setAuctionRooms($AuctionRooms);
    
    $result1 = $CO->saveItem();
    
    
    //Now user
    $UO->getItemById($UserID);
    $UO->setCustomerId($ID);
    $UO->setTitle($Title);
    $UO->setFirstname($Firstname);
    $UO->setSurname($Surname);
    $UO->setMobile($Mobile);
    $UO->setEmail($Email);
    $UO->setStatus('Active');
    $result2 = $UO->saveItem();
    
    
    if ($result1 != true || $result2 != true) {
        $error = true;
    }
    
    //NOW - If RemoveOldCustomer == 'Y' - remove the old customer record
    if (isset($RemoveOldCustomer) && $RemoveOldCustomer === 'Y') {
        if (isset($OldCustomerID) && $OldCustomerID > 0) {
            $delresult = $CO->deleteItem($OldCustomerID);
            if ($delresult != true) {
                $error = true;
            }
        } else {
            error_log('customer_approvalExec.php -> tried to delete old custome record - but none supplied');
            $error = true;
        }
    }
    
    if ($error === true) {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: ./customer_approval.php?state=edit&id=".$ID);
        exit;
    } else {
        unset($_SESSION['PostedForm']);
        //Move onto password screen
        header("Location:./customer_awaiting_approval_list.php");
        exit;
    }