<?php
    include('../assets/dbfuncs.php');
    unset($_SESSION['main']);
    unset($_SESSION['sub']);
    
    use PeterBourneComms\CCA\User;
    $UO = new User();
    if (!is_object($UO)) {
        die();
    }
    
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        //This is an edit - take all variable from the SESSION
    } else {
        unset($_SESSION['PostedForm']);
    }

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
                    <p>//TODO TEXT REQUIRED HERE to explain about vetting, process etc.</p>
                    <form action="./signupExec.php" name="login" enctype="multipart/form-data" method="post" class="standard">
                        <div class='grid-x grid-margin-x'>
                            <div class='medium-6 cell'>
                                <h2>Your details</h2>
                                <?php if (isset($_SESSION['titleerror'])) {
                                    echo $_SESSION['titleerror'];
                                    unset($_SESSION['titleerror']);
                                } ?>
                                <p>
                                    <label for="Title">Title:</label><select name='Title' id='Title'>
                                        <?php
                                            echo "<option value=''";
                                            if ($_SESSION['PostedForm']['Title'] ?? '' == '') {
                                                echo " selected ='selected'";
                                            }
                                            echo ">Please select...</option>";
                                            $TitleOptions = $UO->getTitleOptions();
                                            if (is_array($TitleOptions)) {
                                                foreach($TitleOptions as $Option) {
                                                    echo "<option value='".$Option['Value']."'";
                                                    if ($_SESSION['PostedForm']['Title'] ?? '' == $Option['Value']) {
                                                        echo " selected ='selected'";
                                                    }
                                                    echo ">".$Option['Label']."</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </p>
                                <?php if (isset($_SESSION['firstnameerror'])) {
                                    echo $_SESSION['firstnameerror'];
                                    unset($_SESSION['firstnameerror']);
                                } ?>
                                <p>
                                    <label for="Firstname">Firstname:</label><input type="text" name="Firstname" id="Firstname" value="<?php echo check_output($_SESSION['PostedForm']['Firstname'] ?? ''); ?>" placeholder="Firstname"/>
                                </p>
                                <?php if (isset($_SESSION['surnameerror'])) {
                                    echo $_SESSION['surnameerror'];
                                    unset($_SESSION['surnameerror']);
                                } ?>
                                <p>
                                    <label for="Surname">Surname:</label><input type="text" name="Surname" id="Surname" value="<?php echo check_output($_SESSION['PostedForm']['Surname'] ?? ''); ?>" placeholder="Surname"/>
                                </p>
                                <?php if (isset($_SESSION['mobileerror'])) {
                                    echo $_SESSION['mobileerror'];
                                    unset($_SESSION['mobileerror']);
                                } ?>
                                <p>
                                    <label for="Mobile">Mobile:</label><input type="text" name="Mobile" id="Mobile" value="<?php echo check_output($_SESSION['PostedForm']['Mobile'] ?? ''); ?>" placeholder="Mobile"/>
                                </p>
                                <?php if (isset($_SESSION['emailerror'])) {
                                    echo $_SESSION['emailerror'];
                                    unset($_SESSION['emailerror']);
                                } ?>
                                <p>
                                    <label for="Email">Email address:</label><input type="email" name="Email" id="Email" value="<?php echo check_output($_SESSION['PostedForm']['Email'] ?? ''); ?>" placeholder="Email address"/>
                                </p>
                            </div>
                            <div class='medium-6 cell'>
                                <h2>Company information</h2>
                                <?php if (isset($_SESSION['companyerror'])) {
                                    echo $_SESSION['companyerror'];
                                    unset($_SESSION['companyerror']);
                                } ?>
                                <p>
                                    <label for="Company">Company:</label><input type="text" name="Company" id="Company" value="<?php echo check_output($_SESSION['PostedForm']['Company'] ?? ''); ?>" placeholder="Company"/>
                                </p>
                                <?php if (isset($_SESSION['addresserror'])) {
                                    echo $_SESSION['addresserror'];
                                    unset($_SESSION['addresserror']);
                                } ?>
                                <p>
                                    <label for="Address1">Address:</label><input type="text" name="Address1" id="Address1" value="<?php echo check_output($_SESSION['PostedForm']['Address1'] ?? ''); ?>" placeholder="Street"/>
                                    <input type="text" name="Address2" id="Address2" value="<?php echo check_output($_SESSION['PostedForm']['Address2'] ?? ''); ?>" placeholder=""/>
                                    <input type="text" name="Address3" id="Address3" value="<?php echo check_output($_SESSION['PostedForm']['Address3'] ?? ''); ?>" placeholder=""/>
                                    <input type="text" name="Town" id="Town" value="<?php echo check_output($_SESSION['PostedForm']['Town'] ?? ''); ?>" placeholder="Town"/>
                                    <input type="text" name="County" id="County" value="<?php echo check_output($_SESSION['PostedForm']['County'] ?? ''); ?>" placeholder="County"/>
                                    <input type="text" name="Postcode" id="Postcode" value="<?php echo check_output($_SESSION['PostedForm']['Postcode'] ?? ''); ?>" placeholder="Postcode"/>
                                </p>
                                <?php if (isset($_SESSION['telerror'])) {
                                    echo $_SESSION['telerror'];
                                    unset($_SESSION['telerror']);
                                } ?>
                                <p>
                                    <label for="Tel">Accounts telephone:</label><input type="text" name="Tel" id="Tel" value="<?php echo check_output($_SESSION['PostedForm']['Tel'] ?? ''); ?>" placeholder="Landline"/>
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