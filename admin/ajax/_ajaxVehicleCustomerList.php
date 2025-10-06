<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */
    
    use PeterBourneComms\CCA\Customer;
    use PeterBourneComms\CCA\AuctionRoom;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
        $q = rawurldecode($_POST["q"] ?? '');
        if (isset($_POST['m'])) {
            $m = $_POST['m']; //Mode of operation
        } else { $m = null; }

        //Prepare object
        $CO = new Customer();
        
        if (!is_object($CO)) {
            echo "NO OBJECT";
            die();
        }

        //Now retrieve users list, depending on mode of operation
        if ($q !== '') {
            switch ($m) {
                case 'name-email':
                    $Customers = $CO->listAllItems($q, 'name-email', 'asc', true);
                    break;
                default:
                    $Customers = $CO->listAllItems($q, 'name-email', 'asc', true);
                    break;
            }
        } else {
            $Customers = array();
        }
        
        //Start the output
        $output = "";
        
        if (is_array($Customers) && count($Customers) >= 1) {
            $output = "<table class='standard'>\n";
            $output .= "<tr><th>Company</th><th></th></tr>\n";
            
            foreach ($Customers as $Item) {
                $output .= "<tr>";
                $output .= "<td>".$Item['Company']."<br/>";
                $ARO = new AuctionRoom();
                if (is_object($ARO)) {
                    $AuctionRooms = $ARO->listAllItems();
                    if (is_array($AuctionRooms) && count($AuctionRooms) > 0) {
                        $TotalRooms = count($AuctionRooms);
                        //Now look at what Auctions Rooms this customer is part of
                        if (count($Item['AuctionRooms']) == $TotalRooms) {
                            $output .= "All auction rooms";
                        } else {
                            //List individual rooms
                            $count = 0;
                            foreach ($Item['AuctionRooms'] as $Room) {
                                $RoomInfo = $ARO->getItemById($Room['AuctionRoomID']);
                                if (is_array($RoomInfo) && count($RoomInfo) > 0) {
                                    if (isset($RoomInfo['ImgFilename']) && file_exists(DOCUMENT_ROOT.$RoomInfo['ImgPath'].$RoomInfo['ImgFilename'])) {
                                        $output .= "<img src='".FixOutput($RoomInfo['ImgPath'].$RoomInfo['ImgFilename'])."' alt='".FixOutput($RoomInfo['Title'])."' style='height: 50px; width: auto; margin: 2px 6px;' />";
                                    } else {
                                        $output .= $RoomInfo['Title'];
                                        $count++;
                                        if ($count != count($Item['AuctionRooms'])) {
                                            $output .= ", ";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $output .= "</td>";
                $output .= "<td><button class='button small' id='SelectCustomer' data-customer-id='".$Item['ID']."' data-customer-name='".FixOutput($Item['Company'])."'><i class='fi-check'></i> Select </button></td>";
                $output .= "</tr>\n";
            }
            $output .= "</table>";
        } else {
            $output = "<p><strong>Sorry</strong> - no customers found matching that search criteria</p>";
        }
        
        
        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true, 'detail' => $output);
        }
    
        echo json_encode($retdata);
    }