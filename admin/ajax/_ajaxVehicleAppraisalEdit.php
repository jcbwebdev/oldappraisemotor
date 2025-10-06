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

        $ID = clean_int($_POST['ID'] ?? null);
        $VehicleID = clean_int($_POST['VehicleID'] ?? null);
        $Title = $_POST['Title'] ?? null;
        $Code = $_POST['Code'] ?? null;
        $LocX = $_POST['LocX'] ?? null;
        $LocY = $_POST['LocY'] ?? null;

        //Check some variables are present before proceeding
        if ($VehicleID <= 0 || $Code == '' || $LocX == '' || $LocY == '') {
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
        $VAO = new VehicleAppraisal();
        if (!is_object($VAO)) { die(); }

        //Is this an edit?
        if ($ID > 0) {
            //Yes- edit
            $VAO->getItemById($ID);
            $mode = 'edit';
        } else {
            $mode = 'new';
        }

        $VAO->setVehicleId($VehicleID);
        $VAO->setCode($Code);
        $VAO->setTitle($Title);
        $VAO->setLocX($LocX);
        $VAO->setLocY($LocY);

        $result = $VAO->saveItem();

        $newID = $VAO->getId();

        if ($result === true) {
            $ret_arr = array('Success' => 'Vehicle appraisal item added/edited', 'id'=> $newID, 'mode' => $mode, 'full' => '');
        } else {
            $ret_arr = array('Error' => 'Sorry there was a problem:'.$result);
        }

        echo json_encode($ret_arr);

    }