<?php
    include("../../assets/dbfuncs.php");

    include(DOCUMENT_ROOT . "/assets/inc-page_start.php");
?>
    <title>Lost your password | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT . "/assets/inc-page_head.php"); ?>
    <div class='grid-container space-above'>
        <div class='grid-x grid-margin-x grid-margin-y '>
            <div class='medium-4 small-order-2 medium-order-1 cell'>
            
            </div>
            <div class='medium-8 small-order-1 medium-order-2 cell'>
                <h1>Forgotten your password?</h1>
                <p>If you have lost or forgotten your password please fill in your details below. We will email you a link to reset your password directly on the HETA website.</p>

                <form action="./lostpasswordExec.php" method="post" enctype="multipart/form-data" class="standard" name="register" id="register">
                    <p>
                        <label for="Email">Email:</label><input name="Email" type="email" id="Email" value="<?php echo check_output($_SESSION['Email']); ?>"/>
                    </p>
                    <p>
                        <button class="button" name="submit" type="submit">Request new password</button>
                    </p>
                </form>
            </div>
        </div><!-- end of row -->
    </div>
<?php include(DOCUMENT_ROOT . "/assets/inc-body_end.php"); ?>
<?php include(DOCUMENT_ROOT . "/assets/inc-page_end.php"); ?>