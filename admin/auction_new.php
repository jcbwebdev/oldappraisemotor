<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\AuctionRoom;
    use PeterBourneComms\CCA\Auction;
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'auction';
    
    $AO = new Auction();
    $ARO = new AuctionRoom();
    if (!is_object($AO) || !is_object($ARO)) {
        die();
    }
    
    
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        //This is an edit - take all variable from the SESSION
        //Repair date from Jquery?
        $_SESSION['PostedForm']['StartDate'] = convert_jquery_date($_SESSION['PostedForm']['StartDate']);
    } else {
        unset($_SESSION['PostedForm']);
        $_SESSION['PostedForm']['AuctionRoomID'] = "";
        $_SESSION['PostedForm']['Seller_Percent'] = $AO->getSellerPercent();
        $_SESSION['PostedForm']['Seller_UptoMax'] = $AO->getSellerUptoMax();
        $_SESSION['PostedForm']['Buyer_Percent'] = $AO->getBuyerPercent();
        $_SESSION['PostedForm']['Buyer_UptoMax'] = $AO->getBuyerUptoMax();
        $_SESSION['PostedForm']['BidExtensionTime'] = $AO->getBidExtensionTime();
        $_SESSION['PostedForm']['LotMinimumLength'] = $AO->getLotMinimumLength();
        $_SESSION['PostedForm']['LotBidIncrement'] = $AO->getLotBidIncrement();
    }
    
    unset($_SESSION['error']);
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Auction | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Auction</h1>
            <ul class="tabs" data-tabs id="auction-tabs">
                <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Auction info</a></li>
            </ul>
            <div class="tabs-content" data-tabs-content="auction-tabs">
                <div class="tabs-panel is-active" id="panel1">
                    <form action="./auction_newExec.php" name="form1" id='form1' enctype="multipart/form-data" method="post" class="standard">
                        <div class='callout'>
                            <h2>Date and room/brand</h2>
                            <?php if (isset($_SESSION['auctionroomerror'])) {
                                echo $_SESSION['auctionroomerror'];
                                unset($_SESSION['auctionroomerror']);
                            } ?>
                            <p><label for='AuctionRoomID'>Auction room/brand:</label><select name='AuctionRoomID' id='AuctionRoomID'>
                                    <option value='' <?php if ($_SESSION['PostedForm']['AuctionRoomID'] ?? null === '') { echo " selected='selected'"; } ?>>Please select...</option>
                                    <?php
                                        $Rooms = $ARO->listAllItems();
                                        if (is_array($Rooms) && count($Rooms) > 0) {
                                            foreach($Rooms as $Room) {
                                                echo "<option value='" . $Room['ID'] . "'";
                                                if ($_SESSION['PostedForm']['AuctionRoomID'] === $Room['ID']) {
                                                    echo " selected='selected'";
                                                }
                                                echo ">" . $Room['Title'] . "</option>";
                                            }
                                        }
                                    ?>
                                </select></p>
                            
                            <?php if (isset($_SESSION['dateerror'])) {
                                echo $_SESSION['dateerror'];
                                unset($_SESSION['dateerror']);
                            } ?>
                            <div class='grid-x grid-margin-x'>
                                <div class='medium-6 cell'>
                                    <p><label for='StartDate'>Date of auction:</label>
                                        <input type='text' class='date-field' name='StartDate' id='StartDate' value='<?php echo format_jquery_date(check_output($_SESSION['PostedForm']['StartDate'] ?? null)); ?>' placeholder='Start date' /></p>
                                </div>
                                <div class='medium-6 cell'>
                                    <p><label for='StartTime'>Time:</label>
                                        <input type='text' class='time-field' name='StartTime' id='StartTime' value='<?php echo check_output(format_time($_SESSION['PostedForm']['StartTime'] ?? null)); ?>' placeholder='Format: HH:mm (24 hour)' /></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class='callout success'>
                            <h2>Save and continue</h2>
                            <p class='lead'>Nothing is saved until you press the 'Save' button below:</p>
                            <input type='hidden' id='ID' name='ID' value='<?php if (isset($_SESSION['PostedForm']['ID'])) {
                                echo $_SESSION['PostedForm']['ID'];
                            } ?>'/>
                            <p>
                                <button class='button' type='submit' value='submit'>Save</button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./auction_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> Auctions</a>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script>
        $(document).ready(function () {
            //Dates
            $(".date-field").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'dd/mm/yy'
            }).attr('autocomplete', 'off');

            $('.time-field').timepicker({
                'timeFormat': 'H:i',
                'step': 30,
                'forceRoundTime': false,
                'minTime': '0',
                'maxTime': '23:59',
                'scrollDefault': '09:00'
            });
        });

    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>