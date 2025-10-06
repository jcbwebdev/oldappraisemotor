<?php
    include("../assets/dbfuncs.php");
    
    use PeterBourneComms\CCA\User;
    
    
    //DELETE
    if (isset($_POST['delete'])) {
        $delete = $_POST['delete'];
        $_SESSION['error'] = false;
        
        if ($delete == '1') {
            $UO = new User(clean_int($_POST['ID']));
            $result = $UO->deleteItem();
            
            if ($result === true) {
                $_SESSION['Message'] = array('Type' => 'warning', 'Message' => "User has been deleted.");
            }
            header("Location:user_list.php");
            exit;
        }
    }

    //Deal with any referrer information
    $ID = $_POST['ID'];
    $CustomerID = $_POST['CustomerID'] ?? null;
    $Title = $_POST['Title'] ?? null;
    $Firstname = $_POST['Firstname'] ?? null;
    $Surname = $_POST['Surname'] ?? null;
    $Mobile = $_POST['Mobile'] ?? null;
    $Email = $_POST['Email'] ?? null;
    $AdminLevel = $_POST['AdminLevel'] ?? null;
    $Status = $_POST['Status'] ?? null;
    
    $_SESSION['PostedForm']     = $_POST;
    
    $UO = new User();
    
    if (!is_object($UO)) {
        die();
    }
    
    $email_exists = $UO->emailExists($Email,$ID);
    
    //Check that some data was passed
    if ($Firstname == '' || $Surname == '' || $email_exists) {
        if ( $Firstname == '' ) { $_SESSION['firstnameerror'] = "<p class='error'>Please enter the first name:</p>"; }
        if ( $Surname == '' ) { $_SESSION['surnameerror'] = "<p class='error'>Please enter the surname:</p>"; }
        if ( $email_exists ) { $_SESSION['emailerror'] = "<p class='error'>Email is either invalid - or it already exists in the system. please check that the user doesn't already have an account.</p>"; }
        
        $_SESSION['error'] = true;
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Please check the details - we need the following information:");
        header("Location:./user_edit.php?id=".$ID);
        exit;
    }
    
    
    //Store Customer details
    $UO->getItemById($ID);
    $UO->setCustomerId($CustomerID);
    $UO->setTitle($Title);
    $UO->setFirstname($Firstname);
    $UO->setSurname($Surname);
    $UO->setMobile($Mobile);
    $UO->setEmail($Email);
    $UO->setAdminLevel($AdminLevel);
    $UO->setStatus($Status);
    
    
    $result = $UO->saveItem();
    
    
    if ($result != true) {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: ./user_edit.php?id=".$ID);
    } else {
        unset($_SESSION['PostedForm']);
        $_SESSION['Message'] = array('Type'=>'success','Message'=>"User updated");
        header("Location:./user_list.php");
    }