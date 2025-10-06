<?php
    include('../../assets/dbfuncs.php');
    unset($_SESSION['main']);
    unset($_SESSION['sub']);
    
    include(DOCUMENT_ROOT . '/assets/inc-page_start.php');
?>
    <title>Sign up | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='grid-x grid-margin-x'>
            <div class='medium-2 cell'><!----></div>
            <div class='medium-8 cell'>
                <div class='cca-panel'>
                    <h1>Sign up</h1>
                    <h2>Thanks!</h2>
                    <p>Your application has been saved and submitted. We will be in touch soon.</p>
                </div>
            </div>
            <div class='medium-2 cell'><!----></div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT . '/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_end.php'); ?>