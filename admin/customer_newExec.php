<?php
    include("../assets/dbfuncs.php");
    
    use PeterBourneComms\CCA\Customer;

    //Deal with any referrer information
    $Company = $_POST['Company'] ?? null;
    
    $_SESSION['PostedForm']     = $_POST;
    
    $CO = new Customer();
    
    if (!is_object($CO)) {
        die();
    }
    
    //Check that some data was passed
    if ($Company == '') {
        if ( $Company == '' ) { $_SESSION['companyerror'] = "<p class='error'>Please enter the company name:</p>"; }
        
        $_SESSION['error'] = true;
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Please check the details - we need the following information:");
        header("Location:./customer_new.php");
        exit;
    }
    
    
    //Store Customer details
    $CO->createNewItem();
    
    $CO->setCompany($Company);
    $CO->setStatus('Applied');
    
    $result1 = $CO->saveItem();
    $newID = $CO->getId();
    
    
    if ($result1 != true) {
        $error = true;
    }
    
    if ($error === true) {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: ./customer_new.php");
        exit;
    } else {
        unset($_SESSION['PostedForm']);
        //Move onto password screen
        header("Location:./customer_edit.php?id=".$newID);
        exit;
    }