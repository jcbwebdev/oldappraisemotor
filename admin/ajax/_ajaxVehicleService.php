<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */

    use PeterBourneComms\CCA\Vehicle;
    use PeterBourneComms\CCA\VehicleService;

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        
        include("../../assets/dbfuncs.php");
        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

        $Mode = $_POST['Mode'] ?? null;
        $ID = $_POST['ID'] ?? null;
        $VehicleID = $_POST['VehicleID'] ?? null;
        $Date = $_POST['Date'] ?? null;
        $Mileage = $_POST['Mileage'] ?? null;
        $Type = $_POST['Type'] ?? null;
        $Comments = $_POST['Comments'] ?? null;
        
        
        
        //Create objectd
        $VO = new Vehicle();
        $VSO = new VehicleService();
        
        if (!is_object($VO) || !is_object($VSO)) {
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
                $Services = $VSO->listAllItems($VehicleID, 'vehicle-id');
                //print_r($Services);
                if (is_array($Services) && count($Services) > 0) {
                    $html = "";
                    foreach ($Services as $Service) {
                        $html .= "<div class='service-item' id='Service_".$Service['ID']."' data-vehicle-id='".$Service['VehicleID']."' data-service-id='".$Service['ID']."'><div class='grid-x grid-margin-x'>";
                        $html .= "<div class='medium-2 cell'>".format_shortdate($Service['ServiceDate'])."</div>";
                        $html .= "<div class='medium-2 cell'>".$Service['Mileage']."</div>";
                        $html .= "<div class='medium-2 cell'>".$Service['Type']."</div>";
                        $html .= "<div class='medium-4 cell'>".$Service['Comments']."</div>";
                        $html .= "<div class='medium-2 cell'><button class='button service-delete'  data-vehicle-id='".$Service['VehicleID']."' data-service-id='".$Service['ID']."'>Delete</button></div>";
                        $html .= "</div></div>";
                    }
                } else {
                    $html = "<p><em>No service history added for this vehicle yet</em></p>";
                }
                break;


            case 'insert':
                $VSO->createNewItem();
                $VSO->setVehicleId($VehicleID);
                $VSO->setServiceDate(convert_jquery_date($Date));
                $VSO->setMileage($Mileage);
                $VSO->setType($Type);
                $VSO->setComments($Comments);
                $result = $VSO->saveItem();
                $ID = $VSO->getID();
                $Service = $VSO->getItemById($ID);
        
                if ($result == true) {
                    //Nothing (content will get reloaded by AJAX on host page)
                    $item = $VSO->getItemById($ID);
                    //set up html_content
                    $html = "";
                    $html .= "<div class='service-item' id='Service_".$Service['ID']."' data-vehicle-id='".$Service['VehicleID']."' data-service-id='".$Service['ID']."'><div class='grid-x grid-margin-x'>";
                    $html .= "<div class='medium-2 cell'>".format_shortdate($Service['ServiceDate'])."</div>";
                    $html .= "<div class='medium-2 cell'>".$Service['Mileage']."</div>";
                    $html .= "<div class='medium-2 cell'>".$Service['Type']."</div>";
                    $html .= "<div class='medium-4 cell'>".$Service['Comments']."</div>";
                    $html .= "<div class='medium-2 cell'><button class='button service-delete'  data-vehicle-id='".$Service['VehicleID']."' data-service-id='".$Service['ID']."'>Delete</button></div>";
                    $html .= "</div></div>";
                } else {
                    $error = "There was a problem saving your service entry to the system. Please try again.";
                }
                break;


            case 'delete':
                if (!is_numeric($ID) || $ID <= 0) {
                    $error = "No ID passed.";
                } else {
                    $Service = $VSO->getItemById($ID);
                    if (!is_array($Service)) {
                        $result = false;
                    } else {
                        $result = $VSO->deleteItem();
                    }

                    if ($result == true) {
                        $ret_result = true;
                    } else {
                        $error = "There was a problem deleting that entry. Please try again.";
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