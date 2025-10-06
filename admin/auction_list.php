<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\Auction;
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'auction';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Auction | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Auction</h1>
            <p>Add new, or edit existing</p>
            <p><a href='./auction_new.php' class='button'>Create new auction</a></p>
            <ul class="tabs" data-tabs id="auction-tabs">
                <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Current / Future</a></li>
                <li class="tabs-title"><a data-tabs-target="panel2" href="#panel2">Archive</a></li>
            </ul>
            <div class="tabs-content" data-tabs-content="auction-tabs">
                <div class="tabs-panel is-active" id="panel1">
                    <h2>Current / Future auction</h2>
                    <?php
                        $AO = new Auction();
                        if (is_object($AO)) {
                            $Auctions = $AO->listAllItems(null,'current-future');
                            if (is_array($Auctions) && count($Auctions) > 0) {
                                echo "<table class='standard'>";
                                echo "<tr><th>&nbsp;</th><th>Starts</th><th>Number of lots</th><th>Info</th><th>Edit</th></tr>\n";
                                foreach($Auctions as $Item) {
                                    echo "<tr>";
                                    echo "<td>";
                                    if ($Item['RoomInfo']['ImgFilename'] != '' && file_exists(DOCUMENT_ROOT.$Item['RoomInfo']['ImgPath'].$Item['RoomInfo']['ImgFilename'])) {
                                        echo "<img src='".check_output($Item['RoomInfo']['ImgPath'].$Item['RoomInfo']['ImgFilename'])."' alt='Logo' style='width: 60px; height: auto;' />";
                                    }
                                    echo "</td>";
                                    echo "<td>".format_datetime($Item['AuctionStart'])."</td>";
                                    echo "<td>".$Item['NumberOfLots']."</td>";
                                    echo "<td>";
                                    echo "Bid extension: <strong>".$Item['BidExtensionTime']." seconds</strong><br/>";
                                    echo "Lot min length: <strong>".$Item['LotMinimumLength']." seconds</strong><br/>";
                                    echo "Bid increment: <strong>£".$Item['LotBidIncrement']."</strong>";
                                    echo "</td>";
                                    echo "<td><a href='./auction_edit.php?state=edit&id=".$Item['ID']."'><i class='fi-pencil'></i></a></td>";
                                    echo "</tr>\n";
                                }
                                echo "</table>";
                            } else {
                                echo "<p>No auctions planned</p>";
                            }
                        }
                    ?>
                </div>

                <div class="tabs-panel" id="panel2">
                    <h2>Archive auction</h2>
                    <?php
                        $AO = new Auction();
                        if (is_object($AO)) {
                            $Auctions = $AO->listAllItems(null,'archive');
                            if (is_array($Auctions) && count($Auctions) > 0) {
                                echo "<table class='standard'>";
                                echo "<tr><th>&nbsp;</th><th>Starts</th><th>Number of lots</th><th>Info</th><th>Edit</th></tr>\n";
                                foreach($Auctions as $Item) {
                                    echo "<tr>";
                                    echo "<td>";
                                    if ($Item['RoomInfo']['ImgFilename'] != '' && file_exists(DOCUMENT_ROOT.$Item['RoomInfo']['ImgPath'].$Item['RoomInfo']['ImgFilename'])) {
                                        echo "<img src='".check_output($Item['RoomInfo']['ImgPath'].$Item['RoomInfo']['ImgFilename'])."' alt='Logo' style='width: 60px; height: auto;' />";
                                    }
                                    echo "</td>";
                                    echo "<td>".format_datetime($Item['AuctionStart'])."</td>";
                                    echo "<td>".$Item['NumberOfLots']."</td>";
                                    echo "<td>";
                                    echo "Bid extension: ".$Item['BidExtensionTime']." seconds<br/>";
                                    echo "Lot min length: ".$Item['LotMinimumLength']."<br/>";
                                    echo "Bid increment: £".$Item['LotBidIncrement'];
                                    echo "</td>";
                                    echo "<td><a href='./auction_edit.php?state=edit&id=".$Item['ID']."'><i class='fi-pencil'></i></a></td>";
                                    echo "</tr>\n";
                                }
                                echo "</table>";
                            } else {
                                echo "<p>No archive auctions yet</p>";
                            }
                        }
                    ?>
                </div>
            </div>

            <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
                <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
                <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
            </div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>