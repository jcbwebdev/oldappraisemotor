<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */

    use PeterBourneComms\CCA\Vehicle;
    use PeterBourneComms\CCA\VehicleFeature;

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        
        include("../../assets/dbfuncs.php");
        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

        $Mode = $_POST['Mode'] ?? null;
        $ID = $_POST['ID'] ?? null;
        $VehicleID = $_POST['VehicleID'] ?? null;
        $Feature = $_POST['Feature'] ?? null;
        
        
        //Create objectd
        $VO = new Vehicle();
        $VFO = new VehicleFeature();
        
        if (!is_object($VO) || !is_object($VFO)) {
            echo "ooh";
            die();
        }

        /*
         * Various modes: list, insert, delete, update-order
         */
        $item = null;
        $html = null;
        
        switch ($Mode) {
            case 'list':
                $Features = $VFO->listAllItems($VehicleID, 'vehicle-id');
                //print_r($Features);
                if (is_array($Features) && count($Features) > 0) {
                    $html = "";
                    foreach ($Features as $Feature) {
                        $html .= "<div class='feature-item' id='Feature_".$Feature['ID']."' data-vehicle-id='".$Feature['VehicleID']."' data-feature-id='".$Feature['ID']."'><div><i class='fi-list feature-grab'> </i></div><div class='feature-feature'>".$Feature['Title']."</div><div class='feature-delete' data-vehicle-id='".$VehicleID."' data-feature-id='".$Feature['ID']."'><i class='fi-x'></i></div></div>\n";
                    }
                } else {
                    $html = "<p><em>No features added for this vehicle yet</em></p>";
                }
                break;


            case 'update-order':
                $i = 1;
                foreach ($_POST['Feature'] as $value) {
                    $VFO->getItemById($value);
                    $VFO->setDisplayOrder($i);
                    $VFO->saveItem();
                    $i++;
                }

                break;


            case 'insert':
                $VFO->createNewItem();
                $VFO->setVehicleId($VehicleID);
                $VFO->setTitle($Feature);
                $VFO->setDisplayOrder(10000);
                $result = $VFO->saveItem();
                $ID = $VFO->getID();
        
                if ($result == true) {
                    //Nothing (content will get reloaded by AJAX on host page)
                    //But lets send a feature html element back as an additional parameter
                    $item = $VFO->getItemById($ID);
                    $html = "<div class='feature-item' id='Feature_".$ID."' data-vehicle-id='".$VehicleID."' data-feature-id='".$ID."'><div><i class='fi-list feature-grab'> </i></div><div class='feature-feature'>".$Feature."</div><div class='feature-delete' data-vehicle-id='".$VehicleID."' data-feature-id='".$ID."'><i class='fi-x'></i></div></div>\n";
                } else {
                    $error = "There was a problem saving your feature to the system. Please try again.";
                }
                break;


            case 'delete':
                if (!is_numeric($ID) || $ID <= 0) {
                    $error = "No ID passed.";
                } else {
                    $Feature = $VFO->getItemById($ID);
                    if (!is_array($Feature)) {
                        $result = false;
                    } else {
                        $result = $VFO->deleteItem();
                    }

                    if ($result == true) {
                        $ret_result = true;
                        //Page will reload on true (well the ajax data will)
                    } else {
                        $error = "There was a problem deleting that feature. Please try again.";
                    }
                }
                break;


            default:
                die();
                break;
        }


        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true, 'html_content' => $html, 'json_content' => $item);
        }

        echo json_encode($retdata);
    }