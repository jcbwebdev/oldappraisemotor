<?php
    
    use PeterBourneComms\CMS\Database;
    
    include("../../assets/dbfuncs.php");
    
    //Check that a token is supplied
    $token = $_GET['t'];
    if ($token != '') {
        $DO = new Database();
        $dbconn = $DO->getConnection();
        
        //Query the DB for this token
        //Don't forget to check that its not older than 24 hours old
        $stmt = $dbconn->prepare("SELECT ID FROM PasswordResets WHERE Token = :token AND NOW() <= DATE_ADD(Requested, INTERVAL 1 DAY) LIMIT 1");
        $stmt->execute([
            'token' => $token
        ]);
        $item = $stmt->fetch();
    }
    if (is_array($item) && count($item) > 0 && $item['ID'] > 0) {
        //Offer the page up for resetting the password
    } else {
        $_SESSION['Message'] = array('Type' => 'error', 'Message' => "Sorry - the link you followed has expired. You will need to request a new one.");
        header("Location: /");
        exit;
    }
    
    include(DOCUMENT_ROOT."/assets/inc-page_start.php");
?>
    <title>Password reset | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT."/assets/inc-page_head.php"); ?>
    <div class='grid-container space-above'>
        <div class='grid-x grid-margin-x'>
            <div class='medium-2 cell'><!----></div>
            <div class='medium-8 cell'>
                <div class='cca-panel'>
                    <h1>Reset your password</h1>
                    <form action="passwordresetExec.php" name="passreset" enctype="multipart/form-data" method="post" class="standard">
                        
                        <?php if (isset($_SESSION['passworderror'])) {
                            echo $_SESSION['passworderror'];
                            unset($_SESSION['passworderror']);
                        } ?>
                        <p>
                            <label for="Password1">New Password:</label><input type="password" name="Password1" id="Password1" value="<?php echo check_output($_SESSION['Password1'] ?? ''); ?>" placeholder="Enter new password"/>
                        </p>
                        <p>
                            <label for="Password2">Repeat:</label><input type="password" name="Password2" id="Password2" value="<?php echo check_output($_SESSION['Password2'] ?? ''); ?>" placeholder='Repeat password'/>
                        </p>
                        <p>
                            <button class="button" name="submit" type="submit">Reset password</button>
                        </p>
                        <input type="hidden" name="token" id="token" value="<?php echo check_output($token); ?>"/>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT."/assets/inc-body_end.php"); ?>
<?php include(DOCUMENT_ROOT."/assets/inc-page_end.php"); ?>