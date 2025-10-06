<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */

    use PeterBourneComms\CCA\Vehicle;
    use PeterBourneComms\CCA\AuctionVehicle;

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        
        include("../../assets/dbfuncs.php");
        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

        $Mode = $_POST['Mode'] ?? null;
        $ID = $_POST['ID'] ?? null;
        $AuctionID = $_POST['AuctionID'] ?? null;
        $VehicleID = $_POST['VehicleID'] ?? null;
        //print_r($_POST);
        
        
        //Create objectd
        $VO = new Vehicle();
        $AVO = new AuctionVehicle();
        
        if (!is_object($VO) || !is_object($AVO)) {
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
                $AuctionVehicles = $AVO->listAllItems($AuctionID, 'auction-id');
                //print_r($AuctionVehicles);
                if (is_array($AuctionVehicles) && count($AuctionVehicles) > 0) {
                    $html = "";
                    foreach ($AuctionVehicles as $AuctionVehicle) {
                        //Retrieve the actual vehicle details
                        $Vehicle = $VO->getItemById($AuctionVehicle['VehicleID']);
                        $html .= "<div class='auction-vehicle-detail' data-vehicle-id='".$AuctionVehicle['VehicleID']."' data-auction-vehicle-id='".$AuctionVehicle['ID']."' id='AuctionVehicle_".$AuctionVehicle['ID']."'>";
                        //Image
                        $img = "/assets/img/placeholder-vehicle-thumb.png";
                        if (is_array($Vehicle['Images']) && count($Vehicle['Images']) > 0) {
                            if ($Vehicle['Images'][0]['MediaFilename'] != '' && file_exists(DOCUMENT_ROOT.FixOutput($Vehicle['Images'][0]['MediaPath']."small/".$Vehicle['Images'][0]['MediaFilename'].".".$Vehicle['Images'][0]['MediaExtension']))) {
                                $img = FixOutput($Vehicle['Images'][0]['MediaPath']."small/".$Vehicle['Images'][0]['MediaFilename'].".".$Vehicle['Images'][0]['MediaExtension']);
                            }
                        }
                        $html .= "<div class='avd-image'>";
                        $html .= "<img src='".$img."' alt='".FixOutput($Vehicle['Reg'])."' />";
                        $html .= "</div>"; //end of img
                        //Reg and other info
                        $html .= "<div class='avd-info'>";
                        $html .= "<div class='avd-reg'><span class='reg-display-small'>".$Vehicle['Reg']."</span></div>";
                        $html .= "<p><span class='avd-label'>Make</span><br/><span class='avd-detail'>".$Vehicle['Make']."</span></p>";
                        $html .= "<p><span class='avd-label'>Model</span><br/><span class='avd-detail'>".$Vehicle['Model']."</span></p>";
                        $html .= "<p><span class='avd-label'>Date</span><br/><span class='avd-detail'>".format_shortdate($Vehicle['DateOfFirstReg'])."</span></p>";
                        $html .= "<p><span class='avd-label'>Mileage</span><br/><span class='avd-detail'>".number_format($Vehicle['Mileage'] ?? '', 0)."</span></p>";
                        $html .= "</div>"; //end of info
                        $html .= "</div>"; //end of vehicle panel
                    }
                } else {
                    $html = "<p><em>No vehicles in this auction yet</em></p>";
                }
                break;


            case 'update-order':
                if (isset($_POST['AuctionVehicle'])) {
                    $i = 1;
                    foreach ($_POST['AuctionVehicle'] as $value) {
                        $AVO->getItemById($value);
                        $AVO->setDisplayOrder($i);
                        $AVO->saveItem();
                        $i++;
                    }
                }
                break;

                
            case 'insert':
                //Get Vehicle info - to update the Status to say its in an auction
                $Vehicle = $VO->getItemById($VehicleID);
                if (is_array($Vehicle) && count($Vehicle) > 0) {
                    $AVO->createNewItem();
                    $AVO->setVehicleId($VehicleID);
                    $AVO->setAuctionId($AuctionID);
                    $AVO->setDisplayOrder(10000);
                    $result = $AVO->saveItem();
                    $ID = $AVO->getID();
                    if ($result == true) {
                        //Nothing (content will get reloaded by AJAX on host page)
                        //Update Vehicle status to show as in auction
                        $VO->setVehicleStatus('In auction');
                        $VO->saveItem();
                        $item = $AVO->getItemById($ID);
                    } else {
                        $error = "There was a problem updating the auction. Please try again.";
                    }
                } else {
                    $error = "Could not locate vehicle information";
                }
                break;

            case 'remove':
                if (!is_numeric($ID) || $ID <= 0) {
                    $error = "No ID passed.";
                } else {
                    //Delete Auction record - the AuctionVehicle class automatically resets the vehicle status to 'Waiting'
                    $result = $AVO->deleteItem($ID);
                    if ($result != true) {
                        $error = "Could not delete Auction record";
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