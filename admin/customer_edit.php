<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\Customer;
    use PeterBourneComms\CCA\User;
    use PeterBourneComms\CCA\AuctionRoom;
    use PeterBourneComms\CCA\Note;
    
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
        header("Location: ./customer_list.php");
        exit;
    }
    
    
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        //This is an edit - take all variable from the SESSION
    } else {
        
        $Customer = $CustO->getItemById($id);
        if (is_array($Customer) && count($Customer) > 0) {
            $_SESSION['PostedForm'] = $Customer;
            $_SESSION['PostedForm']['OldImgFilename'] = $Customer['ImgFilename'];
        }
    }
    
    unset($_SESSION['error']);
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'customer';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Customers | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Customer details</h1>
            <p>Edit the customer details as required.</p>
            <div class='grid-x grid-margin-x space-below'>
                <div class='medium-8 cell'>
                    <form action="./customer_editExec.php" name="form1" enctype="multipart/form-data" method="post" class="standard">
                        <div class='grid-x grid-margin-x'>
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
                                    <input type="text" name="Address3" id="Address3" value="<?php echo check_output($_SESSION['PostedForm']['Address3'] ?? ''); ?>" placeholder=""/>
                                </p>
                                <p><label for="Town">Town:</label>
                                    <input type="text" name="Town" id="Town" value="<?php echo check_output($_SESSION['PostedForm']['Town'] ?? ''); ?>" placeholder="Town"/>
                                </p>
                                <p><label for="County">County:</label>
                                    <input type="text" name="County" id="County" value="<?php echo check_output($_SESSION['PostedForm']['County'] ?? ''); ?>" placeholder="County"/>
                                </p>
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
                                <?php if (isset($_SESSION['emailerror'])) {
                                    echo $_SESSION['emailerror'];
                                    unset($_SESSION['emailerror']);
                                } ?>
                                <p>
                                    <label for="Email">Accounts email:</label><input type="email" name="Email" id="Email" value="<?php echo check_output($_SESSION['PostedForm']['Email'] ?? ''); ?>" placeholder="Accounts email"/>
                                </p>
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
                            </div>
                            <div class='medium-6 cell'>
                                <h3>Assign auction rooms to Customer (Company)</h3>
                                <p>Select all auction rooms that this company (and all users in that company) can take part in.</p>
                                <button class='button tiny brown-button select-all-auction-rooms' data-customer-id='<?php echo $id; ?>'>Select All</button>
                                <button class='button tiny brown-button unselect-all-auction-rooms' data-customer-id='<?php echo $id; ?>'>Unselect All</button>
                                <?php
                                    $Rooms = $ARO->listAllItems();
                                    if (is_array($Rooms) && count($Rooms) > 0) {
                                        echo "<div class='grid-x grid-margin-x small-up-1 medium-up-2'>";
                                        foreach ($Rooms as $Room) {
                                            echo "<div class='cell'>";
                                            echo "<input class='auction-room' name='Room".$Room['ID']."' id='Room".$Room['ID']."' data-auction-room-id='".$Room['ID']."' type='checkbox' value='".$Room['ID']."'";
                                            #Now check to see if there is a record in the CustomersByAuctionRoom table
                                            /*if (isset($_SESSION['PostedForm']['Room'.$Room['ID']]) && $_SESSION['PostedForm']['Room'.$Room['ID']] == $Room['ID']) {
                                                echo " checked='checked'";
                                            }*/
                                            if ($CustO->checkAuctionRoomMatch($id, $Room['ID']) === true) {
                                                echo " checked='checked'";
                                            }
                                            echo "><label for='Room".$Room['ID']."'>".$Room['Title']."</label>";
                                            echo "</div>";
                                        }
                                        echo "</div>";
                                    }
                                ?>
                            </div>
                        </div>
                        <div class='cca-panel'>
                            <h3>Delete</h3>
                            <div class="switch large">
                                <input class="switch-input" id="delete" type="checkbox" name="delete" value='1'>
                                <label class="switch-paddle" for="delete">
                                    <span class="show-for-sr">Delete?</span>
                                    <span class="switch-active" aria-hidden="true">Yes</span>
                                    <span class="switch-inactive" aria-hidden="true">No</span>
                                </label>
                            </div>
                            <p class='help-text'>Slide switch to Yes and Click Save - customer and users - will be deleted.</p>
                        </div>
                        <button class="button" name="submit" type="submit">Save</button>

                        <input type='hidden' name='ID' id='ID' value='<?php echo $id; ?>'/>
                    </form>
                </div>
                <div class='medium-4 cell'>
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
                                    $Notes = $NO->listAllItems($id, 'customer-id', 'desc');
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
            <h2>Users</h2>
            <?php
                //Display all users
                $Users = $UO->listAllItems($id, 'customer-id');
                if (is_array($Users) && count($Users) > 0) {
                    echo "<table class='standard'>";
                    echo "<tr><th>Name</th><th>Email</th><th>Mobile</th><th>Edit</th></tr>\n";
                    foreach($Users as $Item) {
                        echo "<tr>";
                        echo "<td>".$Item['Firstname']." ".$Item['Surname']."</td>";
                        echo "<td>";
                        if (isset($Item['Email'])) {
                            echo "<a href='mailto:".$Item['Email']."'>".$Item['Email']."</a>";
                        }
                        echo "</td>";
                        echo "<td>";
                        if (isset($Item['Mobile'])) {
                            echo "<a href='tel:".$Item['Mobile']."'>".$Item['Mobile']."</a>";
                        }
                        echo "</td>";
                        echo "<td><a href='./user_edit.php?id=".$Item['ID']."'><i class='fi-pencil'></i></a></td>";
                        echo "</tr>\n";
                    }
                    echo "</table>\n";
                }
            ?>
            <p><a class='button' href='./user_edit.php?state=new&customerid=<?php echo $id; ?>'><i class='fi-plus'></i> Add new user</a></p>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./customer_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> Customers</a>
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

        $(document).ready(function () {

            /*CKEDITOR.replace('QuickNote', {
                height: '100px',
                width: '100%',
                toolbar: 'Basic'
            });*/


            $('.select-all-auction-rooms').on('click', function (e) {
                e.preventDefault();
                //Select all Rooms
                $('.auction-room').prop('checked', true);
            });
            $('.unselect-all-auction-rooms').on('click', function (e) {
                e.preventDefault();
                //Select all Rooms
                $('.auction-room').prop('checked', false);
            });


            /**
             * NOTES
             */

            //ADD
            $('.note-add-button').on('click', function (e) {
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
                        var newhtml = '<div class="quick-note-div" id="note' + result.newid + '" data-node-id="' + result.newid + '">';

                        //Delete
                        newhtml += '<button class="close-button note-delete-button" data-note-id="' + result.newid + '">';
                        newhtml += '<span aria-hidden="true"><i class="fi-x"></i></span>';
                        newhtml += '</button>';
                        //Content
                        newhtml += '<div class="note-note">' + note + '</div>';
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
            $('.delete-confirm').on('click', function (e) {
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
                        var elem = '#note' + noteid;
                        $(elem).remove();
                        resetDeleteConfirm();
                    }
                });
            });

            //Cancel deletion
            $('.delete-cancel').on('click', function (e) {
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