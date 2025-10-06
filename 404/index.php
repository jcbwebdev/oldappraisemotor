<?php
    http_response_code(404);
    include('../assets/dbfuncs.php');
    unset($_SESSION['main']);
    unset($_SESSION['sub']);

    include(DOCUMENT_ROOT . '/assets/inc-page_start.php');
?>
    <title>404 - Not found | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_head.php'); ?>
    <div class='grid-container space-below'>
        <div class='grid-x grid-margin-x '>
            <div class='medium-2 cell'>
                <!---->
            </div>
            <div class='medium-8 cell'>
                <h1>404 - Sorry we can't find the page that you requested</h1>
                <p>Sorry we can't find the page you requested. Please use the menu above to find your way around. Or <a href='/contact/'>contact us</a>.</p>
            </div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT . '/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_end.php'); ?>