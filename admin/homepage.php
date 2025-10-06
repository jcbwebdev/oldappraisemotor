<?php
    include("../assets/dbfuncs.php");
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CMS\HomePage;
    
    $_SESSION['main'] = "admin";
    $_SESSION['admin-section'] = 'home';
    $_SESSION['sub'] = 'homepage';
    
    if (isset($_SESSION['error']) && isset($_SESSION['PostedForm'])) {
        //This is an edit - take all variable from the SESSION
        
    } else {
        $CO = new HomePage();
        if (is_object($CO)) {
            $HomePage = $CO->getItem();
            if (is_array($HomePage) && count($HomePage) > 0) {
                $_SESSION['PostedForm'] = $HomePage;
            }
        } else {
            header("Location:/admin/");
            exit;
        }
    }
    
    unset($_SESSION['error']);
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Homepage | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above space-below'>
        <div class='grid-x grid-margin-x grid-margin-y '>
            <div class='medium-3 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-sidemenu-admin.php'); ?>
            </div>
            <div class='medium-9 cell'>
                <h1>Homepage</h1>
                <form action="homepageExec.php" enctype="multipart/form-data" method="post" name="form1" id="form1" class="standard">
                    <div class='grid-x grid-margin-x' data-equalizer='row1' data-equalize-on='medium'>
                        <div class='medium-6 cell'>
                            <div class='callout' data-equalizer-watch='row1'>
                                <?php if (isset($_SESSION['titleerror'])) {
                                    echo $_SESSION['titleerror'];
                                    unset($_SESSION['titleerror']);
                                } ?>
                                <p>
                                    <label for="Title">Title:</label><input name="Title" type="text" id="Title" value="<?php echo check_output($_SESSION['PostedForm']['Title']); ?>"/>
                                </p>
                                <p>
                                    <label for="SubTitle">Sub Title:</label><input name="SubTitle" type="text" id="SubTitle" value="<?php echo check_output($_SESSION['PostedForm']['SubTitle']); ?>"/>
                                </p>
                            </div>
                        </div>
                        <div class='medium-6 cell'>
                            <div class="callout warning" data-equalizer-watch='row1'>
                                <p>
                                    <label for="MetaTitle">Meta Title:</label><input name="MetaTitle" type="text" id="MetaTitle" value="<?php echo check_output($_SESSION['PostedForm']['MetaTitle']); ?>"/>
                                </p>
                                <p>
                                    <label for="MetaDesc">Meta Description:</label><textarea id="MetaDesc" name="MetaDesc" style="overflow:auto; height: 70px;" placeholder="The hidden page description for SEO"><?php echo check_output($_SESSION['PostedForm']['MetaDesc']); ?></textarea>
                                </p>
                                <p>
                                    <label for="MetaKey">Meta Keywords:</label><textarea id="MetaKey" name="MetaKey" style="overflow:auto; height: 50px;" placeholder="The hidden page key words for SEO"><?php echo check_output($_SESSION['PostedForm']['MetaKey']); ?></textarea>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    
                    <?php if (isset($_SESSION['contenterror'])) {
                        echo $_SESSION['contenterror'];
                        unset($_SESSION['contenterror']);
                    } ?>
                    <!--<div class='grid-x grid-margin-x'>
                        <div class='medium-6 cell'>-->
                            <p>
                                <label for="Content">Main Content:</label><textarea id="Content" name="Content" style="overflow:auto; height: 200px;"><?php echo check_output($_SESSION['PostedForm']['Content']); ?></textarea>
                            </p>
                        <!--</div>
                        <div class='medium-6 cell'>
                            <p><label for="Col2Content">Right hand column:</label><textarea id="Col2Content" name="Col2Content" style="overflow:auto; height: 100px;"><?php echo check_output($_SESSION['PostedForm']['Col2Content']); ?></textarea></p>

                        </div>
                    </div>-->
                    
                    <div class='callout success'>
                        <h2>Save your changes</h2>
                        <p class='lead'>Nothing is saved until you press the 'Save' button below:</p>
                        <input type='hidden' id='ID' name='ID' value='<?php echo $_SESSION['PostedForm']['ID']; ?>'/>
                        <p>
                            <button class='button' type='submit' value='submit'>Save</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script src="/vendor/ckeditor/ckeditor.js"></script>
    <script>
        $(document).ready(function () {
            CKEDITOR.replace('Content', {
                height: '500px',
                width: '100%'
            });
            CKEDITOR.replace('Col2Content', {
                height: '500px',
                width: '100%'
            });
        });

    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>