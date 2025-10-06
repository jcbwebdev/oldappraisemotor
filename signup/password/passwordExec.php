<?php
    include("../../assets/dbfuncs.php");

    use PeterBourneComms\CCA\User;

    //Deal with any referrer information
    $Password = $_POST['Password'];
    $Password2 = $_POST['Password2'];
    $UserID = $_SESSION['UserID'];
    
    $_SESSION['PostedForm']     = $_POST;
    
    $UO = new User();
    
    if (!is_object($UO)) {
        die();
    }
    
    //Check that some data was passed
    if (clean_int($UserID) <= 0 || $Password == '' || $Password != $Password2) {
        if ( clean_int($UserID) <= 0 ) {
            $_SESSION['Message'] = array('Type'=>'alert','Message'=>"There was a problem saving the last stage of your application. Please try again");
            header("Location: ../");
            exit;
        }
        if ( $Password == '' || $Password != $Password2 ) { $_SESSION['passworderror'] = "<p class='error'>Please enter your password in both boxes:</p>"; }
        
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Please check your details - we need the following information:");
        header("Location:/signup/password/");
        exit;
    }
    
    
    //Store User details
    $UO->getItemById($UserID);
    $UO->setPassword($Password);
    $result = $UO->saveItem();
    
    if (!is_numeric($UserID)) {
        error_log('passwordExec.php -> Could not save password');
        $error = true;
    }
    
    if ($error === true) {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: ../");
        exit;
    } else {
        unset($_SESSION['PostedForm']);
        unset($_SESSION['UserID']);
        //Move onto password screen
        header("Location:/signup/complete/");
        exit;
    }