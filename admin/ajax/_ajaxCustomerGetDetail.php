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
    
        $id = rawurldecode($_POST["id"] ?? '');
        /*if (isset($_POST['m'])) {
            $m = $_POST['m']; //Mode of operation
        } else { $m = null; }*/

        //Prepare object
        $CO = new Customer();
        
        if (!is_object($CO)) {
            $error = "NO OBJECTS";
        }

        //Now retrieve users list, depending on mode of operation
        /*switch($m) {
            case 'organisation':
                $Users = $CO->listAllItems($q,'organisation');
                break;
            case 'email':
                $Users = $CO->listAllItems($q,'email');
                break;
            default:
                $Users = $CO->listAllItems($q,'surname');
                break;
        }*/
        
        $Customer = $CO->getItemById($id);
        if (is_array($Customer) && count($Customer) > 0) {
            //Start the output
            $html = "<p>Customer found: ".$Customer['Company']."</p>";
            $json = $Customer;
            //Return auction rooms as a nice chunk of html as well
            $ARO = new AuctionRoom();
            if (is_object($ARO)) {
                //Step through the Customers auction rooms - and retrieve logos etc.
                if (is_array($Customer['AuctionRooms']) && count($Customer['AuctionRooms']) > 0) {
                    $count = 0;
                    $output = "";
                    foreach ($Customer['AuctionRooms'] as $Room) {
                        $RoomInfo = $ARO->getItemById($Room['AuctionRoomID']);
                        if (is_array($RoomInfo) && count($RoomInfo) > 0) {
                            if (isset($RoomInfo['ImgFilename']) && file_exists(DOCUMENT_ROOT.$RoomInfo['ImgPath'].$RoomInfo['ImgFilename'])) {
                                $output .= "<img src='".FixOutput($RoomInfo['ImgPath'].$RoomInfo['ImgFilename'])."' alt='".FixOutput($RoomInfo['Title'])."' style='height: 50px; width: auto; margin: 2px 6px;' />";
                            } else {
                                $output .= $RoomInfo['Title'];
                                $count++;
                                if ($count != count($Customer['AuctionRooms'])) {
                                    $output .= ", ";
                                }
                            }
                        }
                    }
                }
            }
            
        } else {
            $html = "<p><strong>Sorry</strong> - could not find that customer.</p>";
            $json = null;
        }
    
        
        
        if (isset($error) && $error !== '') {
            $retdata = array('success' => false, 'err' => $error);
        } else {
            $retdata = array('success' => true, 'html' => $html, 'json' => $json, 'auctionrooms' => $output);
        }
    
        echo json_encode($retdata);
    }