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
                    <h2>Enter a password</h2>
                    <form action="./passwordExec.php" name="login" enctype="multipart/form-data" method="post" class="standard">
                        <div class='grid-x grid-margin-x'>
                            <div class='medium-6 cell'>
                                <p>
                                    <label for="Password">Password:</label><input type="password" name="Password" id="Password" value="" placeholder="Password" tabindex='1' />
                                </p>
                                <div class='callout secondary'>
                                    <p>You must accept our Terms and Conditions. <a href='#' target='_blank'>Click here to read them.</a> Click the switch to show you have read and accept them.</p>
                                    <label>I accept the Terms and Conditions</label>
                                    <div class='switch'>
                                        <input class='switch-input' id='TsCs' type='checkbox' name='TsCs' tabindex='3'>
                                        <label class='switch-paddle' for='TsCs'>
                                            <span class='show-for-sr'>Terms and Conditions accepted</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class='medium-6 cell'>
                                <p>
                                    <label for="Password2">Repeat:</label><input type="password" name="Password2" id="Password2" value="" placeholder="Re-type password" tabindex='2'/>
                                </p>
                                <p>
                                    <button class="button" name="submit" type="submit">Continue &gt;</button>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class='medium-2 cell'><!----></div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT . '/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_end.php'); ?>