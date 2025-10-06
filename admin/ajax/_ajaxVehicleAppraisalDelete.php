<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main page

        If its opened any other way - it should fail gracefully
    */
    
    use PeterBourneComms\CCA\VehicleAppraisal;
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");
        
        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
        
        $VehicleID = clean_int($_POST['VehicleID']);
        $DefectID = clean_int($_POST['DefectID']);

        //Check some variables are present before proceeding
        if ($VehicleID <= 0 || $DefectID <= 0) {
            die();
        }

        //Check I can edit this vehicle
        /*if ($_SESSION['UserDetails']['AdminLevel'] != 'Full' && $_SESSION['UserDetails']['AdminLevel'] != 'Editor') {
            $VO = new TVA_Vehicle();
            $check = $VO->checkCanEdit($VehicleID, $_SESSION['UserDetails']['ID']);
            if (!$check['Success'] === true)
            {
                die();
            }
        }*/


        //Create object
        $VAO = new VehicleAppraisal($DefectID);
        if (!is_object($VAO)) { die(); }

        $result = $VAO->deleteItem();

        if ($result === true) {
            $ret_arr = array('Success' => 'Vehicle appraisal item deleted', 'full' => '');
        } else {
            $ret_arr = array('Error' => 'Sorry there was a problem:'.$result);
        }

        echo json_encode($ret_arr);
        
    }