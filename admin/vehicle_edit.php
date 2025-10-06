<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\Vehicle;
    use PeterBourneComms\CCA\AuctionRoom;
    use PeterBourneComms\CCA\Auction;
    use PeterBourneComms\CCA\AuctionVehicle;
    use PeterBourneComms\CCA\VehicleService;
    use PeterBourneComms\CCA\VehicleAppraisal;
    
    if (isset($_GET['id'])) {
        $id = clean_int($_GET['id']);
    }
    
    $VO = new Vehicle();
    
    if (!is_object($VO)) {
        die();
    }
    
    $id = clean_int($_GET['id'] ?? null);
    if ($id <= 0) {
        header("Location: ./vehicle_list.php");
        exit;
    }
    
    
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        //This is an edit - take all variable from the SESSION
    } else {
        
        $Vehicle = $VO->getItemById($id);
        //print_r($Vehicle);
        if (is_array($Vehicle) && count($Vehicle) > 0) {
            $_SESSION['PostedForm'] = $Vehicle;
            $_SESSION['PostedForm']['Customer'] = $Vehicle['CustomerInfo']['Company'];
            //Convert dates
            $_SESSION['PostedForm']['DateOfFirstReg'] = format_jquery_date($_SESSION['PostedForm']['DateOfFirstReg']);
            $_SESSION['PostedForm']['MOTExpires'] = format_jquery_date($_SESSION['PostedForm']['MOTExpires']);
        }
    }
    
    unset($_SESSION['error']);
    
    unset($_SESSION['error']);
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'vehicles';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Vehicles | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <form id="form1" name="form1" method="post" action="./vehicle_editExec.php" class="standard" data-abide novalidate>
            <div class='cca-panel'>
                <div class='grid-x grid-margin-x'>
                    <div class='medium-8 cell'>
                        <h1>Vehicle details</h1>
                        <p>Edit details as appropriate - and move to a different field to save the details.<br/>You don't need to press Save.
                        </p>
                    </div>
                    <div class='medium-4 cell text-right'>
                        <span class='reg-display'><?php echo $Vehicle['Reg']; ?></span>
                    </div>
                </div>

                <div data-abide-error id='WholeFormError' class="alert callout" style="display: none;">
                    <p>
                        <i class="fi-alert"></i> There are some errors in your form.https://www.usb-print.co.uk/usb-packagng/blu-ray-style-usb-case/
                    </p>
                </div>
    
                <div class='grid-x grid-margin-x'>
                    <div class='medium-8 cell'>
                        <div class='grid-x grid-margin-x'>
                            <div class='medium-6 cell'>
                                <label for="Reg">Registration:</label><input type="text" name="Reg" id="Reg" value="<?php echo check_output($_SESSION['PostedForm']['Reg'] ?? ''); ?>" placeholder="Registration" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Reg' class='field-update' required />
                                <label for="Make">Make:</label><input type="text" name="Make" id="Make" value="<?php echo check_output($_SESSION['PostedForm']['Make'] ?? ''); ?>" placeholder="Make" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Make' class='field-update' required />
                                <label for="Model">Model:</label><input type="text" name="Model" id="Model" value="<?php echo check_output($_SESSION['PostedForm']['Model'] ?? ''); ?>" placeholder="Model" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Model' class='field-update' required />
                            </div>
                            <div class='medium-6 cell'>
                                <label>Main image</label>
                                <?php
                                    //Retrieve first image
                                    $Images = $Vehicle['Images'];
                                    if (is_array($Images) && count($Images) > 0) {
                                        //Retrieve just the first image
                                        $VehicleImage = $Images[0];
                                        if ($VehicleImage['FullPath'] != '' && file_exists(FixOutput(DOCUMENT_ROOT . $VehicleImage['FullPath']))) {
                                            echo "<div class='vehicle-image-admin'><img src='" . FixOutput($VehicleImage['FullPath']) . "' title='" . FixOutput($VehicleImage['MediaFilename']) . "' alt='" . FixOutput($VehicleImage['MediaFilename']) . "' /></div>";
                                        }
                                    }
                                ?>
                            </div>
                        </div>
                        <label for='ShortDesc'>General description</label>
                        <textarea name='ShortDesc' id='ShortDesc' class='field-update' data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='ShortDesc'><?php echo check_output($_SESSION['PostedForm']['ShortDesc'] ?? ''); ?></textarea>
                        <div class='grid-x grid-margin-x'>
                            <div class='medium-6 cell'>
                                <label for="VehicleType">Type:</label><select name='VehicleType' id='VehicleType' data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='VehicleType' class='field-update' required >
                                    <option value='' <?php if (!$_SESSION['PostedForm']['VehicleType'] || $_SESSION['PostedForm']['VehicleType'] == '') {
                                        echo 'selected';
                                    } ?>>Please select...
                                    </option>
                                    <?php
                                        $Options = $VO->getVehicleTypeOptions();
                                        if (is_array($Options) && count($Options) > 0) {
                                            foreach ($Options as $ThisOption) {
                                                echo "<option value='".$ThisOption['Value']."'";
                                                if ($_SESSION['PostedForm']['VehicleType'] == $ThisOption['Value']) {
                                                    echo " selected='selected'";
                                                }
                                                echo ">".$ThisOption['Label']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="ManufacturerColour">Colour:</label><input type="text" name="ManufacturerColour" id="ManufacturerColour" value="<?php echo check_output($_SESSION['PostedForm']['ManufacturerColour'] ?? ''); ?>" placeholder="Colour" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='ManufacturerColour' class='field-update' required />
                                <label for="FinishType">Finish type:</label><input type="text" name="FinishType" id="FinishType" value="<?php echo check_output($_SESSION['PostedForm']['FinishType'] ?? ''); ?>" placeholder="Finish type eg: Metallic" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='FinishType' class='field-update'/>
                                <label for="TrimType">Trim type:</label><input type="text" name="TrimType" id="TrimType" value="<?php echo check_output($_SESSION['PostedForm']['TrimType'] ?? ''); ?>" placeholder="Trim type eg: Full leather" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='TrimType' class='field-update'/>
                                <label for="TrimColour">Trim colour:</label><input type="text" name="TrimColour" id="TrimColour" value="<?php echo check_output($_SESSION['PostedForm']['TrimColour'] ?? ''); ?>" placeholder="Trim colour" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='TrimColour' class='field-update'/>
                                <label for="DateOfFirstReg">Date of first reg:</label><input type="text" name="DateOfFirstReg" id="DateOfFirstReg" value="<?php echo check_output($_SESSION['PostedForm']['DateOfFirstReg'] ?? ''); ?>" placeholder="Date of first reg" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='DateOfFirstReg' class='field-update date-field' required />
                                <label for="NoOfDoors">Number of doors:</label><input type="number" name="NoOfDoors" id="NoOfDoors" value="<?php echo check_output($_SESSION['PostedForm']['NoOfDoors'] ?? ''); ?>" placeholder="Number of doors" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='NoOfDoors' class='field-update'/>
                                <label for="NoOfKeys">Number of keys:</label><input type="number" name="NoOfKeys" id="NoOfKeys" value="<?php echo check_output($_SESSION['PostedForm']['NoOfKeys'] ?? ''); ?>" placeholder="Number of keys" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='NoOfKeys' class='field-update'/>
                            </div>
                            <div class='medium-6 cell'>
                                <label for="Mileage">Mileage:</label><input type="number" name="Mileage" id="Mileage" value="<?php echo check_output($_SESSION['PostedForm']['Mileage'] ?? ''); ?>" placeholder="Mileage" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Mileage' class='field-update' required />
    
                                <label for="Transmission">Transmission:</label><select name='Transmission' id='Transmission' data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Transmission' class='field-update'>
                                    <option value='' <?php if (!$_SESSION['PostedForm']['Transmission'] || $_SESSION['PostedForm']['Transmission'] == '') {
                                        echo 'selected';
                                    } ?>>Please select...
                                    </option>
                                    <?php
                                        $Options = $VO->getTransmissionOptions();
                                        if (is_array($Options) && count($Options) > 0) {
                                            foreach ($Options as $ThisOption) {
                                                echo "<option value='".$ThisOption['Value']."'";
                                                if ($_SESSION['PostedForm']['Transmission'] == $ThisOption['Value']) {
                                                    echo " selected='selected'";
                                                }
                                                echo ">".$ThisOption['Label']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
    
                                <label for="Fuel">Fuel:</label><select name='Fuel' id='Fuel' data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Fuel' class='field-update'>
                                    <option value='' <?php if (!$_SESSION['PostedForm']['Fuel'] || $_SESSION['PostedForm']['Fuel'] == '') {
                                        echo 'selected';
                                    } ?>>Please select...
                                    </option>
                                    <?php
                                        $Options = $VO->getFuelOptions();
                                        if (is_array($Options) && count($Options) > 0) {
                                            foreach ($Options as $ThisOption) {
                                                echo "<option value='".$ThisOption['Value']."'";
                                                if ($_SESSION['PostedForm']['Fuel'] == $ThisOption['Value']) {
                                                    echo " selected='selected'";
                                                }
                                                echo ">".$ThisOption['Label']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
    
                                <label for="EngineSize">Engine size:</label><input type="number" name="EngineSize" id="EngineSize" value="<?php echo check_output($_SESSION['PostedForm']['EngineSize'] ?? ''); ?>" placeholder="Engine size" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='EngineSize' class='field-update'/>
    
                                <label for="WheelSize">Wheel size:</label>
                                <div class='input-group'>
                                    <input type="number" name="WheelSize" id="WheelSize" value="<?php echo check_output($_SESSION['PostedForm']['WheelSize'] ?? ''); ?>" placeholder="Wheel size" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='WheelSize' class='field-update input-group-field'/>
                                    <span class='input-group-label'>"</span>
                                </div>
    
    
                                <label for="AlloySpec">Alloy spec:</label><input type="text" name="AlloySpec" id="AlloySpec" value="<?php echo check_output($_SESSION['PostedForm']['AlloySpec'] ?? ''); ?>" placeholder="Alloy specification" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='AlloySpec' class='field-update'/>
                                <label for="NoOfOwners">Number of owners:</label><input type="number" name="NoOfOwners" id="NoOfOwners" value="<?php echo check_output($_SESSION['PostedForm']['NoOfOwners'] ?? ''); ?>" placeholder="Number of owners" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='NoOfOwners' class='field-update'/>
                            </div>
                        </div>
                    </div>
                    <div class='medium-4 cell'>
                        <div class='callout success'>
                            <h3>Status</h3>
                            <?php
                                if ($_SESSION['PostedForm']['VehicleStatus'] == '' || $_SESSION['PostedForm']['VehicleStatus'] === 'Waiting' || $_SESSION['PostedForm']['VehicleStatus'] === 'Not sold') {
                            ?>
                            <label for="VehicleStatus">Status:</label><select name='VehicleStatus' id='VehicleStatus' data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='VehicleStatus' class='field-update'>
                                <option value='' <?php if (!$_SESSION['PostedForm']['VehicleStatus'] || $_SESSION['PostedForm']['VehicleStatus'] == '') {
                                    echo 'selected';
                                } ?>>Please select...
                                </option>
                                <?php
                                    $Options = $VO->getStatusOptions();
                                    if (is_array($Options) && count($Options) > 0) {
                                        foreach ($Options as $ThisOption) {
                                            echo "<option value='".$ThisOption['Value']."'";
                                            if ($_SESSION['PostedForm']['VehicleStatus'] == $ThisOption['Value']) {
                                                echo " selected='selected'";
                                            }
                                            echo ">".$ThisOption['Label']."</option>";
                                        }
                                    }
                                ?>
                            </select>
                            <?php
                                } else {
                                    echo "<label>Status:</label><strong>".check_output($_SESSION['PostedForm']['VehicleStatus'] ?? '')."</strong>";
                                    //Look up auction for link
                                    $AVO = new AuctionVehicle();
                                    if (is_object($AVO)) {
                                        $AuctionVehicleInfo = $AVO->listAllItems($Vehicle['ID'], 'vehicle-id')[0]; //return first item only - should only be one active auction in theory!
                                        //print_r($AuctionVehicleInfo);
                                        echo "&nbsp;<a href='./auction_edit.php?id=".$AuctionVehicleInfo['Auction']['ID']."#panel2'>Open auction <i class='fi-link'></i></a>";
                                    }
                                }
                            ?>
                        </div>
                        <div class='callout'>
                            <h3>Customer details</h3>
                            <label for="Customer">Customer:</label><input type="text" name="Customer" id="Customer" value="<?php echo check_output($_SESSION['PostedForm']['Customer'] ?? ''); ?>" placeholder="Customer" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Customer' class='customer-search'/>
                            <div id='results-list'></div>
                            <div id='CustomerAddress'>
                                <p><?php
                                        if ($Vehicle['CustomerInfo']['Address1'] != '' ?? '') {
                                            echo $Vehicle['CustomerInfo']['Address1']."<br/>";
                                        }
                                        if ($Vehicle['CustomerInfo']['Address2'] != '' ?? '') {
                                            echo $Vehicle['CustomerInfo']['Address2']."<br/>";
                                        }
                                        if ($Vehicle['CustomerInfo']['Address3'] != '' ?? '') {
                                            echo $Vehicle['CustomerInfo']['Address3']."<br/>";
                                        }
                                        if ($Vehicle['CustomerInfo']['Town'] != '' ?? '') {
                                            echo $Vehicle['CustomerInfo']['Town']."<br/>";
                                        }
                                        if ($Vehicle['CustomerInfo']['County'] != '' ?? '') {
                                            echo $Vehicle['CustomerInfo']['County']."<br/>";
                                        }
                                        if ($Vehicle['CustomerInfo']['Postcode'] != '' ?? '') {
                                            echo $Vehicle['CustomerInfo']['Postcode']."<br/>";
                                        }
                                    ?>
                                </p>
                            </div>
                            <input type='hidden' name='CustomerID' id='CustomerID' value='<?php echo $Vehicle['CustomerInfo']['ID']; ?>' />
                            <input type='hidden' name='Company' id='Company' value='<?php echo $Vehicle['CustomerInfo']['Company']; ?>' />
                            <h3>Auction Rooms for this customer</h3>
                            <div id='AuctionRooms'>
                                <?php
                                    $ARO = new AuctionRoom();
                                    if (is_object($ARO)) {
                                        //Step through the Customers auction rooms - and retrieve logos etc.
                                        if (is_array($Vehicle['CustomerInfo']['AuctionRooms']) && count($Vehicle['CustomerInfo']['AuctionRooms']) > 0) {
                                            $count = 0;
                                            foreach ($Vehicle['CustomerInfo']['AuctionRooms'] as $Room) {
                                                $RoomInfo = $ARO->getItemById($Room['AuctionRoomID']);
                                                if (is_array($RoomInfo) && count($RoomInfo) > 0) {
                                                    if (isset($RoomInfo['ImgFilename']) && file_exists(DOCUMENT_ROOT.$RoomInfo['ImgPath'].$RoomInfo['ImgFilename'])) {
                                                        echo "<img src='".FixOutput($RoomInfo['ImgPath'].$RoomInfo['ImgFilename'])."' alt='".FixOutput($RoomInfo['Title'])."' style='height: 50px; width: auto; margin: 2px 6px;' />";
                                                    } else {
                                                        echo $RoomInfo['Title'];
                                                        $count++;
                                                        if ($count != count($Vehicle['CustomerInfo']['AuctionRooms'])) {
                                                            echo ", ";
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                ?>
                            </div>
                        </div>
                        <label>Override Auction default fees</label>
                        <div class="switch large">
                            <input class="switch-input field-update" id="OverrideAuctionFees" type="checkbox" name="OverrideAuctionFees" value='Y' data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='OverrideAuctionFees' <?php if ($_SESSION['PostedForm']['OverrideAuctionFees'] == 'Y') { echo " checked='checked'"; } ?> />
                            <label class="switch-paddle" for="OverrideAuctionFees">
                                <span class="show-for-sr">Override fees?</span>
                                <span class="switch-active" aria-hidden="true">Yes</span>
                                <span class="switch-inactive" aria-hidden="true">No</span>
                            </label>
                        </div>
                        
                        <div id='AuctionOverride'>
                            <ul class="tabs" data-tabs id="fees-tabs">
                                <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Seller fees</a></li>
                                <li class="tabs-title"><a data-tabs-target="panel2" href="#panel2">Buyer fees</a></li>
                            </ul>
                            <div class="tabs-content" data-tabs-content="fees-tabs">
                                <div class="tabs-panel is-active" id="panel1">
                                    <div class='callout alert' style='display:none;' id='SellerFeeError'>
                                        <p><strong>You cannot specify BOTH a seller fixed fee and auction %. Please only complete one section.</strong></p>
                                    </div>
                                    <h3>Seller</h3>
                                    <label for="Seller_Percent">% of auction value:</label>
                                    <div class='input-group'>
                                        <input type="number" name="Seller_Percent" id="Seller_Percent" value="<?php echo check_output($_SESSION['PostedForm']['Seller_Percent']*100 ?? ''); ?>" step='1' placeholder="Percentage of auction value" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Seller_Percent' class='field-update fee-update input-group-field' />
                                        <span class='input-group-label'>%</span>
                                    </div>
                                    <label for="Seller_UptoMax">Up to a maximum of:</label>
                                    <div class='input-group'>
                                        <span class='input-group-label'>£</span>
                                        <input type="number" name="Seller_UptoMax" id="Seller_UptoMax" value="<?php echo check_output($_SESSION['PostedForm']['Seller_UptoMax'] ?? ''); ?>" placeholder="(optional) Up to a maximum of" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Seller_UptoMax' class='field-update fee-update input-group-field'/>
                                    </div>
                                    <h3>or</h3>
                                    <label for="Seller_Fixed">Fixed fee:</label>
                                    <div class='input-group'>
                                        <span class='input-group-label'>£</span>
                                        <input type="number" name="Seller_Fixed" id="Seller_Fixed" value="<?php echo check_output($_SESSION['PostedForm']['Seller_Fixed'] ?? ''); ?>" placeholder="Fixed fee" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Seller_Fixed' class='field-update fee-update input-group-field'/>
                                    </div>
                                </div>
                                <div class="tabs-panel" id="panel2">
                                    <div class='callout alert' style='display:none;' id='BuyerFeeError'>
                                        <p><strong>You cannot specify BOTH a buyer fixed fee and auction %. Please only complete one section.</strong></p>
                                    </div>
                                    <h3>Buyer</h3>
                                    <label for="Buyer_Percent">% of auction value:</label>
                                    <div class='input-group'>
                                        <input type="number" name="Buyer_Percent" id="Buyer_Percent" value="<?php echo check_output($_SESSION['PostedForm']['Buyer_Percent']*100 ?? ''); ?>" placeholder="Percentage of auction value" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Buyer_Percent' class='field-update fee-update input-group-field' />
                                        <span class='input-group-label'>%</span>
                                    </div>
                                    <label for="Buyer_UptoMax">Up to a maximum of:</label>
                                    <div class='input-group'>
                                        <span class='input-group-label'>£</span>
                                        <input type="number" name="Buyer_UptoMax" id="Buyer_UptoMax" value="<?php echo check_output($_SESSION['PostedForm']['Buyer_UptoMax'] ?? ''); ?>" placeholder="(optional) Up to a maximum of" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Buyer_UptoMax' class='field-update fee-update input-group-field'/>
                                    </div>
                                    <h3>or</h3>
                                    <label for="Buyer_Fixed">Fixed fee:</label>
                                    <div class='input-group'>
                                        <span class='input-group-label'>£</span>
                                        <input type="number" name="Buyer_Fixed" id="Buyer_Fixed" value="<?php echo check_output($_SESSION['PostedForm']['Buyer_Fixed'] ?? ''); ?>" placeholder="Fixed fee" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='Buyer_Fixed' class='field-update fee-update input-group-field'/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class='cca-panel'>
                <h2>Features</h2>
                <p>List of features. You can add more and re-order them below by dragging and dropping.</p>
                <div class='callout'>
                    <h3>Add new feature</h3>
                    <input type='text' name='NewFeature' id='NewFeature' placeholder='Enter new feature and hit enter' value='' data-vehicle-id='<?php echo $Vehicle['ID']; ?>' />
                </div>
                <div class='feature-list' id='feature-list'></div>
            </div>
            
            <div class='cca-panel images'>
                <h2>Images</h2>
                <p>Drag more images in - or rearrange existing images</p>
                <div class='library-container' id='vehicle-images'></div>
            </div>
    
            <div class='grid-x grid-margin-x'>
                <div class='medium-8 cell'>
                    <div class='cca-panel'>
                        <h2>Vehicle Appraisal</h2>
                        <div class='vehicle-appraisal-parent'>
                            <div class='vehicle-appraisal-container'>
                                <?php
                                    //Load any items for the appraisal
                                    $VAO = new VehicleAppraisal();
                                    if (is_object($VAO)) {
                                        $AppraisalItems = $VAO->getAppraisalItems();
                                        $Defects = $VAO->listAllItems($_SESSION['PostedForm']['ID'], 'vehicle-id');
                                        if (is_array($Defects) && count($Defects) > 0) {
                                            foreach ($Defects as $Item) {
                                                echo "<div class='appraisal-icon-placed appraisal-icon' data-code='" . $Item['Code'] . "' data-id='" . $Item['ID'] . "' style='top:" . $Item['LocY'] . "px; left:" . $Item['LocX'] . "px;position:absolute;'><img src='" . $AppraisalItems[$Item['Code']]['Icon'] . "' alt='" . $Item['Code'] . "' /></div>"; //data-type='" . $Item['Type'] . "'
                                            }
                                        }
                                    }
                                ?>
                            </div>

                            <div class='appraisal-key'>
                                <h3>Instructions</h3>
                                <p>Drags and drop icons from the panel below to highlight damage</p>
                                <?php
                                    foreach ($AppraisalItems as $Item) {
                                        echo "<div><div class='appraisal-icon' ";
                                        echo "data-code='" . $Item['Code'] . "' ";
                                        echo "data-type='" . $Item['Type'] . "' ";
                                        //echo "data-icon='".urlencode($Item['Icon'])."' ";
                                        echo "><img src='" . $Item['Icon'] . "' alt='" . $Item['Type'] . "'/></div>";
                                        echo $Item['Type'] . "</div>";
                                    }
                                ?>
                                <p class='help-text space-above'>Drag elements from the vehicle back to the <strong>panel
                                        above</strong> to remove them</p>
                            </div>
                        </div>
                        <div style='clear:both;'><!----></div>
                    </div>
                </div>
                <div class='medium-4 cell'>
                    <div class='cca-panel'>
                        <label for="MOTExpires">MOT expires:</label><input type="text" name="MOTExpires" id="MOTExpires" value="<?php echo check_output($_SESSION['PostedForm']['MOTExpires'] ?? ''); ?>" placeholder="MOT expires" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='MOTExpires' class='field-update date-field' />
                        <label for="ServiceHistory">Service history:</label><select name='ServiceHistory' id='ServiceHistory' data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='ServiceHistory' class='field-update'>
                            <option value='' <?php if (!$_SESSION['PostedForm']['ServiceHistory'] || $_SESSION['PostedForm']['ServiceHistory'] == '') {
                                echo 'selected';
                            } ?>>Please select...
                            </option>
                            <?php
                                $Options = $VO->getServiceHistoryOptions();
                                if (is_array($Options) && count($Options) > 0) {
                                    foreach ($Options as $ThisOption) {
                                        echo "<option value='".$ThisOption['Value']."'";
                                        if ($_SESSION['PostedForm']['ServiceHistory'] == $ThisOption['Value']) {
                                            echo " selected='selected'";
                                        }
                                        echo ">".$ThisOption['Label']."</option>";
                                    }
                                }
                            ?>
                        </select>

                        <label>V5 present?</label>
                        <div class="switch">
                            <input class="switch-input field-update" id="V5Present" type="checkbox" name="V5Present" value='Y' data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='V5Present' <?php if ($_SESSION['PostedForm']['V5Present'] == 'Y') { echo " checked='checked'"; } ?> />
                            <label class="switch-paddle" for="V5Present">
                                <span class="show-for-sr">V5 present?</span>
                                <span class="switch-active" aria-hidden="true">Yes</span>
                                <span class="switch-inactive" aria-hidden="true">No</span>
                            </label>
                        </div>
                        <label>Tyres</label>
                        <div class='input-group'>
                            <span class='input-group-label'>Front OS</span><input type="number" name="TyreFOS" id="TyreFOS" value="<?php echo check_output($_SESSION['PostedForm']['TyreFOS'] ?? ''); ?>" placeholder="Front OS" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='TyreFOS' class='field-update input-group-field'/>
                            <span class='input-group-label'>mm</span>
                        </div>
                        <div class='input-group'>
                            <span class='input-group-label'>Front NS</span><input type="number" name="TyreFNS" id="TyreFNS" value="<?php echo check_output($_SESSION['PostedForm']['TyreFNS'] ?? ''); ?>" placeholder="Front NS" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='TyreFNS' class='field-update input-group-field'/>
                            <span class='input-group-label'>mm</span>
                        </div>
                        <div class='input-group'>
                            <span class='input-group-label'>Rear OS</span><input type="number" name="TyreROS" id="TyreROS" value="<?php echo check_output($_SESSION['PostedForm']['TyreROS'] ?? ''); ?>" placeholder="Rear OS" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='TyreROS' class='field-update input-group-field'/>
                            <span class='input-group-label'>mm</span>
                        </div>
                        <div class='input-group'>
                            <span class='input-group-label'>Rear NS</span><input type="number" name="TyreRNS" id="TyreRNS" value="<?php echo check_output($_SESSION['PostedForm']['TyreRNS'] ?? ''); ?>" placeholder="Rear NS" data-vehicle-id='<?php echo $Vehicle['ID']; ?>' data-field='TyreRNS' class='field-update input-group-field'/>
                            <span class='input-group-label'>mm</span>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class='cca-panel'>
                <h2>Service history</h2>
                <div class='service-history' id='service-history'></div>
                <div id='ServiceHistoryEdit'>
                    <div class='grid-x grid-margin-x'>
                        <div class='medium-2 cell'><label>Date</label><input type='text' class='service-field date-field' id='ServiceDate' name='ServiceDate' data-vehicle-id='<?php echo $id; ?>' placeholder='Date' /></div>
                        <div class='medium-2 cell'><label>Mileage</label><input type='number' class='service-field' id='ServiceMileage' name='ServiceMileage' data-vehicle-id='<?php echo $id; ?>' placeholder='Mileage' /></div>
                        <div class='medium-2 cell'>
                            <label>Type</label><select class='service-field' id='ServiceType' name='ServiceType' data-vehicle-id='<?php echo $id; ?>'>
                                <option value=''>Please select...</option>
                                <?php
                                    $VSO = new VehicleService();
                                    if (is_object($VSO)) {
                                        $Options = $VSO->getTypeOptions();
                                        if (is_array($Options) && count($Options) > 0) {
                                            foreach ($Options as $ThisOption) {
                                                echo "<option value='".$ThisOption['Value']."'>".$ThisOption['Label']."</option>";
                                            }
                                        }
                                    }
                                ?>
                            </select></div>
                        <div class='medium-4 cell'><label>Comments</label><input type='text' class='service-field' id='ServiceComments' name='ServiceComments' data-vehicle-id='<?php echo $id; ?>' placeholder='Comments' /></div>
                        <div class='medium-2 cell'><label>&nbsp;</label><button class='button' id='NewService' name='NewService' data-vehicle-id='<?php echo $id; ?>'>Add</button></div>
                    </div>
                </div>
            </div>
        </form>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./vehicle_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> Vehicles</a>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script>
        var form_errors = [];

        var VehicleID = '<?php echo $Vehicle['ID']; ?>';
        
        var OverrideAuctionFees = '<?php echo $Vehicle['OverrideAuctionFees']; ?>';

        $(document)
            // build form errors up
            .on("invalid.zf.abide", function (ev, elem) {
                if ($(elem)[0].hasAttribute('placeholder') && $(elem).attr('placeholder') != '') {
                    var errorfield = $(elem).attr('placeholder');
                } else {
                    var errorfield = $(elem).attr('name');
                }
                //console.log('errorfield = '+errorfield);
                //Add to error list - if not already there
                if (!form_errors.includes(errorfield)) {
                    form_errors.push(errorfield);
                }

            }).on("valid.zf.abide", function (ev, elem) {
            //Remove from list if field is OK
            if ($(elem)[0].hasAttribute('placeholder') && $(elem).attr('placeholder') != '') {
                var errorfield = $(elem).attr('placeholder');
            } else {
                var errorfield = $(elem).attr('name');
            }
            //Is this field in the errors list - if so remove it
            var present = form_errors.indexOf(errorfield);
            if (present >= 0) {
                //Remove from the array
                form_errors.splice(present, 1);
            }
        }).on("forminvalid.zf.abide", function (ev, frm) {
            //SUBMIT FINAL CHECK
            //if errors in form_errors list - show and do not submit - otherwise submit
            if (form_errors.length > 0 || reference_uploaded === false || los_uploaded === false) {
                //Add errors into error message
                var html = "<p><i class='fi-alert'></i> There are some errors in your form. <strong>Please complete the following fields</strong></p><ul>";
                form_errors.forEach(function (item, index, array) {
                    html += "<li>" + item + "</li>";
                });
                /*if (reference_uploaded === false) {
                    html += "<li><strong>Reference document(s) need uploading</strong></li>";
                }*/
                html += "</ul>";
                /*if (los_uploaded === false) {
                    html += "<p><strong>Letter of support needs uploading</strong></p>";
                }*/
                $('#WholeFormError').html(html);
                $( "html" ).scrollTop( 0 );
            } else {
                //Submit!
            }
        });
        
        $(document).ready(function () {

            //init dates
            $('.date-field').datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'dd/mm/yy'
            }).attr('autocomplete', 'off');
            
            //Hide AuctionOverride
            if (OverrideAuctionFees != 'Y') {
                $('#AuctionOverride').hide();
            } else {
                $('#AuctionOverride').show();
            }
            
            //Load initial features and data
            listFeatures();
            listService();

            var cl = new createContentLibrary({
                libraryContainer: 'vehicle-images',
                maxDimension: 1000,
                contentParentTable: 'Vehicles',
                contentID: '<?php echo $id; ?>'
            });
            
            
            /**
             * Field saving on update and Abide code
             */
            //On leave a field - reninit abide
            $('.field-update').on('blur', function (e) {
                $('#form1').foundation('validateInput', $(this));
            });

            //Check for enter and don't submit - on ALL fields
            $('form input').keydown(function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
            $('form select').keydown(function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });

            //AJAX for update on each field as it changes
            $('body').on('change', '.field-update', function () {
                var field = $(this).data('field');
                var value = $(this).val();

                //Determine if this is a switch - if so we need to check the state of the switch (checkbox). This will change the value
                if ($(this).hasClass('switch-input')) {
                    if (!$(this).is(':checked')) {
                        value = null;
                    }
                }

                updateField(field, value);
            });


            /**
             * Service history
             */
            
            //GENERAL TYPING
            $('.service-field').keydown(function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                }
            });
            
            //INSERT
            $('#NewService').on('click', function (e) {
                e.preventDefault();
                
                $.ajax({
                    type: "POST",
                    url: './ajax/_ajaxVehicleService.php',
                    data: {
                        Date: $('#ServiceDate').val(),
                        Mileage: $('#ServiceMileage').val(),
                        Type: $('#ServiceType').val(),
                        Comments: $('#ServiceComments').val(),
                        VehicleID: VehicleID,
                        Mode: 'insert'
                    },
                    dataType: 'json'
                }).done(function (result) {
                    if (result.err) {
                        //error
                        //console.log(result.err);
                        alert('There was a problem: ' + result.err);
                    } else {
                        //Add the returned feature to the bottom of the list
                        $('#service-history').append(result.html_content);
                        //Clear the NewFeature input
                        $('.service-field').val('');
                    }
                });
            });

            //DELETE
            $('#service-history').on('click', '.service-delete', function (e) {
                e.preventDefault();
                var serviceid = $(this).data('service-id');
                $.ajax({
                    url: '/admin/ajax/_ajaxVehicleService.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        Mode: 'delete',
                        ID: serviceid
                    }
                }).done(function (result) {
                    // Handle server response
                    if (result.err) {
                        //error
                        alert('Sorry - there was a problem: ' + result.err);
                    } else {
                        //succcess - delete
                        $('#Service_' + serviceid).slideUp(300, function () {
                            $('#Service_' + serviceid).remove();
                        });
                    }
                });
            });
            
            

            /**
             * Features
             */
            
            //INSERT
            $('#NewFeature').keydown(function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    //Save this feature
                    var feature = $(this).val();
                    if (feature != '') {
                        $.ajax({
                            type: "POST",
                            url: './ajax/_ajaxVehicleFeatures.php',
                            data: {
                                Feature: feature,
                                VehicleID: VehicleID,
                                Mode: 'insert'
                            },
                            dataType: 'json'
                        }).done(function (result) {
                            if (result.err) {
                                //error
                                //console.log(result.err);
                                alert('There was a problem: ' + result.err);
                            } else {
                                //Add the returned feature to the bottom of the list
                                $('#feature-list').append(result.html_content);
                                //Clear the NewFeature input
                                $('#NewFeature').val('');
                            }
                        });
                    }
                }
            });

            //DELETE
            $('#feature-list').on('click', '.feature-delete', function (e) {
                e.preventDefault();
                var featureid = $(this).data('feature-id');
                $.ajax({
                    url: '/admin/ajax/_ajaxVehicleFeatures.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        Mode: 'delete',
                        ID: featureid
                    }
                }).done(function (result) {
                    // Handle server response
                    if (result.err) {
                        //error
                        alert('Sorry - there was a problem: ' + result.err);
                    } else {
                        //succcess - delete
                        $('#Feature_' + featureid).slideUp(300, function () {
                            $('#Feature_' + featureid).remove();
                        });
                    }
                });
            });

            //RE-ORDER Features
            $('#feature-list').sortable({
                placeholder: 'feature-sortable-placeholder',
                update: function (event, ui) {

                    var data = $('#feature-list').sortable('serialize');
                    // POST to server
                    $.ajax({
                        url: '/admin/ajax/_ajaxVehicleFeatures.php',
                        type: 'POST',
                        dataType: 'json',
                        data: 'Mode=update-order&VehicleID=' + VehicleID + '&' + data
                    }).done(function (result) {
                        //handle the server response
                        if (result.err) {
                            alert('Sorry - there was a problem: ' + result.err);
                        }
                    });
                }
            });
            

            /**
             * Auction fee override
             */
            $('#OverrideAuctionFees').on('change', function(e) {
                e.preventDefault();
                if (!$(this).is(':checked')) {
                    $('#AuctionOverride').hide();
                } else {
                    $('#AuctionOverride').show();
                }
            });
            
            //Now need some logic so that Seller/Buyer % OR Fixed fee isn't completed
            $('body').on('change', '.fee-update', function(e) {
                e.preventDefault();
                //Seller
                if ($('#Seller_Fixed').val() > 0 && $('#Seller_Percent').val() > 0) {
                    //form_errors.push('You cannot specify BOTH a Seller Fixed fee AND Seller auction %');
                    $('#SellerFeeError').show();
                } else {
                    $('#SellerFeeError').hide();
                }
                //Buyer
                if ($('#Buyer_Fixed').val() > 0 && $('#Buyer_Percent').val() > 0) {
                    //form_errors.push('You cannot specify BOTH a Seller Fixed fee AND Seller auction %');
                    $('#BuyerFeeError').show();
                } else {
                    $('#BuyerFeeError').hide();
                }
            });



            /**
             * Customer lookup and storing
             */
            //Timeout and ajax lookup functionality
            var delayTimer;

            //Search delay
            $('.customer-search').on('keyup', function () {
                doSearch(encodeURIComponent($(this).val()));
            });

            /**
             * Search function
             * @param text
             * @param mode
             */
            function doSearch(text) {
                clearTimeout(delayTimer);
                delayTimer = setTimeout(function () {
                    //Search
                    $.ajax({
                        type: "POST",
                        url: './ajax/_ajaxVehicleCustomerList.php',
                        data: {
                            q: text,
                            m: 'name-email'
                        },
                        dataType: 'json'
                    }).done(function (result) {
                        if (result.err) {
                            //error
                            //console.log(result.err);
                            alert('There was a problem: ' + result.err);
                        } else {
                            //Display the returned data
                            $('#results-list').html(result.detail);
                        }
                    });
                }, 500); // Will do the ajax stuff after 500 ms, or 0.5s
            }
            

            /**
             * Select Customer functionality from the ajax
             */
            $('body').on('click', '#SelectCustomer', function (e) {
                e.preventDefault();
                
                //Do a full customer lookup
                var customerid = $(this).data('customer-id');
                
                if (customerid > 0) {
                    $.ajax({
                        type: "POST",
                        url: './ajax/_ajaxCustomerGetDetail.php',
                        data: {
                            id: customerid
                        },
                        dataType: 'json'
                    }).done(function (result) {
                        if (result.err) {
                            //error
                            //console.log(result.err);
                            alert('There was a problem: ' + result.err);
                        } else {
                            //Assign to fields
                            var customer = result.json;
                            $('#CustomerID').val(customer.ID);
                            $('#Company').val(customer.Company);
                            $('#Customer').val(customer.Company);
                            
                            //Save the customer on the vehicle record
                            updateField('CustomerID',customer.ID);

                            var newaddress = "<p>";
                            if (customer.Address1 != '') { newaddress += customer.Address1+"<br/>"; }
                            if (customer.Address2 != '') { newaddress += customer.Address2+"<br/>"; }
                            if (customer.Address3 != '') { newaddress += customer.Address3+"<br/>"; }
                            if (customer.Town != '') { newaddress += customer.Town+"<br/>"; }
                            if (customer.County != '') { newaddress += customer.County+"<br/>"; }
                            if (customer.Postcode != '') { newaddress += customer.Postcode+"<br/>"; }
                            
                            $('#CustomerAddress').html(newaddress);

                            if (result.auctionrooms != '') {
                                $('#AuctionRooms').html(result.auctionrooms);
                            }
                        }
                    });
                }

                //Hide results from earlier lookup
                $('#results-list').html('');
            });

            
            
            

            /**
             *   VEHICLE APPRAISAL STUFF
             */
            
            //Array of types
            var appraisalTypes = {};
            <?php
                foreach ($AppraisalItems as $Item) {
                    echo "appraisalTypes['" . $Item['Code'] . "'] = { code:'" . $Item['Code'] . "', type:'" . $Item['Type'] . "', icon:'" . $Item['Icon'] . "'};\n";
                }
            ?>

            //Make draggable
            $('.appraisal-icon').draggable({
                cursor: 'grabbing',
                revert: 'invalid',
                helper: 'clone'
            });
            //Make any already placed draggable
            $('.appraisal-icon-placed').draggable({
                cursor: 'grabbing',
                revert: 'invalid',
                helper: 'original'
            });

            //Respond to a drop
            $('.vehicle-appraisal-container').droppable({
                accept: '.appraisal-icon',
                //hoverClass: "job-ready-to-receive",
                drop: function (event, ui) {
                    var $this = $(this);
                    var itemtype = ui.draggable.data('type');
                    var itemcode = ui.draggable.data('code');

                    //1. Store locations in the vehicle appraisal via AJAX
                    var coords = ui.position;

                    //Set up data object
                    var data = {
                        Title: itemtype,
                        Code: itemcode,
                        VehicleID: VehicleID,
                        LocX: coords.left,
                        LocY: coords.top
                    };

                    //Move or new?
                    if (ui.draggable.data('id') != '') {
                        //Move / edit
                        var itemid = ui.draggable.data('id');
                        data['ID'] = itemid;
                    } else {
                        //New
                        var itemid = 0;
                    }

                    //Add to database
                    $.ajax({
                        url: './ajax/_ajaxVehicleAppraisalEdit.php',
                        method: 'POST',
                        dataType: 'json',
                        data: data
                    }).done(function (result) {
                        // Handle server response
                        if (result.err) {
                            //error
                            alert('Sorry - there was a problem: ' + result.err);
                        } else {
                            //Next step depends on whether this was an edit or not
                            if (result.mode == 'edit') {
                                //NOTHING NEED BE DONE!
                            } else {
                                //New - store and create
                                //Store returned record ID in var itemid
                                var itemid = result.id;

                                //2. Drop version of icon on the car
                                //Look up icon from js array/obj
                                var itemicon = appraisalTypes[itemcode]['icon'];
                                //Create new instance - and make droppable
                                var newicon = '<div class=\'appraisal-icon appraisal-icon-placed\' data-code=\'' + itemcode + '\' data-type=\'' + itemtype + '\' data-id=\'' + itemid + '\' style=\'top:' + Math.floor(coords.top) + 'px; left:' + Math.floor(coords.left) + 'px;position:absolute;\'><img src=\'' + itemicon + '\' alt=\'' + itemtype + '\'/></div>';

                                $($this).append(newicon);

                                //3. Allow it to be removed
                                $('.appraisal-icon-placed').draggable({
                                    cursor: 'grabbing',
                                    revert: 'invalid',
                                    helper: 'original'
                                });
                            }
                        }
                    });
                }
            });

            //Respond to a drop - to REMOVE an appraisal item
            $('.appraisal-key').droppable({
                accept: '.appraisal-icon-placed',
                //hoverClass: "job-ready-to-receive",
                drop: function (event, ui) {
                    var itemid = ui.draggable.data('id');

                    //Remove from database via AJAX
                    $.ajax({
                        url: './ajax/_ajaxVehicleAppraisalDelete.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            VehicleID: VehicleID,
                            DefectID: itemid
                        }
                    }).done(function (result) {
                        // Handle server response
                        if (result.err) {
                            //error
                            alert('Sorry - there was a problem: ' + result.err);
                        } else {
                            //Remove item from vehicle
                            $(ui.draggable).remove();
                        }
                    });
                }
            });
            
            
        });

        
        
        
        
        function updateField(field, value) {
            $.ajax({
                url: "/admin/ajax/_ajaxVehicleFieldUpdate.php",
                method: "POST",
                data: {
                    Field: field,
                    Value: value,
                    VehicleID: VehicleID
                },
                dataType: "json"
            }).done(function (result) {
                if (result.err) {
                    //error
                    //console.log(result.err);
                    alert('There was a problem saving your change: ' + result.err);
                } else {
                    //Also - if this is a multiline item - create the line entry - and remove button
                    /*if (result.multiline == 'true') {
                        var html = "";
                        html += "<div class='grid-x response-row' id='ResponseRow" + result.newid + "'><div class='medium-8 cell'><input type='text' value='" + result.value + "' readonly /></div><div class='medium-4 cell'><div class='input-group'><span class='input-group-label'>€</span><input type='number' step='0.01' value='" + result.amount + "' readonly /><div class='input-group-button'><button name='RemoveLine' class='button inline-button remove-line' data-question-id='" + result.questionid + "' data-response-row='" + result.newid + "'>Delete item</button></div></div></div></div>";

                        $('#RowItems').append(html);
                        //Redo the totals
                        var current_total = parseFloat($('.running-total').html());
                        if (isNaN(current_total)) { current_total = 0; }
                        new_total = parseFloat(current_total) + parseFloat(result.amount);
                        $('.running-total').html(new_total.toFixed(2));
                        //Hidden field also
                        $('#Financial_Cost').val(new_total.toFixed(2));

                        //Now empty the input fields
                        //Now empty the input fields
                        $('.multiline-field-update').val('');
                    }*/
                }
            });
        }
        
        function listFeatures() {
            $.ajax({
                type: "POST",
                url: '/admin/ajax/_ajaxVehicleFeatures.php',
                data: {
                    VehicleID: VehicleID,
                    Mode: 'list'
                },
                dataType: 'json'
            }).done(function (result) {
                if (result.err) {
                    //error
                    //console.log(result.err);
                    alert('There was a problem: ' + result.err);
                } else {
                    //Add the returned feature to the bottom of the list
                    $('#feature-list').html(result.html_content);
                }
            });
        }

        function listService() {
            $.ajax({
                type: "POST",
                url: '/admin/ajax/_ajaxVehicleService.php',
                data: {
                    VehicleID: VehicleID,
                    Mode: 'list'
                },
                dataType: 'json'
            }).done(function (result) {
                if (result.err) {
                    //error
                    //console.log(result.err);
                    alert('There was a problem: ' + result.err);
                } else {
                    //Add the returned feature to the bottom of the list
                    $('#service-history').html(result.html_content);
                }
            });
        }
    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>