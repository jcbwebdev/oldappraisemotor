<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\AuctionRoom;
    use PeterBourneComms\CCA\Auction;
    use PeterBourneComms\CCA\Vehicle;
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'auction';
    
    if (isset($_GET['id'])) {
        $id = clean_int($_GET['id']);
    } else {
        header("Location:auction_list.php");
        exit;
    }
    
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
        //its the first stage of an edit - so retrieve from the DB
        //Retrieve all the information for this area
        $Auction = $AO->getItemById($id);
        if (is_array($Auction) && count($Auction) > 0) {
            $_SESSION['PostedForm'] = $Auction;
            //Split the date into two fields
            $_SESSION['PostedForm']['StartDate'] = $Auction['AuctionStartDate'];
            $_SESSION['PostedForm']['StartTime'] = $Auction['AuctionStartTime'];
        }
    }
    
    unset($_SESSION['error']);
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Auction | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Auction</h1>
            <ul class="tabs" data-tabs data-deep-link='true' id="auction-tabs">
                <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Auction info</a></li>
                <li class="tabs-title"><a data-tabs-target="panel2" href="#panel2">Vehicles</a></li>
            </ul>
            <div class="tabs-content" data-tabs-content="auction-tabs">
                <div class="tabs-panel is-active" id="panel1">
                    <p>Edit the details as required.</p>
                    <form action="./auction_editExec.php" name="form1" id='form1' enctype="multipart/form-data" method="post" class="standard">
                        <div class='callout'>
                            <h2>Date and room/brand</h2>
                            <?php if (isset($_SESSION['auctionroomerror'])) {
                                echo $_SESSION['auctionroomerror'];
                                unset($_SESSION['auctionroomerror']);
                            } ?>
                            <p>
                                <label for='AuctionRoomID'>Auction room/brand:</label><select name='AuctionRoomID' id='AuctionRoomID'>
                                    <option value='' <?php if ($_SESSION['PostedForm']['AuctionRoomID'] ?? null === '') {
                                        echo " selected='selected'";
                                    } ?>>Please select...
                                    </option>
                                    <?php
                                        $Rooms = $ARO->listAllItems();
                                        if (is_array($Rooms) && count($Rooms) > 0) {
                                            foreach ($Rooms as $Room) {
                                                echo "<option value='".$Room['ID']."'";
                                                if ($_SESSION['PostedForm']['AuctionRoomID'] === $Room['ID']) {
                                                    echo " selected='selected'";
                                                }
                                                echo ">".$Room['Title']."</option>";
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
                                        <input type='text' class='date-field' name='StartDate' id='StartDate' value='<?php echo format_jquery_date(check_output($_SESSION['PostedForm']['StartDate'] ?? null)); ?>' placeholder='Start date'/>
                                    </p>
                                </div>
                                <div class='medium-6 cell'>
                                    <p><label for='StartTime'>Time:</label>
                                        <input type='text' class='time-field' name='StartTime' id='StartTime' value='<?php echo check_output(format_time($_SESSION['PostedForm']['StartTime'] ?? null)); ?>' placeholder='Format: HH:mm (24 hour)'/>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class='callout'>
                            <h2>Fees</h2>
                            <div class='grid-x grid-margin-x'>
                                <div class='medium-6 cell'>
                                    <h3>Seller</h3>
                                    <?php if (isset($_SESSION['sfeeserror'])) {
                                        echo $_SESSION['sfeeserror'];
                                        unset($_SESSION['sfeeserror']);
                                    } ?>
                                    <label for="Seller_Percent">% of auction value:</label>
                                    <div class='input-group'>
                                        <input type="number" name="Seller_Percent" id="Seller_Percent" value="<?php echo check_output($_SESSION['PostedForm']['Seller_Percent'] * 100 ?? ''); ?>" step='1' placeholder="Percentage of auction value" class='input-group-field'/>
                                        <span class='input-group-label'>%</span>
                                    </div>

                                    <label for="Seller_UptoMax">Up to a maximum of:</label>
                                    <div class='input-group'>
                                        <span class='input-group-label'>£</span>
                                        <input type="number" name="Seller_UptoMax" id="Seller_UptoMax" value="<?php echo check_output($_SESSION['PostedForm']['Seller_UptoMax'] ?? ''); ?>" placeholder="(optional) Up to a maximum of" class='input-group-field'/>
                                    </div>
                                    <h3>or</h3>
                                    <label for="Seller_Fixed">Fixed fee:</label>
                                    <div class='input-group'>
                                        <span class='input-group-label'>£</span>
                                        <input type="number" name="Seller_Fixed" id="Seller_Fixed" value="<?php echo check_output($_SESSION['PostedForm']['Seller_Fixed'] ?? ''); ?>" placeholder="Fixed fee" class='input-group-field'/>
                                    </div>
                                </div>

                                <div class='medium-6 cell'>
                                    <h3>Buyer</h3>
                                    <?php if (isset($_SESSION['bfeeserror'])) {
                                        echo $_SESSION['bfeeserror'];
                                        unset($_SESSION['bfeeserror']);
                                    } ?>
                                    <label for="Buyer_Percent">% of auction value:</label>
                                    <div class='input-group'>
                                        <input type="number" name="Buyer_Percent" id="Buyer_Percent" value="<?php echo check_output($_SESSION['PostedForm']['Buyer_Percent'] * 100 ?? ''); ?>" step='1' placeholder="Percentage of auction value" class='input-group-field'/>
                                        <span class='input-group-label'>%</span>
                                    </div>

                                    <label for="Buyer_UptoMax">Up to a maximum of:</label>
                                    <div class='input-group'>
                                        <span class='input-group-label'>£</span>
                                        <input type="number" name="Buyer_UptoMax" id="Buyer_UptoMax" value="<?php echo check_output($_SESSION['PostedForm']['Buyer_UptoMax'] ?? ''); ?>" placeholder="(optional) Up to a maximum of" class='input-group-field'/>
                                    </div>
                                    <h3>or</h3>
                                    <label for="Buyer_Fixed">Fixed fee:</label>
                                    <div class='input-group'>
                                        <span class='input-group-label'>£</span>
                                        <input type="number" name="Buyer_Fixed" id="Buyer_Fixed" value="<?php echo check_output($_SESSION['PostedForm']['Buyer_Fixed'] ?? ''); ?>" placeholder="Fixed fee" class='input-group-field'/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class='callout'>
                            <h2>Auction settings</h2>
                            <?php if (isset($_SESSION['settingserror'])) {
                                echo $_SESSION['settingserror'];
                                unset($_SESSION['settingserror']);
                            } ?>
                            <div class='grid-x grid-margin-x'>
                                <div class='medium-4 cell'>
                                    <label for='BidExtensionTime'>Bid extension time:</label>
                                    <div class='input-group'>
                                        <input type='number' step='1' placeholder='Bidding extends by (s)' value='<?php echo check_output($_SESSION['PostedForm']['BidExtensionTime']); ?>' name='BidExtensionTime' id='BidExtensionTime' class='input-group-field'/>
                                        <span class='input-group-label'>seconds</span>
                                    </div>
                                </div>
                                <div class='medium-4 cell'>
                                    <label for='LotMinimumLength'>Lot minimum duration:</label>
                                    <div class='input-group'>
                                        <input type='number' step='1' placeholder='Min duration of lot (s)' value='<?php echo check_output($_SESSION['PostedForm']['LotMinimumLength']); ?>' name='LotMinimumLength' id='LotMinimumLength' class='input-group-field'/>
                                        <span class='input-group-label'>seconds</span>
                                    </div>
                                </div>
                                <div class='medium-4 cell'>
                                    <label for='LotBidIncrement'>Lot bid increment:</label>
                                    <div class='input-group'>
                                        <span class='input-group-label'>£</span>
                                        <input type='number' step='1' placeholder='Bid increment (£)' value='<?php echo check_output($_SESSION['PostedForm']['LotBidIncrement']); ?>' name='LotBidIncrement' id='LotBidIncrement' class='input-group-field'/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class='callout alert'>
                            <h3>Delete</h3>
                            <div class="switch large">
                                <input class="switch-input" id="delete" type="checkbox" name="delete" value='1'>
                                <label class="switch-paddle" for="delete">
                                    <span class="show-for-sr">Delete?</span>
                                    <span class="switch-active" aria-hidden="true">Yes</span>
                                    <span class="switch-inactive" aria-hidden="true">No</span>
                                </label>
                            </div>
                            <p class='help-text'>Slide switch to Yes and Click Submit - auction will be deleted. If there are any vehicles assigned to this auction - they will be removed from it. THIS CANNOT BE UNDONE!</p>
                        </div>

                        <div class='callout success'>
                            <h2>Save your changes</h2>
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

                <div class="tabs-panel" id="panel2">
                    <h2>Vehicles in auction</h2>
                    <div class='grid-x grid-margin-x'>
                        <div class='small-6 cell'>
                            <h3>Available to auction</h3>
                            <p>Drag to the column on the right to move vehicles into the auction.</p>
                            <div class='auction-vehicle-list available-list' id='available-list'>
                                <?php
                                    $VO = new Vehicle();
                                    if (is_object($VO)) {
                                        $Vehicles = $VO->listAllItems(null, 'available-for-auction');
                                        $output = "";
                                        if (is_array($Vehicles) && count($Vehicles) > 0) {
                                            foreach ($Vehicles as $Vehicle) {
                                                $output .= "<div class='auction-vehicle-detail' data-vehicle-id='".$Vehicle['ID']."' id='Vehicle_".$Vehicle['ID']."'>";
                                                //Image
                                                $img = "/assets/img/placeholder-vehicle-thumb.png";
                                                if (is_array($Vehicle['Images']) && count($Vehicle['Images']) > 0) {
                                                    if ($Vehicle['Images'][0]['MediaFilename'] != '' && file_exists(DOCUMENT_ROOT.FixOutput($Vehicle['Images'][0]['MediaPath']."small/".$Vehicle['Images'][0]['MediaFilename'].".".$Vehicle['Images'][0]['MediaExtension']))) {
                                                        $img = FixOutput($Vehicle['Images'][0]['MediaPath']."small/".$Vehicle['Images'][0]['MediaFilename'].".".$Vehicle['Images'][0]['MediaExtension']);
                                                    }
                                                }
                                                $output .= "<div class='avd-image'>";
                                                $output .= "<img src='".$img."' alt='".FixOutput($Vehicle['Reg'])."' />";
                                                $output .= "</div>"; //end of img
                                                //Reg and other info
                                                $output .= "<div class='avd-info'>";
                                                $output .= "<div class='avd-reg'><span class='reg-display-small'>".$Vehicle['Reg']."</span></div>";
                                                $output .= "<p><span class='avd-label'>Make</span><br/><span class='avd-detail'>".$Vehicle['Make']."</span></p>";
                                                $output .= "<p><span class='avd-label'>Model</span><br/><span class='avd-detail'>".$Vehicle['Model']."</span></p>";
                                                $output .= "<p><span class='avd-label'>Date</span><br/><span class='avd-detail'>".format_shortdate($Vehicle['DateOfFirstReg'])."</span></p>";
                                                $output .= "<p><span class='avd-label'>Mileage</span><br/><span class='avd-detail'>".number_format($Vehicle['Mileage'] ?? null, 0)."</span></p>";
                                                $output .= "</div>"; //end of info
                                                $output .= "</div>"; //end of vehicle panel
                                            }
                                        }
                                    }
                                    echo $output;
                                ?>
                            </div>
                        </div>
                        <div class='small-6 cell'>
                            <h3>In this auction</h3>
                            <p>You set the order of the auction here as well.</p>
                            <div class='auction-vehicle-list in-auction-list' id='in-auction-list'></div>
                        </div>
                    </div>
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

        var AuctionID = <?php echo $_SESSION['PostedForm']['ID']; ?>;

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

            //
            // AUCTION VEHICLES
            //

            //Initialise list of in auction
            listAuctionVehicles();

            //Available list
            $("#available-list").sortable({
                connectWith: ".auction-vehicle-list",
                revert: "invalid",
                placeholder: "ui-state-highlight"
            });

            //In auction list
            $("#in-auction-list").sortable({
                connectWith: ".auction-vehicle-list",
                revert: "invalid",
                placeholder: "ui-state-highlight",
                receive: function (event, ui) {
                    var thisvehicleid = ui.item[0].dataset['vehicleId'];
                    //Now save it
                    $.ajax({
                        url: '/admin/ajax/_ajaxAuctionVehicles.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            AuctionID: AuctionID,
                            VehicleID: thisvehicleid,
                            Mode: 'insert'
                        },
                    }).done(function (result) {
                        //handle the server response
                        listAuctionVehicles();
                        if (result.err) {
                            alert('Sorry - there was a problem: ' + result.err);
                        } else {
                        }
                    });
                },
                update: function (event, ui) {
                    var data = $('#in-auction-list').sortable('serialize');
                    console.log(data);
                    //listAuctionVehicles();
                    updateAuctionOrder(data);
                },
                remove: function (event, ui) {
                    var thisauctionid = ui.item[0].dataset['auctionVehicleId'];
                    //Now save it
                    $.ajax({
                        url: '/admin/ajax/_ajaxAuctionVehicles.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            AuctionID: AuctionID,
                            ID: thisauctionid,
                            Mode: 'remove'
                        },
                    }).done(function (result) {
                        //handle the server response
                        if (result.err) {
                            alert('Sorry - there was a problem: ' + result.err);
                        }
                    });
                }
            });
        });


        function listAuctionVehicles() {
            $.ajax({
                type: "POST",
                url: '/admin/ajax/_ajaxAuctionVehicles.php',
                data: {
                    AuctionID: AuctionID,
                    Mode: 'list'
                },
                dataType: 'json'
            }).done(function (result) {
                if (result.err) {
                    //error
                    alert('There was a problem: ' + result.err);
                } else {
                    //Add the returned feature to the bottom of the list
                    $('#in-auction-list').html(result.html_content);
                }
            });
        }
        
        function updateAuctionOrder(data) {
            // POST to server
            $.ajax({
                url: '/admin/ajax/_ajaxAuctionVehicles.php',
                type: 'POST',
                dataType: 'json',
                data: 'Mode=update-order&AuctionID=' + AuctionID + '&' + data
            }).done(function (result) {
                //handle the server response
                if (result.err) {
                    alert('Sorry - there was a problem: ' + result.err);
                }
            });
        }
    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>