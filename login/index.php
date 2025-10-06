<?php
    include('../assets/dbfuncs.php');
    unset($_SESSION['main']);
    unset($_SESSION['sub']);
    
    $referrer					= $_GET['referrer'] ?? null;

    include(DOCUMENT_ROOT . '/assets/inc-page_start.php');
?>
    <title>Login | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_head.php'); ?>
    <div class='grid-container double-space-above'>
        <div class='grid-x grid-margin-x'>
            <div class='medium-1 cell'><!----></div>
            <div class='medium-10 cell'>
                <div class='grid-x grid-margin-x' data-equalizer data-equalize-on='medium' id='main-login'>
                    <div class='medium-6 cell'>
                        <div class='cca-panel' data-equalizer-watch='main-login'>
                            <h1>Log in</h1>
                            <form action="./loginExec.php" name="login" enctype="multipart/form-data" method="post" class="standard">
                                <p>
                                    <label for="Email">Email address:</label><input type="email" name="Email" id="Email" value="" placeholder="Email address"/>
                                </p>
                                <p>
                                    <label for="Password">Password:</label><input type="password" name="Password" id="Password" value="" placeholder="Your password"/>
                                </p>
                                <p>
                                    <button class="button" name="submit" type="submit">Log in</button>
                                </p>
                                <input type='hidden' name='referrer' id='referrer' value='<?php echo urlencode(check_output($referrer)); ?>' />
                                <p><a class='black-link' href='/my-details/lost-password/'>Forgotten your password? No problem - click here</a></p>
                            </form>
                        </div>
                    </div>
                    <div class='medium-6 cell'>
                        <div class='cca-panel' data-equalizer-watch='main-login'>
                            <h1>Welcome to Click Car Auctions</h1>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris in mollis nulla, eget rutrum risus. Nulla hendrerit cursus urna, ut bibendum lacus aliquet sit amet. Quisque vitae volutpat justo. Ut sapien justo, convallis id lacinia id, consequat ut leo. </p>
                            <p>
                                <a class='button brown-button' href='#'>Learn more</a>
                                <a class='button' href='/signup/'>Sign up</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='medium-1 cell'><!----></div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT . '/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_end.php'); ?>