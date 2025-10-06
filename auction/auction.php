<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('User', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\AuctionRoom;
    use PeterBourneComms\CCA\Auction;
    use PeterBourneComms\CCA\AuctionVehicle;
    use PeterBourneComms\CCA\Vehicle;
    
    $_SESSION['main'] = 'auction';
    $_SESSION['sub'] = 'room';
    
    
    $AO = new Auction();
    $ARO = new AuctionRoom();
    $AVO = new AuctionVehicle();
    $VO = new Vehicle();
    if (!is_object($AO) || !is_object($ARO) || !is_object($AVO) || !is_object($VO)) {
        die();
    }
    
    //Now need to derive the Auction Room we've been passed - and determine if its a live auction - with vehicles remaining etc.
    if (isset($_GET['a']) && is_numeric($_GET['a'])) {
        $id = $_GET['a'];
        $Auction = $AO->getItemById($id);
    } else {
        //echo "1";
        header('Location: /auction/');
        exit;
    }
    
    if (is_array($Auction) && count($Auction) > 0) {
        if (isset($Auction['AuctionStatus']['Detail']) && $Auction['AuctionStatus']['Detail']['Status'] === 'current' && $Auction['RemainingLots'] > 0) {
            //continue to display lots
        } else {
            //echo "2";
            header('Location: /auction/');
            exit;
        }
    } else {
        //echo "3";
        header('Location: /auction/');
        exit;
    }
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title><?php echo $Auction['RoomInfo']['Title']; ?> auction | Auctions | <?php echo $sitetitle; ?></title>
    <script src="<?php echo AUCTION_CONNECTION; ?>/socket.io/socket.io.js"></script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <?php
            //Logo first
            if (isset($Auction['RoomInfo']['ImgFilename']) && file_exists(DOCUMENT_ROOT.$Auction['RoomInfo']['ImgPath'].$Auction['RoomInfo']['ImgFilename'])) {
                $img = "<img src='".FixOutput($Auction['RoomInfo']['ImgPath'].$Auction['RoomInfo']['ImgFilename'])."' alt='".FixOutput($Auction['RoomInfo']['Title'])."' style='width: auto; height: 100px;' />";
            } else {
                $img = "";
            }
            
            echo "<div class='grid-x grid-margin-x'>";
            echo "<div class='medium-8 cell medium-order-1 small-order-2'>";
            echo "<h1>".$Auction['RoomInfo']['Title']." Auction</h1>";
            echo "<p>Started: <strong>".$Auction['AuctionStatus']['Detail']['Starts']."</strong>&nbsp;&nbsp;&nbsp;&nbsp;Vehicles remaining: <strong><span id='vehciles-remaining-detail'>".$Auction['RemainingLots']."</span></strong></p>";
            echo "</div>"; //cell
            echo "<div class='medium-4 cell medium-order-2 small-order-1 medium-text-right small-text-left'>";
            echo $img;
            echo "</div>"; //cell
            echo "</div>"; //grid
            
            $Lots = $AVO->listAllItems($id,'auction-id','remaining');
            if (is_array($Lots) && count($Lots) > 0) {
                //print_r($Lots);
                $counter = 1;
                
                echo "<div class='auction-live-lots-container'>";
                foreach($Lots as $Lot) {
                    //Get vehicle
                    $Vehicle = $VO->getItemById($Lot['VehicleID']);
                    if (!is_array($Vehicle) || count($Vehicle) <= 0) {
                        break;
                    }
                    //print_r($Vehicle);
                    //Image
                    $Images = $Vehicle['Images'];
                    if (is_array($Images) && count($Images) > 0) {
                        //Retrieve just the first image
                        $VehicleImage = $Images[0];
                        if ($VehicleImage['FullPath'] != '' && file_exists(FixOutput(DOCUMENT_ROOT . $VehicleImage['FullPath']))) {
                            $img = FixOutput($VehicleImage['FullPath']);
                        } else {
                            $img = "/assets/img/placeholder-vehicle-thumb.svg";
                        }
                    }
                    
                    //Status
                    if ((!isset($Lot['AuctionStatus']) || $Lot['AuctionStatus'] == '' || $Lot['AuctionStatus'] == 'Waiting') && $counter === 1) {
                        $lot_status = "active";
                    } else {
                        $lot_status = "inactive";
                    }
                    
                    
                    //OUTPUT
                    echo "<div class='auction-lot-summary ".$lot_status."' id='Lot_".$Lot['ID']."' data-lot-id='".$Lot['ID']."' data-auction-id='".$Auction['ID']."'>";
                    echo "<div class='grid-x grid-margin-x'>";
                    echo "<div class='medium-8 cell'><h2>".mb_convert_case($Vehicle['Make']." " .$Vehicle['Model'], MB_CASE_TITLE)."</h2></div>";
                    echo "<div class='medium-4 cell medium-text-right small-text-left'><span class='reg-display-small'>".$Vehicle['Reg']."</span></div>";
                    echo "</div>"; //grid
                    
                    echo "<div class='vehicle-and-bidding'>";
                    echo "<div class='vehicle-info'>";
                    echo "<div class='vehicle-image'>";
                    echo "<img src='".$img."' title='".FixOutput($Vehicle['Model'])."' alt='".FixOutput($Vehicle['Model'])."' />";
                    echo "</div>"; //vehicle-image
                    
                    echo "<div class='vehicle'>";
                    echo "<p><strong><span class='larger'>".number_format($Vehicle['Mileage'],0)."</span></strong> miles&nbsp;&nbsp;&nbsp;&nbsp;<strong>".date('Y',strtotime($Vehicle['DateOfFirstReg']))."</strong></p>";
                    echo "<p><strong>".mb_convert_case($Vehicle['FuelType'], MB_CASE_TITLE)."</strong>&nbsp;&nbsp;&nbsp;&nbsp;<strong>".$Vehicle['EngineSize']."cc</strong>&nbsp;&nbsp;&nbsp;&nbsp;<strong>".mb_convert_case($Vehicle['Transmission'], MB_CASE_TITLE)."</strong></p>";
                    if ($Vehicle['ServiceHistory'] != '') {
                        echo "<p>Service history: <strong>".$Vehicle['ServiceHistory']."</strong></p>";
                    }
                    echo "<button class='button vehicle-details-open' data-lot-id='".$Lot['ID']."' data-auction-id='".$Auction['ID']."'>View Details</button>";
                    echo "</div>"; //vehicle
                    echo "</div>"; //vehicle-info
                    
                    echo "<div class='bidding-info'>";
                    echo "<div class='bid-summary'>";
                    if ($lot_status === 'active') {
                        echo "<h3>Bidding</h3>";
                        echo "<div class='bid-grid'>";
                        echo "<div class='col-label'>Time remaining:</div>";
                        echo "<div class='col-value' id='time-remaining'>2m 30s</div>";
                        echo "</div>"; //bid-grid
                        echo "<div class='bid-grid'>";
                        echo "<div class='col-label'>Number of bids:</div>";
                        echo "<div class='col-value' id='no-of-bids'>5</div>";
                        echo "</div>"; //bid-grid
                        echo "<div class='bid-grid'>";
                        echo "<div class='col-label'>Reserve met?</div>";
                        echo "<div class='col-value' id='reserve-met'><i class='fi-checkbox green-icon large-icon'></i></div>";
                        echo "</div>"; //bid-grid
                        echo "<div class='bid-grid'>";
                        echo "<div class='col-label'>Proxy bid increments:</div>";
                        echo "<div class='col-value' id='bid-increments'>£100</div>";
                        echo "</div>"; //bid-grid
                    } else {
                        echo "<h3>Bidding not open yet</h3>";
                    }
                    echo "</div>"; //bid-summary
                    
                    if ($lot_status === 'active') {
                        echo "<div class='bid-actions'>";
                        //With you flash
                        echo "<div class='bid-info-panel blank'>";
                        //echo "With you - reserve not met";
                        echo "</div>";
                        echo "<div class='other-bid-actions'>";
                        //Current bid info
                        echo "<div class='bid-grid'>";
                        echo "<div class='col-label big-label'>Current bid:</div>";
                        echo "<div class='col-value big-value' id='current-bid'>£8,400</div>";
                        echo "</div>"; //bid-grid
                        echo "<div class='bid-grid'>";
                        echo "<div class='col-label medium-label'>Your last bid:</div>";
                        echo "<div class='col-value medium-value' id='you-last-bid'>£8,200</div>";
                        echo "</div>"; //bid-grid
                        
                        echo "<div class='bid-form'>";
                        echo "<label for='next-bid'>Next bid:</label>";
                        echo "<div class='input-group'><span class='input-group-label'>£</span><input name='next-bid' id='next-bid' data-lot-id='".$Lot['ID']."' data-auction-id='".$Auction['ID']."' type='number' step='100' class='input-group-field'/><div class='input-group-button'><button class='button' id='next-bid-submit'>Bid</button></div></div>";
                        echo "</div>"; //bid submit
                        
                        echo "</div>"; //other-bid-actions
                        echo "</div>"; //bid-actions
                    }
                    echo "</div>"; //bidding
                    
                    
                    echo "</div>"; //vehicle-and-bidding
                    
                    echo "</div>"; //auction-lot-summary
                    
                    $counter++;
                }
                echo "</div>"; //auction-live-lots-container
            }
        ?>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script>
        const socket = io("<?php echo AUCTION_CONNECTION; ?>");
        
        //Retrieve intital auction data
        var auctiondata = {
            auctionid: <?php echo $id; ?>,
            userid: <?php echo $_SESSION['UserDetails']['ID']; ?>
        };
        
        
        ////Auction connection stuff
        //var connectionURL = '<?php //echo AUCTION_CONNECTION; ?>//';
        //var socket = auctionTESTConnect(connectionURL, auctiondata);
        //
        ////console.log(socket);
        ///*console.log('Sending TEST on socket');
        //socket.emit('auction-TEST', auctiondata);
        //
        //socket.on('auction-TEST-return', function (retdata) {
        //    console.log('TEST returned: ');
        //    console.log(retdata);
        //});*/
        ////console.log('About to socket.emit 1st request: auction-info-request');
        ////socket.emit('auction-info-request', auctiondata);
        ////socket.emit('max-bid-info-request', auctiondata);
        //
        //
        //function auctionTESTConnect(connURL) {
        //    console.log('starting socket conn');
        //    if (connURL === "") { connURL = "//cca.local:8080"; }
        //    /*var connectionOptions = {
        //        "force new connection": true,
        //        "reconnectionAttempts": "Infinite",
        //        "timeout": 10000,
        //        "autoConnect": false,
        //        "transports": ['websocket']
        //    };*/
        //    //Check io is available (script has loaded and node running on server)
        //    if (typeof(io) == 'undefined') {
        //        //console.log('io undefined');
        //        //console.log('attempted to connect to: '+connURL);
        //        window.location.replace("/site-closed.php");
        //    }
        //
        //    var socket = io(connURL);
        //    //var socket = io.connect(connURL, connectionOptions);
        //    //socket.open();
        //
        //    console.log(socket);
        //    return socket;
        //}
        //
    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>