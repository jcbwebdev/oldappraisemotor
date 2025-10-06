<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */
    
    use PeterBourneComms\CCA\Vehicle;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
        $q = rawurldecode($_POST["q"] ?? '');
        if (isset($_POST['m'])) {
            $m = $_POST['m']; //Mode of operation
        } else { $m = null; }

        //Prepare object
        $VO = new Vehicle();
        
        if (!is_object($VO)) {
            echo "NO OBJECT";
            die();
        }

        //Now retrieve users list, depending on mode of operation
        switch($m) {
            case 'reg-customer':
                $Vehicles = $VO->listAllItems($q,'reg-customer', 'asc');
                break;
            default:
                $Vehicles = $VO->listAllItems($q,'reg-customer', 'asc');
                break;
        }
        
        //Start the output
        $output = "";
        
        if (is_array($Vehicles) && count($Vehicles) >= 1) {
            $output = "<table class='standard'>\n";
            $output .= "<tr><th>Registration</th><th>Customer</th><th>Status</th><th>Auction</th><th>Edit</th></tr>\n";
            
            foreach ($Vehicles as $Item) {
                $output .= "<tr>";
                $output .= "<td><span class='reg-display-small'>".$Item['Reg']."</span></td>";
                $output .= "<td>".$Item['CustomerInfo']['Company']."</td>";
                $output .= "<td>".$Item['VehicleStatus']."</td>";
                $output .= "<td>";
                if (isset($Item['Auctions']) && is_array($Item['Auctions']) && count($Item['Auctions'])) {
                    foreach ($Item['Auctions'] as $Auction) {
                        if (is_array($Auction['Auction']['RoomInfo']) && count($Auction['Auction']['RoomInfo']) > 0) {
                            $Room = $Auction['Auction']['RoomInfo'];
                            if (isset($Room['ImgFilename']) && file_exists(DOCUMENT_ROOT.$Room['ImgPath'].$Room['ImgFilename'])) {
                                    $output .= "<img src='".FixOutput($Room['ImgPath'].$Room['ImgFilename'])."' alt='".FixOutput($Room['Title'])."' style='width: auto; height: 60px; margin: 2px 12px;' />";
                            }
                        }
                    }
                }
                $output .= "</td>";
                $output .= "<td><a href='./vehicle_edit.php?id=".$Item['ID']."'><i class='fi-pencil'></i></a></td>";
                $output .= "</tr>\n";
            }
            $output .= "</table>";
        } else {
            $output = "<p><strong>Sorry</strong> - no vehicles found matching that search criteria</p>";
        }
        
        
        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true, 'detail' => $output);
        }
    
        echo json_encode($retdata);
    }