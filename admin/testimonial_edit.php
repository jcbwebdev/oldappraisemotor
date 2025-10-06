<?php
    include("../assets/dbfuncs.php");
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CMS\Testimonial;
    use PeterBourneComms\CMS\Content;
    
    $_SESSION['main'] = "admin";
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = "testimonial";
    
    if (!isset($_GET['state']) || $_GET['state'] == '') {
        header("Location:testimonial_list.php");
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
    } elseif ($state == 'edit') {
        //its the first stage of an edit - so retrieve from the DB
        //Retrieve all the information for this area
        $TO = new Testimonial();
        
        if (is_object($TO)) {
            $Testimonial = $TO->getItemById($id);
            if (is_array($Testimonial) && count($Testimonial) > 0) {
                $_SESSION['PostedForm'] = $Testimonial;
            }
        } else {
            header("Location:testimonial_list.php");
            exit;
        }
    }
    
    unset($_SESSION['error']);
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Testimonials | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above space-below'>
        <div class='grid-x grid-margin-x grid-margin-y '>
            <div class='medium-3 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-sidemenu-admin.php'); ?>
            </div>
            <div class='medium-9 cell'>
                <h1>Testimonials</h1>
                <form action="testimonial_editExec.php?state=<?php echo $state; ?>" method="post" name="form1" id="form1" class="standard" enctype="multipart/form-data">

                    <!--<p><label for='ContentID'>Assign to:</label>
            <select name='ContentID' id='ContentID'>
                <option value='' <?php if (isset($_SESSION['PostedForm']['ContentID']) && $_SESSION['PostedForm']['ContentID'] === '') {
                        echo "selected='selected'";
                    } ?>>Please select...
                </option>
                <?php
                        $CO = new Content();
                        $Content = $CO->getAllParentContent();
                        if (is_array($Content) && count($Content) > 0) {
                            foreach ($Content as $Page) {
                                echo "<option value='".$Page['ID']."'";
                                if (isset($_SESSION['PostedForm']['ContentID']) && $Page['ID'] == $_SESSION['PostedForm']['ContentID']) {
                                    echo " selected='selected'";
                                }
                                echo ">".$Page['Title']."</option>";
                            }
                        }
                    ?>
            </select></p>-->
                    
                    <?php if (isset($_SESSION['titleerror'])) {
                        echo $_SESSION['titleerror'];
                        unset($_SESSION['titleerror']);
                    } ?>
                    <p>
                        <label for="Quote">Quote:</label><textarea name="Quote" id="Quote" style='height: 90px;'><?php echo check_output($_SESSION['PostedForm']['Quote'] ?? null); ?></textarea>
                    </p>

                    <!--<p>
                        <label for="Attribution">Attribution:</label><input name="Attribution" type="text" id="Attribution" placeholder="Name of client" value="<?php echo check_output($_SESSION['PostedForm']['Attribution'] ?? null); ?>"/>
                    </p>-->

                    <!--<label>Full detail:</label>
        <?php if (isset($_SESSION['contenterror'])) {
                        echo $_SESSION['contenterror'];
                        unset($_SESSION['contenterror']);
                    } ?>
        <p>
            <textarea id="Content" name="Content" style="overflow:auto; height: 300px;"><?php echo check_output($_SESSION['PostedForm']['Content'] ?? null); ?></textarea>
        </p>

        <div class="callout warning">
            <?php if (isset($_SESSION['urlerror'])) {
                        echo $_SESSION['urlerror'];
                        unset($_SESSION['urlerror']);
                    } ?>
            <p>
                <label for="URLText">URL to use:</label><input name="URLText" type="text" id="URLText" value="<?php echo check_output($_SESSION['PostedForm']['URLText'] ?? null); ?>" placeholder="eg: about-us" /><!--<span class="caption"> Note: If you change this buttons and links on the home page may not work until you contact Peter Bourne.</span></p>--><!--
            <p class="help-text">Use this to give a human readable URL to the page - make it unique! (Don't use spaces)<br />eg: about-us
            </p>
            <p>
                <label for="MetaTitle">Meta Title:</label><input name="MetaTitle" type="text" id="MetaTitle" placeholder="Page title for the tab" value="<?php echo check_output($_SESSION['PostedForm']['MetaTitle'] ?? null); ?>" />
            </p>
            <p>
                <label for="MetaDesc">Meta Description:</label><textarea id="MetaDesc" name="MetaDesc" style="overflow:auto; height: 70px;" placeholder="The hidden page description for SEO"><?php echo check_output($_SESSION['PostedForm']['MetaDesc'] ?? null); ?></textarea>
            </p>
            <p>
                <label for="MetaKey">Meta Keywords:</label><textarea id="MetaKey" name="MetaKey" style="overflow:auto; height: 50px;" placeholder="The hidden page key words for SEO"><?php echo check_output($_SESSION['PostedForm']['MetaKey'] ?? null); ?></textarea>
            </p>
        </div>-->


                    <div class='callout alert'>
                        <h3>Delete</h3>
                        <div class="switch large">
                            <input class="switch-input" id="delete" type="checkbox" name="delete" value='1'>
                            <label class="switch-paddle" for="delete">
                                <span class="show-for-sr">Do you like me?</span>
                                <span class="switch-active" aria-hidden="true">Yes</span>
                                <span class="switch-inactive" aria-hidden="true">No</span>
                            </label>
                        </div>
                        <p class='help-text'>Slide switch to Yes and Click Submit - testimonial will be deleted.</p>
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
            <a href='./testimonial_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> Testimonial list</a>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script src="/vendor/ckeditor/ckeditor.js"></script>
    <script type='text/javascript'>
        $(document).ready(function () {

            /*CKEDITOR.replace('Content', {
                height: '450px'
            });*/

        });
    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>