<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */
    
    use PeterBourneComms\CCA\Vehicle;
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        
        include("../../assets/dbfuncs.php");
        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
        
        $ID = $_POST['VehicleID'] ?? null;
        $Field = $_POST['Field'];
        $Value = $_POST['Value'];
        /*$Multiline = $_POST['Multiline'] ?? null;
        $Amount = $_POST['Amount'] ?? null;*/
        
        
        if (!is_numeric($ID) || $ID <= 0) {
            die();
        }
        
        //Let's check if we're allowed to do this before we go too far
        $VO = new Vehicle();
        if (!is_object($VO)) {
            die();
        }
        
        $Vehicle = $VO->getItemById($ID);
        if (!is_array($Vehicle) || count($Vehicle) <= 0) {
            die();
        }
        
        //If date fields - convert the format
        if ($Field === 'MOTExpires' || $Field === 'DateOfFirstReg') {
            $Value = convert_jquery_date($Value);
        }
        
        //Try the update
        $result = $VO->updateField($Field, $Value, $ID);
        
        if ($result !== true) {
            $error = "There was a problem saving your data to our system.";
        }
        
        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true, 'detail' => $result);
        }
        
        echo json_encode($retdata);
        
    }