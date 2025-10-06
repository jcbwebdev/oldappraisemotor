<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\AuctionRoom;
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'auction-room';
    
    if (!isset($_GET['state']) || $_GET['state'] == '') {
        header("Location:auction_room_list.php");
        exit;
    }
    $state = $_GET['state'];
    if (isset($_GET['id'])) {
        $id = clean_int($_GET['id']);
    } else {
        $id = null;
    }
    
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        //This is an edit - take all variable from the SESSION
    } elseif ($state == 'new') {
        unset($_SESSION['PostedForm']);
        $_SESSION['PostedForm']['ImgPath'] = '/user_uploads/images/auction-room-logos/';
    } elseif ($state == 'edit') {
        //its the first stage of an edit - so retrieve from the DB
        //Retrieve all the information for this area
        
        $ARO = new AuctionRoom();
        
        if (is_object($ARO)) {
            $AuctionRoom = $ARO->getItemById($id);
            if (is_array($AuctionRoom) && count($AuctionRoom) > 0) {
                $_SESSION['PostedForm'] = $AuctionRoom;
                $_SESSION['PostedForm']['OldImgFilename'] = $AuctionRoom['ImgFilename'];
            }
        } else {
            header("Location:auction_room_list.php");
            exit;
        }
    }
    
    unset($_SESSION['error']);
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Auction Rooms | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Auction room</h1>
            <p>Edit the details as required.</p>
            <form action="./auction_room_editExec.php?state=<?php echo $state; ?>" name="form1" id='form1' enctype="multipart/form-data" method="post" class="standard">
                <div class='grid-x grid-margin-x'>
                    <div class='medium-4 cell'>
                        <?php if (isset($_SESSION['titleerror'])) {
                            echo $_SESSION['titleerror'];
                            unset($_SESSION['titleerror']);
                        } ?>
                        <p>
                            <label for="Title">Title:</label><input name="Title" type="text" id="Title" value="<?php echo check_output($_SESSION['PostedForm']['Title'] ?? null); ?>"/>
                        </p>
                        
                        <?php if (isset($_SESSION['contenterror'])) {
                            echo $_SESSION['contenterror'];
                            unset($_SESSION['contenterror']);
                        } ?>
                        <p>
                            <label for="Content">Auction room description:</label><textarea id="Content" name="Content" style='height: 100px;'><?php echo check_output($_SESSION['PostedForm']['Content'] ?? null); ?></textarea>
                        </p>
                    </div>
                    <div class='medium-4 cell'>
                        <div class='callout'>
                            <h2>Image</h2>
                            <p class="help-text">Once you've dragged an image on, you can crop it. You can only crop to the specified proportions.</p>
                            <?php if (isset($_SESSION['imageerror']) && $_SESSION['imageerror'] != '') {
                                echo $_SESSION['imageerror'];
                                unset($_SESSION['imageerror']);
                            } ?>
                            <div id='image1'></div>
                        </div>
                    </div>
                    <div class='medium-4 cell'>
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
                    <p class='help-text'>Slide switch to Yes and Click Submit - auction room will be deleted.</p>
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

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./auction_room_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> Auction rooms</a>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script>
        $(document).ready(function () {
            //Cropper init stuff
            var drop = new createImageCropper({
                imageContainer: 'image1',
                instanceCounter: '1',
                width: 600,
                height: 600,
                formName: 'form1',
                outputElem: 'ImgFile',
                oldField: 'OldImgFilename',
                origPath: '<?php echo FixOutput($_SESSION['PostedForm']['ImgPath'] ?? ''); ?>',
                origImg: '<?php echo FixOutput($_SESSION['PostedForm']['OldImgFilename'] ?? ''); ?>',
                deleteID: '<?php echo FixOutput($_SESSION['PostedForm']['ID'] ?? ''); ?>',
                dialogText: 'Are you sure you want to delete this image?',
                thumbnails: 'N',
                restoreSize: 250,
                scriptToRun: '/admin/ajax/_imageHandler.php',
                contentType: 'auction-room'
            });
        });

    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>