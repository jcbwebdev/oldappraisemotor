<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\Customer;
    use PeterBourneComms\CCA\User;
    use PeterBourneComms\CCA\AuctionRoom;
    use PeterBourneComms\CCA\Note;
    
    $state = $_GET['state'] ?? null;
    
    if (isset($_GET['id'])) {
        $id = clean_int($_GET['id']);
    }
    
    $UO = new User();
    $CustO = new Customer();
    $ARO = new AuctionRoom();
    $NO = new Note();
    
    if (!is_object($UO) || !is_object($CustO) || !is_object($ARO) || !is_object($NO)) {
        die();
    }
    
    $id = clean_int($_GET['id'] ?? null);
    if ($id <= 0) {
        header("Location: ./customer_awaiting_approval_list.php");
        exit;
    }
    
    
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        //This is an edit - take all variable from the SESSION
    } elseif ($state == 'edit') {
        
        $Customer = $CustO->getItemById($id);
        if (is_array($Customer) && count($Customer) > 0) {
            $_SESSION['PostedForm'] = $Customer;
            $_SESSION['PostedForm']['OldImgFilename'] = $Customer['ImgFilename'] ?? '';
            if (isset($Customer['Users']) && is_array($Customer['Users'])) {
                $_SESSION['PostedForm']['Title'] = $Customer['Users'][0]['Title'];
                $_SESSION['PostedForm']['Firstname'] = $Customer['Users'][0]['Firstname'];
                $_SESSION['PostedForm']['Surname'] = $Customer['Users'][0]['Surname'];
                $_SESSION['PostedForm']['Mobile'] = $Customer['Users'][0]['Mobile'];
                $_SESSION['PostedForm']['Email'] = $Customer['Users'][0]['Email'];
                $_SESSION['PostedForm']['UserID'] = $Customer['Users'][0]['ID'];
            }
        }
    } else {
        header('Location:customer_awaiting_approval_list.php');
        exit;
    }
    
    unset($_SESSION['error']);
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'approval';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>New Customers | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Customer approval</h1>
            <p>Edit the customer details as required. Any clashes with existing company records will be highlighted.</p>
            <div class='grid-x grid-margin-x'>
                <div class='medium-8 cell'>
                    <form action="./customer_approvalExec.php" name="login" enctype="multipart/form-data" method="post" class="standard">
                        <div class='grid-x grid-margin-x'>
                            <div class='medium-6 cell'>
                                <h2>Your details</h2>
                                <?php if (isset($_SESSION['titleerror'])) {
                                    echo $_SESSION['titleerror'];
                                    unset($_SESSION['titleerror']);
                                } ?>
                                <p>
                                    <label for="Title">Title:</label><select name='Title' id='Title'>
                                        <?php
                                            echo "<option value=''";
                                            if ($_SESSION['PostedForm']['Title'] ?? '' == '') {
                                                echo " selected ='selected'";
                                            }
                                            echo ">Please select...</option>";
                                            $TitleOptions = $UO->getTitleOptions();
                                            if (is_array($TitleOptions)) {
                                                foreach ($TitleOptions as $Option) {
                                                    echo "<option value='".$Option['Value']."'";
                                                    if ($_SESSION['PostedForm']['Title'] == $Option['Value']) {
                                                        echo " selected ='selected'";
                                                    }
                                                    echo ">".$Option['Label']."</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </p>
                                <?php if (isset($_SESSION['firstnameerror'])) {
                                    echo $_SESSION['firstnameerror'];
                                    unset($_SESSION['firstnameerror']);
                                } ?>
                                <p>
                                    <label for="Firstname">Firstname:</label><input type="text" name="Firstname" id="Firstname" value="<?php echo check_output($_SESSION['PostedForm']['Firstname'] ?? ''); ?>" placeholder="Firstname"/>
                                </p>
                                <?php if (isset($_SESSION['surnameerror'])) {
                                    echo $_SESSION['surnameerror'];
                                    unset($_SESSION['surnameerror']);
                                } ?>
                                <p>
                                    <label for="Surname">Surname:</label><input type="text" name="Surname" id="Surname" value="<?php echo check_output($_SESSION['PostedForm']['Surname'] ?? ''); ?>" placeholder="Surname"/>
                                </p>
                                <?php if (isset($_SESSION['mobileerror'])) {
                                    echo $_SESSION['mobileerror'];
                                    unset($_SESSION['mobileerror']);
                                } ?>
                                <p>
                                    <label for="Mobile">Mobile:</label><input type="text" name="Mobile" id="Mobile" value="<?php echo check_output($_SESSION['PostedForm']['Mobile'] ?? ''); ?>" placeholder="Mobile"/>
                                </p>
                                <?php if (isset($_SESSION['emailerror'])) {
                                    echo $_SESSION['emailerror'];
                                    unset($_SESSION['emailerror']);
                                } ?>
                                <p>
                                    <label for="Email">Email address:</label><input type="email" name="Email" id="Email" value="<?php echo check_output($_SESSION['PostedForm']['Email'] ?? ''); ?>" placeholder="Email address"/>
                                </p>
                            </div>
                            <div class='medium-6 cell'>
                                <h2>Company information</h2>
                                <?php if (isset($_SESSION['companyerror'])) {
                                    echo $_SESSION['companyerror'];
                                    unset($_SESSION['companyerror']);
                                } ?>
                                <p>
                                    <label for="Company">Company:</label><input type="text" name="Company" id="Company" value="<?php echo check_output($_SESSION['PostedForm']['Company'] ?? ''); ?>" placeholder="Company"/>
                                </p>
                                <?php if (isset($_SESSION['addresserror'])) {
                                    echo $_SESSION['addresserror'];
                                    unset($_SESSION['addresserror']);
                                } ?>
                                <p>
                                    <label for="Address1">Address:</label><input type="text" name="Address1" id="Address1" value="<?php echo check_output($_SESSION['PostedForm']['Address1'] ?? ''); ?>" placeholder="Street"/>
                                    <input type="text" name="Address2" id="Address2" value="<?php echo check_output($_SESSION['PostedForm']['Address2'] ?? ''); ?>" placeholder=""/>
                                    <input type="text" name="Address3" id="Address3" value="<?php echo check_output($_SESSION['PostedForm']['Address3'] ?? ''); ?>" placeholder=""/></p>
                                <p><label for="Town">Town:</label>
                                    <input type="text" name="Town" id="Town" value="<?php echo check_output($_SESSION['PostedForm']['Town'] ?? ''); ?>" placeholder="Town"/></p>
                                <p><label for="County">County:</label>
                                    <input type="text" name="County" id="County" value="<?php echo check_output($_SESSION['PostedForm']['County'] ?? ''); ?>" placeholder="County"/></p>
                                <p><label for="Postcode">Postcode:</label>
                                    <input type="text" name="Postcode" id="Postcode" value="<?php echo check_output($_SESSION['PostedForm']['Postcode'] ?? ''); ?>" placeholder="Postcode"/>
                                </p>
                                <?php if (isset($_SESSION['telerror'])) {
                                    echo $_SESSION['telerror'];
                                    unset($_SESSION['telerror']);
                                } ?>
                                <p>
                                    <label for="Tel">Accounts telephone:</label><input type="text" name="Tel" id="Tel" value="<?php echo check_output($_SESSION['PostedForm']['Tel'] ?? ''); ?>" placeholder="Accounts landline"/>
                                </p>
                                <?php if (isset($_SESSION['companyemailerror'])) {
                                    echo $_SESSION['companyemailerror'];
                                    unset($_SESSION['companyemailerror']);
                                } ?>
                                <p>
                                    <label for="CompanyEmail">Accounts email:</label><input type="email" name="CompanyEmail" id="CompanyEmail" value="<?php echo check_output($_SESSION['PostedForm']['CompanyEmail'] ?? ''); ?>" placeholder="Accounts email"/>
                                </p>
                            </div>
                        </div>
                        <div class='callout'>
                            <h3>Assign auction rooms to Customer (Company)</h3>
                            <p>Select all auction rooms that this company (and all users in that company) can take part in.</p>
                            <button class='button tiny brown-button select-all-auction-rooms' data-customer-id='<?php echo $id; ?>'>Select All</button> <button class='button tiny brown-button unselect-all-auction-rooms' data-customer-id='<?php echo $id; ?>'>Unselect All</button>
                            <?php
                                $Rooms = $ARO->listAllItems();
                                if (is_array($Rooms) && count($Rooms) > 0) {
                                    echo "<div class='grid-x grid-margin-x small-up-6 medium-up-4'>";
                                    foreach ($Rooms as $Room) {
                                        echo "<div class='cell'>";
                                        echo "<input class='auction-room' name='Room".$Room['ID']."' id='Room".$Room['ID']."' data-auction-room-id='".$Room['ID']."' type='checkbox' value='".$Room['ID']."'";
                                        #Now check to see if there is a record in the CustomersByAuctionRoom table
                                        if ($state != 'new') {
                                            if ($CustO->checkAuctionRoomMatch($id, $Room['ID']) === true) {
                                                echo " checked='checked'";
                                            }
                                        } else {
                                            if (isset($_SESSION['PostedForm']['Room'.$Room['ID']]) && $_SESSION['PostedForm']['Room'.$Room['ID']] == $Room['ID']) {
                                                echo " checked='checked'";
                                            }
                                        }
                                        echo "><label for='Room".$Room['ID']."'>".$Room['Title']."</label>";
                                        echo "</div>";
                                    }
                                    echo "</div>";
                                }
                            ?>
                        </div>


                        <div class='cca-panel'>
                            <h3>Status</h3>
                            <p>Update the status here from 'Applied' to 'Active' if you are happy and want to approve the customer.</p>
                            <p>
                                <label for='Status'>Status:</label>
                                <select name='Status' id='Status'>
                                    <?php
                                        echo "<option value=''";
                                        if ($_SESSION['PostedForm']['Status'] ?? '' == '') {
                                            echo " selected ='selected'";
                                        }
                                        echo ">Please select...</option>";
                                        $StatusOptions = $CustO->getStatusOptions();
                                        if (is_array($StatusOptions)) {
                                            foreach($StatusOptions as $Option) {
                                                echo "<option value='".$Option['Value']."'";
                                                if ($_SESSION['PostedForm']['Status'] == $Option['Value']) {
                                                    echo " selected ='selected'";
                                                }
                                                echo ">".$Option['Label']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </p>
                            <h3>OR</h3>
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
                                <p class='help-text'>Slide switch to Yes and Click Submit - customer - and users - will be deleted.</p>
                            </div>
                        </div>
                        
                        
                        <button class="button" name="submit" type="submit">Save Customer</button>
                        <input type='hidden' name='ID' id='ID' value='<?php echo $id; ?>'/>
                        <input type='hidden' name='UserID' id='UserID' value='<?php echo $_SESSION['PostedForm']['UserID']; ?>'/>
                        <input type='hidden' name='RemoveOldCustomer' id='RemoveOldCustomer' value=''/>
                        <input type='hidden' name='OldCustomerID' id='OldCustomerID' value=''/>
                    </form>
                </div>
                <div class='medium-4 cell'>
                    <?php
                        //Check for Company name conflicts
                        $SimilarCustomers = $CustO->listAllItems($_SESSION['PostedForm']['Company'], 'company-fuzzy');
                        //This will bring this customer record up as well - so need to miss that one out
                        if (is_array($SimilarCustomers) && count($SimilarCustomers) > 0) {
                            //Check each one
                            $display_conflict = false;
                            foreach($SimilarCustomers as $thisone) {
                                if ($thisone['ID'] != $id) {
                                    $display_conflict = true;
                                    break;
                                }
                            }
                            if ($display_conflict === true) {
                                reset($SimilarCustomers);
                                echo "<div class='cca-panel blue-panel'>";
                                echo "<p><strong>Potential Company Conflict</strong></p>";
                                echo "<p>The following similar businesses are already present in the system. Select one of them to assign this user to that business instead.</p><p>Or retain these details and a new business parent record will be created.</p>";
                                echo "<table class='standard'>";
                                foreach($SimilarCustomers as $Item) {
                                    if ($Item['ID'] != $id) {
                                        echo "<tr><td class='conflict-company-option' data-company-name='".FixOutput($Item['Company'])."' data-customer-id='".$Item['ID']."'>".$Item['Company']."</td></tr>\n";
                                    }
                                }
                                echo "</table>";
                                echo "<p><button class='button blue-button swap-company-button'>Swap Company</button></p>";
                                
                                echo "<p class='help-text'><strong>NOTE</strong><br/>If you select a company above, click Swap - and Approve the customer - the originally entered customer record will be lost - along with any notes below (as the user is assigned to the existing company - with any address changes you might make).</p>";
                                echo "</div>";
                            }
                        }
                    
                    ?>
                   
                    <div class='cca-panel'>
                        <p><strong>Notes</strong></p>
                        <p>
                            <label for="QuickNote">Type a quick note and hit submit to save it without affecting the rest of the page</label>
                            <textarea id="QuickNote" name="QuickNote" style="overflow:auto; height: 100px;"><?php echo check_output($_SESSION['QuickNote'] ?? ''); ?></textarea>
                        </p>
                        <button class="button note-add-button" data-parent-id='<?php echo $id; ?>'>Save note</button>
                        
                        <div class="notes-container">
                            <?php
                                $NO = new Note();
                                if (is_object($NO)) {
                                    $Notes = $NO->listAllItems($id,'customer-id', 'desc');
                                    if (is_array($Notes) && count($Notes) > 0) {
                                        foreach ($Notes as $note) {
                                            echo "<div class='quick-note-div' id='note".$note['ID']."' data-note-id='".$note['ID']."'>";
                                            //Delete
                                            echo "<button class='close-button note-delete-button' data-note-id='".$note['ID']."'>";
                                            echo "<span aria-hidden='true'><i class='fi-x'></i></span>";
                                            echo "</button>";
                                            //Content
                                            echo "<div class='note-note'>".$note['Content']."</div>";
                                            echo "<div class='note-attrib'>By: <span class='note-by'>".$note['NoteBy']."</span><br/>";
                                            echo "<span class='note-date'>".format_datetime($note['DateEdited'])."</span></div>";
                                            echo "</div>";
                                        }
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./customer_awaiting_approval_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> New customers</a>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
    
    
    <!-- ########## REVEALS ########## -->
    <!--DeleteNote -->
    <div class='reveal' id='DeleteNote' data-reveal data-close-on-click='false' data-close-on-esc='false' data-animation-in='fade-in' data-animation-out='fade-out'>
        <h2>Delete note</h2>
        <h3>Are you sure?</h3>
        <p>This will delete the selected note.</p>
        <span class='button float-left delete-confirm'>Delete</span>
        <span class='button float-right delete-cancel'>Cancel</span>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script src="/vendor/ckeditor/ckeditor.js"></script>
    <script>
        var thiscustomerid = '<?php echo $id; ?>';
        var newcustomerid = '';
        
        $(document).ready(function() {

            /*CKEDITOR.replace('QuickNote', {
                height: '100px',
                width: '100%',
                toolbar: 'Basic'
            });*/
            
            
            $('.select-all-auction-rooms').on('click', function(e) {
                e.preventDefault();
                //Select all Rooms
                $('.auction-room').prop('checked',true);
            });
            $('.unselect-all-auction-rooms').on('click', function(e) {
                e.preventDefault();
                //Select all Rooms
                $('.auction-room').prop('checked',false);
            });
            
            //Click Conflict option - store Customer ID
            $('body').on('click','.conflict-company-option', function(e) {
                var customerid = $(this).data('customer-id');
                //Store customer id in global var
                newcustomerid = customerid;
                //Show the row its been selected
                $(this).prepend('<i class="fi-check" /> ');
            });
            
            //Click Swap company - Update details AND SET FLAG to remove old customer record on save.
            $('.swap-company-button').on('click', function(e) {
                e.preventDefault();
                
                if (newcustomerid != '') {
                    //Load the full customer details by AJAX
                    $.ajax({
                        type: "POST",
                        url: './ajax/_ajaxCustomerGetDetail.php',
                        data: {
                            id: newcustomerid
                        },
                        dataType: 'json'
                    }).done(function (result) {
                        if (result.err) {
                            //error
                            //console.log(result.err);
                            alert('There was a problem: ' + result.err);
                        } else if (result.json != '') {
                            //Update the fields and set the secret fields
                            //Replace the fields
                            $('#Company').val(result.json.Company);
                            $('#Address1').val(result.json.Address1);
                            $('#Address2').val(result.json.Address2);
                            $('#Address3').val(result.json.Address3);
                            $('#Town').val(result.json.Town);
                            $('#County').val(result.json.County);
                            $('#Postcode').val(result.json.Postcode);
                            $('#Tel').val(result.json.Tel);
                            $('#CompanyEmail').val(result.json.Email);

                            //Set the two fields for old record deletion
                            $('#ID').val(result.json.ID);
                            $('#RemoveOldCustomer').val('Y');
                            $('#OldCustomerID').val(thiscustomerid);
                            
                        } else {
                            alert("Sorry - there was an issue");
                        }
                    });
                }
            });


            /**
             * NOTES
             */
            
            //ADD
            $('.note-add-button').on('click', function(e) {
                e.preventDefault();
                var parentid = $(this).data('parent-id');
                var note = $('#QuickNote').val();
                if (note == '') {
                    alert("Please enter a note first");
                    return false;
                }
                $.ajax({
                    url: './ajax/_ajaxNotes.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        mode: 'add',
                        parentid: parentid,
                        parent: 'Customers',
                        note: note
                    }
                }).done(function (result) {
                    // Handle server response
                    //console.log(result);
                    if (result.err) {
                        //error
                        //console.log(result.err);
                        alert('There was a problem saving the note: ' + result.err);
                    } else {
                        //success
                        //Add element to list
                        var newhtml = '<div class="quick-note-div" id="note'+result.newid+'" data-node-id="'+result.newid+'">';
                        
                        //Delete
                        newhtml += '<button class="close-button note-delete-button" data-note-id="'+result.newid+'">';
                        newhtml += '<span aria-hidden="true"><i class="fi-x"></i></span>';
                        newhtml += '</button>';
                        //Content
                        newhtml += '<div class="note-note">'+note+'</div>';
                        newhtml += '</div>';
                        
                        $('.notes-container').prepend(newhtml);
                        $('#QuickNote').val('');
                        resetDeleteConfirm();
                    }
                });
                
            });
            
            //DELETE
            $('body').on('click', '.note-delete-button', function (e) {
                e.preventDefault();
                var noteid = $(this).data('note-id');
                //var elem = $(this);
                //Set some data on the dialog button
                $('.delete-confirm').data('note-id', noteid);
                //$('.remove-cancel').data('elem',elem);
                //Open the dialog
                $('#DeleteNote').foundation('open');
            });

            //Confirm remove the evaluator
            $('.delete-confirm').on('click',function(e) {
                var noteid = $(this).data('note-id');
                $.ajax({
                    url: './ajax/_ajaxNotes.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        mode: 'delete',
                        id: noteid
                    }
                }).done(function (result) {
                    // Handle server response
                    //console.log(result);
                    if (result.err) {
                        //error
                        //console.log(result.err);
                        alert('There was a problem deleting the note: ' + result.err);
                    } else {
                        //success
                        //Remove element from list
                        var elem = '#note'+noteid;
                        $(elem).remove();
                        resetDeleteConfirm();
                    }
                });
            });

            //Cancel deletion
            $('.delete-cancel').on('click',function(e) {
                //var elem = $(this).data('elem');
                resetDeleteConfirm();
            });
        });

        function resetDeleteConfirm() {
            $('.delete-confirm').data('note-id', null);
            $('.delete-cancel').data('elem', null);
            $('#DeleteNote').foundation('close');
        }
    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>