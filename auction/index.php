<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('User', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\AuctionRoom;
    use PeterBourneComms\CCA\Auction;
    use PeterBourneComms\CCA\AuctionVehicle;
    
    $_SESSION['main'] = 'auction';
    unset($_SESSION['sub']);
    
    
    $AO = new Auction();
    $ARO = new AuctionRoom();
    $AVO = new AuctionVehicle();
    if (!is_object($AO) || !is_object($ARO) || !is_object($AVO)) {
        die();
    }
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Auctions | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <h1>Auctions</h1>
        <?php
            $Auctions = $AO->listAllItems(null,'current-future', null,'asc');
            if (is_array($Auctions) && count($Auctions) > 0) {
                echo "<p>The following auctions are available, or will be soon:</p>";
                echo "<div class='grid-x grid-margin-x small-up-1 medium-up-2 large-up-3 future-auctions-view'>";
                foreach ($Auctions as $Auction) {
                    //Some prep for this auction
                    //Image
                    if (isset($Auction['RoomInfo']['ImgFilename']) && file_exists(DOCUMENT_ROOT.$Auction['RoomInfo']['ImgPath'].$Auction['RoomInfo']['ImgFilename'])) {
                        $img = FixOutput($Auction['RoomInfo']['ImgPath'].$Auction['RoomInfo']['ImgFilename']);
                    } else {
                        $img = "/assets/img/placeholder-vehicle-thumb.png";
                    }
                    
                    //Vehicle info
                    $vehicles = $Auction['NumberOfLots'];
                    $remaining = $Auction['RemainingLots'];
                    
                    //Button
                    if (isset($Auction['AuctionStatus']['Result']) && isset($Auction['AuctionStatus']['Detail']) && $Auction['AuctionStatus']['Result'] === true) {
                        if ($Auction['AuctionStatus']['Detail']['Status'] === 'current') {
                            $buttonclass = "";
                            $buttonaction = " data-auction-id='".$Auction['ID']."'";
                            $buttontext = "Join";
                        } else {
                            $buttonclass = " disabled";
                            $buttonaction = "";
                            $buttontext = "Coming soon!";
                        }
                        $button = "<button id='GotoCurrentAuction' class='button small ".$buttonclass."' ".$buttonaction.">".$buttontext."</button>";
                    } else {
                        $button = "";
                    }
                    
                    //OUTPUT
                    echo "<div class='cell'>";
                    echo "<div class='auction-panel cca-panel'>";
                    echo "<h2>".$Auction['RoomInfo']['Title']."</h2>";
                    //print_r($Auction);
                    echo "<div class='auction-info'>";
                    echo "<div class='auction-logo'><img src='".$img."' alt='".FixOutput($Auction['RoomInfo']['Title'])."' /></div>";
                    echo "<div class='auction-data'>";
                    echo "<div class='auction-stats'>";
                    echo "<table class='stats'>";
                    echo "<tr><th>".$Auction['AuctionStatus']['Detail']['StartLabel']."</th><td>".$Auction['AuctionStatus']['Detail']['Starts']."</td></tr>";
                    echo "<tr><th>Vehicles:</th><td>".$vehicles."</td></tr>";
                    echo "<tr><th>Remaining:</th><td>".$remaining."</td></tr>";
                    echo "</table>";
                    echo "</div>"; //auction-stats
                    echo "<div class='auction-button'>";
                    echo $button;
                    echo "</div>"; //auction-button
                    echo "</div>"; //auction-info
                    echo "</div>"; //auction-info
                    echo "</div>"; //auction-panel
                    echo "</div>"; //cell
                }
                echo "</div>";
            } else {
                echo "<p>Sorry - there are no auctions planned. Please check back soon.</p>";
            }
        ?>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script>
        $(document).ready(function() {
            $('#GotoCurrentAuction').on('click', function(e) {
                e.preventDefault();
                var auctionid = $(this).data('auction-id');
                if (auctionid > 0) {
                    window.location.href = "/auction/"+auctionid;
                }
            });
        });
    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>