<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CMS\Carousel;
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'home';
    $_SESSION['sub'] = 'carousel';
    
    if (!isset($_GET['state']) || $_GET['state'] == '') {
        header("Location:carousel_list.php");
        exit;
    }
    $state = $_GET['state'];
    if (isset($_GET['id'])) {
        $id = clean_int($_GET['id']);
    } else { $id = null; }
    
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        //This is an edit - take all variable from the SESSION
    } elseif ($state == 'new') {
        unset($_SESSION['PostedForm']);
        $_SESSION['PostedForm']['ImgPath'] = '/user_uploads/images/carousel/';
    } elseif ($state == 'edit') {
        //its the first stage of an edit - so retrieve from the DB
        //Retrieve all the information for this area
        
        $CO = new Carousel();
        
        if (is_object($CO)) {
            $Carousel = $CO->getItemById($id);
            if (is_array($Carousel) && count($Carousel) > 0) {
                $_SESSION['PostedForm'] = $Carousel;
                $_SESSION['PostedForm']['OldImgFilename'] = $Carousel['ImgFilename'];
            }
        } else {
            header("Location:carousel_list.php");
            exit;
        }
    }
    
    unset($_SESSION['error']);
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Carousel | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above space-below'>
        <div class='grid-x grid-margin-x grid-margin-y '>
            <div class='medium-3 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-sidemenu-admin.php'); ?>
            </div>
            <div class='medium-9 cell'>
                <h1>Carousel item administration</h1>
                <form action="carousel_detailsExec.php?state=<?php echo check_output($state); ?>" enctype="multipart/form-data" method="post" name="form1" id="form1" class="standard">
                    <?php if (isset($_SESSION['contenterror'])) {
                        echo $_SESSION['contenterror'];
                        unset($_SESSION['contenterror']);
                    } ?>

                    <div class='callout'>
                        <?php if (isset($_SESSION['titleerror'])) {
                            echo $_SESSION['titleerror'];
                            unset($_SESSION['titleerror']);
                        } ?>
                        <p>
                            <label for="Title">Title:</label><input name="Title" type="text" id="Title" value="<?php echo check_output($_SESSION['PostedForm']['Title'] ?? null); ?>"/>
                        </p>
                        <?php if (isset($_SESSION['linkerror'])) {
                            echo $_SESSION['linkerror'];
                            unset($_SESSION['linkerror']);
                        } ?>
                        <p><label for="Content">Other text:</label><textarea id="Content" name="Content" style='height: 100px;'><?php echo check_output($_SESSION['PostedForm']['Content'] ?? null); ?></textarea></p>
                        <p>
                            <label for="CTALink">Link:</label><input name="CTALink" type="text" id="CTALink" value="<?php echo check_output($_SESSION['PostedForm']['CTALink'] ?? null); ?>"/>
                            <span class="help-text">eg: /about-us  (do not type https:// in here unless linking to an external website)
                        </p>
                        <!--<div class='grid-x grid-margin-x'>
                            <div class='medium-6 cell'>
                                <p><label for="Content">Other text:</label><textarea id="Content" name="Content" style='height: 100px;'><?php echo check_output($_SESSION['PostedForm']['Content'] ?? null); ?></textarea></p>
                            </div>
                            <div class='medium-6 cell'>
                                <p>
                                    <label for="CTALabel">Button text:</label><input name="CTALabel" type="text" id="CTALabel" value="<?php echo check_output($_SESSION['PostedForm']['CTALabel'] ?? null); ?>"/>
                                </p>
                                
                            </div>
                        </div>-->
                    </div>


                    <h2>Image</h2>
                    <p class="help-text">Once you've dragged an image on, you can select a portion to show in the carousel. You can only crop to the specified proportions.</p>
                    <?php if (isset($_SESSION['imageerror']) && $_SESSION['imageerror'] != '') {
                        echo $_SESSION['imageerror'];
                        unset($_SESSION['imageerror']);
                    } ?>
                    <div id='image1'></div>

                    <p><label for="DisplayOrder">Display
                            Order:</label><input name="DisplayOrder" type="text" id="DisplayOrder" value="<?php echo check_output($_SESSION['PostedForm']['DisplayOrder'] ?? null); ?>"/><span class="note">Standard ascending order</span>
                    </p>

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
                        <p class='help-text'>Slide switch to Yes and Click Submit - carousel slide will be deleted.</p>
                    </div>

                    <div class='callout success'>
                        <h2>Save your changes</h2>
                        <p class='lead'>Nothing is saved until you press the 'Save' button below:</p>
                        <input type='hidden' id='ID' name='ID' value='<?php if (isset($_SESSION['PostedForm']['ID'])) { echo $_SESSION['PostedForm']['ID']; } ?>'/>
                        <p>
                            <button class='button' type='submit' value='submit'>Save</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./carousel_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> Carousel list</a>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script src="/vendor/ckeditor/ckeditor.js"></script>
    <script>
        $(document).ready(function () {
            //Cropper init stuff
            var drop = new createImageCropper({
                imageContainer: 'image1',
                instanceCounter: '1',
                width: 1600,
                height: 700,
                formName: 'form1',
                outputElem: 'ImgFile',
                oldField: 'OldImgFilename',
                origPath: '<?php echo FixOutput($_SESSION['PostedForm']['ImgPath'] ?? null); ?>',
                origImg: '<?php echo FixOutput($_SESSION['PostedForm']['OldImgFilename'] ?? null); ?>',
                deleteID: '<?php echo FixOutput($_SESSION['PostedForm']['ID'] ?? null); ?>',
                dialogText: 'Are you sure you want to delete this image?',
                thumbnails: 'N',
                restoreSize: 250,
                scriptToRun: '/admin/ajax/_imageHandler.php',
                contentType: 'carousel'
            });
            /*CKEDITOR.replace('Content', {
                height: '150px'
            });*/
        });

    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>