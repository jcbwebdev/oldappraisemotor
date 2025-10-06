<?php
    include('assets/dbfuncs.php');
    echo('sssss');
    exit();
    //If not logged in - redirect to login page
    if (!isset($_SESSION['UserDetails']['ID']) || !is_numeric($_SESSION['UserDetails']['ID']) || $_SESSION['UserDetails']['ID'] <= 0) {
        header("Location:/login/");
        exit;
    }

    use PeterBourneComms\CMS\HomePage;

    $_SESSION['main'] = 'home';
    unset($_SESSION['sub']);

    $HO = new HomePage();
    if (is_object($HO)) {
        $Content = $HO->getItem();
    }

    if ($Content['MetaTitle'] == '') {
        $Content['MetaTitle'] = $Content['Title'];
    }
    if ($Content['MetaDesc'] == '') {
        $Content['MetaDesc'] = $globalMetaDesc;
    }
    if ($Content['MetaKey'] == '') {
        $Content['MetaKey'] = $globalMetaKey;
    }

    include(DOCUMENT_ROOT . '/assets/inc-page_start.php');
?>
    <title><?php echo $Content['MetaTitle']; ?> | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_head.php'); ?>
    <div class='grid-container'>
        <div class='grid-x grid-margin-x'>
            <div class='medium-2 cell'><!----></div>
            <div class='medium-8 cell'>
                <?php
                    if ($Content['Title'] != '') {
                        echo "<h1>" . $Content['Title'] . "</h1>";
                    }
                    if ($Content['SubTitle'] != '') {
                        echo "<h2>" . $Content['SubTitle'] . "</h2>";
                    }
    
                    echo $Content['Content'];
                ?>
                <p><a class='button large' href='/auction/'>TEMP LINK TO AUCTIONS</a></p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris in mollis nulla, eget rutrum risus. Nulla hendrerit cursus urna, ut bibendum lacus aliquet sit amet. Quisque vitae volutpat justo. Ut sapien justo, convallis id lacinia id, consequat ut leo. Nulla nulla leo, imperdiet sit amet ligula at, feugiat tincidunt dui. Pellentesque iaculis vitae nisi eu ullamcorper. Sed nibh dui, faucibus non neque sed, gravida finibus enim.Pellentesque gravida eros a tellus interdum vulputate.  </p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris in mollis nulla, eget rutrum risus. Nulla hendrerit cursus urna, ut bibendum lacus aliquet sit amet. Quisque vitae volutpat justo. Ut sapien justo, convallis id lacinia id, consequat ut leo. Nulla nulla leo, imperdiet sit amet ligula at, feugiat tincidunt dui. Pellentesque iaculis vitae nisi eu ullamcorper. Sed nibh dui, faucibus non neque sed, gravida finibus enim.Pellentesque gravida eros a tellus interdum vulputate.  </p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris in mollis nulla, eget rutrum risus. Nulla hendrerit cursus urna, ut bibendum lacus aliquet sit amet. Quisque vitae volutpat justo. Ut sapien justo, convallis id lacinia id, consequat ut leo. Nulla nulla leo, imperdiet sit amet ligula at, feugiat tincidunt dui. Pellentesque iaculis vitae nisi eu ullamcorper. Sed nibh dui, faucibus non neque sed, gravida finibus enim.Pellentesque gravida eros a tellus interdum vulputate.  </p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris in mollis nulla, eget rutrum risus. Nulla hendrerit cursus urna, ut bibendum lacus aliquet sit amet. Quisque vitae volutpat justo. Ut sapien justo, convallis id lacinia id, consequat ut leo. Nulla nulla leo, imperdiet sit amet ligula at, feugiat tincidunt dui. Pellentesque iaculis vitae nisi eu ullamcorper. Sed nibh dui, faucibus non neque sed, gravida finibus enim.Pellentesque gravida eros a tellus interdum vulputate.  </p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris in mollis nulla, eget rutrum risus. Nulla hendrerit cursus urna, ut bibendum lacus aliquet sit amet. Quisque vitae volutpat justo. Ut sapien justo, convallis id lacinia id, consequat ut leo. Nulla nulla leo, imperdiet sit amet ligula at, feugiat tincidunt dui. Pellentesque iaculis vitae nisi eu ullamcorper. Sed nibh dui, faucibus non neque sed, gravida finibus enim.Pellentesque gravida eros a tellus interdum vulputate.  </p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris in mollis nulla, eget rutrum risus. Nulla hendrerit cursus urna, ut bibendum lacus aliquet sit amet. Quisque vitae volutpat justo. Ut sapien justo, convallis id lacinia id, consequat ut leo. Nulla nulla leo, imperdiet sit amet ligula at, feugiat tincidunt dui. Pellentesque iaculis vitae nisi eu ullamcorper. Sed nibh dui, faucibus non neque sed, gravida finibus enim.Pellentesque gravida eros a tellus interdum vulputate.  </p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris in mollis nulla, eget rutrum risus. Nulla hendrerit cursus urna, ut bibendum lacus aliquet sit amet. Quisque vitae volutpat justo. Ut sapien justo, convallis id lacinia id, consequat ut leo. Nulla nulla leo, imperdiet sit amet ligula at, feugiat tincidunt dui. Pellentesque iaculis vitae nisi eu ullamcorper. Sed nibh dui, faucibus non neque sed, gravida finibus enim.Pellentesque gravida eros a tellus interdum vulputate.  </p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris in mollis nulla, eget rutrum risus. Nulla hendrerit cursus urna, ut bibendum lacus aliquet sit amet. Quisque vitae volutpat justo. Ut sapien justo, convallis id lacinia id, consequat ut leo. Nulla nulla leo, imperdiet sit amet ligula at, feugiat tincidunt dui. Pellentesque iaculis vitae nisi eu ullamcorper. Sed nibh dui, faucibus non neque sed, gravida finibus enim.Pellentesque gravida eros a tellus interdum vulputate.  </p>
            </div>
            <div class='medium-2 cell'><!----></div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT . '/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_end.php'); ?>