<?php
    include("../assets/dbfuncs.php");
    
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
            header("Location:customer_list.php");
            exit;
        }
    }

    //Deal with any referrer information
    $ID = $_POST['ID'];
    $Company = $_POST['Company'] ?? null;
    $Address1 = $_POST['Address1'] ?? null;
    $Address2 = $_POST['Address2'] ?? null;
    $Address3 = $_POST['Address3'] ?? null;
    $Town = $_POST['Town'] ?? null;
    $County = $_POST['County'] ?? null;
    $Postcode = $_POST['Postcode'] ?? null;
    $Tel = $_POST['Tel'] ?? null;
    $Email = $_POST['Email'] ?? null;
    $Status = $_POST['Status'] ?? null;
    
    
    
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
    
    if (!is_object($CO)) {
        die();
    }
    
    //Check that some data was passed
    if ($Company == '' || $Address1 == '' || $Town == '' || $Postcode == '' || $Tel == '') {
        if ( $Company == '' ) { $_SESSION['companyerror'] = "<p class='error'>Please enter the company name:</p>"; }
        if ( $Address1 == '' ||  $Town == '' || $Postcode == '') { $_SESSION['addresserror'] = "<p class='error'>Please enter the first line of the address, the town and the postcode:</p>"; }
        if ( $Tel == '') { $_SESSION['telerror'] = "<p class='error'>Please enter the telephone number of the accounts department:</p>"; }
        
        $_SESSION['error'] = true;
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Please check the details - we need the following information:");
        header("Location:./customer_edit.php?id=".$ID);
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
    //$CO->setMobile($Mobile);
    $CO->setStatus($Status);
    //$CO->setDateRegistered(date('Y-m-d H:i:s', time()));
    
    //Auction Rooms
    $CO->setAuctionRooms($AuctionRooms);
    
    $result1 = $CO->saveItem();
    
    
    if ($result1 != true) {
        $error = true;
    }
    
    if ($error === true) {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: ./customer_edit.php?id=".$ID);
    } else {
        unset($_SESSION['PostedForm']);
        $_SESSION['Message'] = array('Type'=>'success','Message'=>"Customer updated");
        header("Location:./customer_list.php");
    }