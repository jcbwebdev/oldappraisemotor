<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\Customer;
    use PeterBourneComms\CCA\User;
    use PeterBourneComms\CCA\Note;
    
    $state = $_GET['state'] ?? null;
    
    $CustomerID = $_GET['customerid'] ?? null;
    
    if ($state === 'new' && (!is_numeric($CustomerID) || $CustomerID <= 0)) {
        header("Location:user_list.php");
        exit;
    }
    
    
    $UO = new User();
    $CustO = new Customer();
    $NO = new Note();
    
    if (!is_object($UO) || !is_object($CustO) || !is_object($NO)) {
        die();
    }
    
    $id = clean_int($_GET['id'] ?? null);
    
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        //This is an edit - take all variable from the SESSION
    } elseif ($state == 'new') {
        unset($_SESSION['PostedForm']);
        $_SESSION['PostedForm']['CustomerID'] = $CustomerID;
        $_SESSION['PostedForm']['Title'] = "";
        $_SESSION['PostedForm']['Status'] = 'Active';
        $_SESSION['PostedForm']['AdminLevel'] = '';
    } else {
        $User = $UO->getItemById($id);
        if (is_array($User) && count($User) > 0) {
            $_SESSION['PostedForm'] = $User;
            //$_SESSION['PostedForm']['OldImgFilename'] = $User['ImgFilename'];
        }
    }
    
    //Load Customer
    $Customer = $CustO->getItemById($_SESSION['PostedForm']['CustomerID']);
    /*if (!is_array($Customer) || count($Customer) <= 0) {
        header("Location: ./user_list.php");
        exit;
    }*/
    
    unset($_SESSION['error']);
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'user';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Users | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>User details</h1>
            <p>Edit the user details as required.</p>
            <p>Company</p>
            <h2 class='user-edit-company-name'><?php echo check_output($Customer['Company'] ?? ''); ?> <span class='quick-link'><a href='./customer_edit.php?id=<?php echo $_SESSION['PostedForm']['CustomerID']; ?>'><i class='fi-link'> </i></a></span></h2>
            <div class='grid-x grid-margin-x space-below'>
                <div class='medium-8 cell'>
                    <form action="./user_editExec.php" name="form1" enctype="multipart/form-data" method="post" class="standard">
                        <div class='grid-x grid-margin-x'>
                            <div class='medium-6 cell'>
                                <h2>User details</h2>
                                <?php if (isset($_SESSION['titleerror'])) {
                                    echo $_SESSION['titleerror'];
                                    unset($_SESSION['titleerror']);
                                } ?>
                                <p>
                                    <label for="Title">Title:</label><select name='Title' id='Title'>
                                        <?php
                                            echo "<option value=''";
                                            if ($_SESSION['PostedForm']['Title'] == '') {
                                                echo " selected ='selected'";
                                            }
                                            echo ">Please select...</option>";
                                            $TitleOptions = $UO->getTitleOptions();
                                            if (is_array($TitleOptions)) {
                                                foreach($TitleOptions as $Option) {
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
                                <p>
                                    <label for='Status'>Status:</label>
                                    <select name='Status' id='Status'>
                                        <?php
                                            echo "<option value=''";
                                            if ($_SESSION['PostedForm']['Status'] ?? '' == '') {
                                                echo " selected ='selected'";
                                            }
                                            echo ">Please select...</option>";
                                            $StatusOptions = $UO->getStatusOptions();
                                            if (is_array($StatusOptions)) {
                                                foreach($StatusOptions as $Option) {
                                                    echo "<option value='".$Option['Value']."'";
                                                    if ($_SESSION['PostedForm']['Status'] === $Option['Value']) {
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
                                <div class='cca-panel space-below'>
                                    <p>
                                        <label for='AdminLevel'>Admin level:</label>
                                        <select name='AdminLevel' id='AdminLevel'>
                                            <?php
                                                echo "<option value=''";
                                                if ($_SESSION['PostedForm']['AdminLevel'] ?? '' == '') {
                                                    echo " selected ='selected'";
                                                }
                                                echo ">Please select...</option>";
                                                $AdminOptions = $UO->getAdminLevelOptions();
                                                if (is_array($AdminOptions)) {
                                                    foreach($AdminOptions as $Option) {
                                                        echo "<option value='".$Option['Value']."'";
                                                        if ($_SESSION['PostedForm']['AdminLevel'] === $Option['Value']) {
                                                            echo " selected ='selected'";
                                                        }
                                                        echo ">".$Option['Label']."</option>";
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </p>
                                </div>
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
                            <p class='help-text'>Slide switch to Yes and Click Save - user will be deleted.</p>
                        </div>
                        <button class="button" name="submit" type="submit">Save</button>

                        <input type='hidden' name='ID' id='ID' value='<?php echo $id; ?>'/>
                        <input type='hidden' name='CustomerID' id='CustomerID' value='<?php echo $_SESSION['PostedForm']['CustomerID']; ?>' />
                    </form>
                </div>
                <div class='medium-4 cell'>
                    <div class='cca-panel'>
                        <p><button class='button send-password-to-user' data-user-id='<?php echo $id; ?>'>Send password reset email</button></p>

                        <div class='cca-panel email-sent-success'><p class='lead'>The password reset email was sent successfully.</p></div>
                        
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
                                    $Notes = $NO->listAllItems($id, 'user-id', 'desc');
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
            <a href='./user_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> Users</a>
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
        var thisuserid = '<?php echo $id; ?>';

        $(document).ready(function () {
            $('.email-sent-success').hide();

            /*CKEDITOR.replace('QuickNote', {
                height: '100px',
                width: '100%',
                toolbar: 'Basic'
            });*/

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
                        parent: 'Users',
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
                e.preventDefault();
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


            /**
             * SEND PASSWORD RESET
             *
             */
            $('.send-password-to-user').on('click', function(e) {
                e.preventDefault();
                var userid = $(this).data('user-id');
                
                $.ajax({
                    url: './ajax/_ajaxUserSendPasswordReset.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        id: userid
                    }
                }).done(function (result) {
                    // Handle server response
                    //console.log(result);
                    if (result.err) {
                        //error
                        //console.log(result.err);
                        alert('There was a problem sending the email: ' + result.err);
                    } else {
                        //success
                        $('.email-sent-success').show('fast').delay(4000).hide('fast');
                    }
                });
            });
        });

        function resetDeleteConfirm() {
            $('.delete-confirm').data('note-id', null);
            $('.delete-cancel').data('elem', null);
            $('#DeleteNote').foundation('close');
        }
    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>