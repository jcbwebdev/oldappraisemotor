<?php
    include("../assets/dbfuncs.php");

    use PeterBourneComms\CCA\User;
    use PeterBourneComms\CCA\Customer;

    //Deal with any referrer information
    $Title = $_POST['Title'] ?? null;
    $Firstname = $_POST['Firstname'] ?? null;
    $Surname = $_POST['Surname'] ?? null;
    $Mobile = $_POST['Mobile'] ?? null;
    $Email = $_POST['Email'] ?? null;
    $Company = $_POST['Company'] ?? null;
    $Address1 = $_POST['Address1'] ?? null;
    $Address2 = $_POST['Address2'] ?? null;
    $Address3 = $_POST['Address3'] ?? null;
    $Town = $_POST['Town'] ?? null;
    $County = $_POST['County'] ?? null;
    $Postcode = $_POST['Postcode'] ?? null;
    $Tel = $_POST['Tel'] ?? null;
    
    $_SESSION['PostedForm'] = $_POST;
    
    $CO = new Customer();
    $UO = new User();
    
    if (!is_object($UO) || !is_object($CO)) {
        die();
    }  
        
    $email_exists = $UO->emailExists($Email);
    
    //Check that some data was passed
    if ($Title == '' || $Firstname == '' || $Surname == '' || $Mobile == '' || $email_exists || $Address1 == '' || $Town == '' || $Postcode == '' || $Tel == '') {
        if ( $Title == '' ) { $_SESSION['titleerror'] = "<p class='error'>Please select your title:</p>"; }
        if ( $Firstname == '' ) { $_SESSION['firstnameerror'] = "<p class='error'>Please enter your firstname:</p>"; }
        if ( $Surname == '' ) { $_SESSION['surnameerror'] = "<p class='error'>Please enter your surname:</p>"; }
        if ( $Mobile == '' ) { $_SESSION['mobileerror'] = "<p class='error'>Please enter your mobile number:</p>"; }
        if ( $email_exists ) { $_SESSION['emailerror'] = "<p class='error'>Sorry that email is either invalid - or it already exists in the system. please check that you don't already have an account. You can reset your password on the login screen.</p>"; }
        if ( $Address1 == '' ||  $Town == '' || $Postcode == '') { $_SESSION['addresserror'] = "<p class='error'>Please enter the first lione of your address, the town and your postcode:</p>"; }
        if ( $Tel == '') { $_SESSION['telerror'] = "<p class='error'>Please enter the telephone number of your accounts department:</p>"; }
        
        $_SESSION['error'] = true;
        
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Please check your details - we need the following information:");
        header("Location:/signup/");
        exit;
    }
    
    
    //Store Customer details
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
    $CO->setStatus('Active');
    $CO->setDateRegistered(date('Y-m-d H:i:s', time()));
    $CO->saveItem();
    
    $CustomerID = $CO->getId();
    
    if (!is_numeric($CustomerID)) {
        error_log('signupExec.php -> Could not create Customer record');
        $error = true;
    } else {
        //Now user
        $UO->setCustomerId($CustomerID);
        $UO->setTitle($Title);
        $UO->setFirstname($Firstname);
        $UO->setSurname($Surname);
        $UO->setMobile($Mobile);
        $UO->setEmail($Email);
        $UO->setStatus('Active');
        $UO->setAdminLevel('F');
        $result = $UO->saveItem();
        
        $UserID = $UO->getId();
    }
    
    if (!is_numeric($UserID)) {
        error_log('signupExec.php -> Could not create User record');
        $error = true;
    }
    
    if ($error === true) {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: ./");
        exit;
    } else {
        unset($_SESSION['PostedForm']);
        $_SESSION['UserID'] = $UserID;
        //Move onto password screen
        header("Location:./password/");
        exit;
    }