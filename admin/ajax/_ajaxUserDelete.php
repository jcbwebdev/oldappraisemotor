<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */
    
    use PeterBourneComms\CMS\Member;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
        if (isset($_POST['UserID'])) {
            $UserID = $_POST['UserID'];
        } else { die(); }
    
        if ($UserID <= 0) { die(); }
    
        //Create object
        $UO = new Member();
        if (!is_object($UO)) { echo "ooh"; die(); }
    
        
        $User = $UO->getItemById($UserID);
        if (!is_array($User)) { $result = false; } else {
            $result = $UO->deleteItem($UserID);
        }
    
        if ($result == true) {
            $ret_result = true;
            //Page will reload on true (well the ajax data will)
        } else {
            $error = "There was a problem deleting that user. Please try again.";
        }
        
    
    
        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true, 'user' => $User);
        }
        
        //Set a session success message
        $_SESSION['Message'] = array('Type'=>'success','Message'=>"The user  (".$User['Firstname']." ".$User['Surname'].") has been deleted");
        
        echo json_encode($retdata);
    
    }