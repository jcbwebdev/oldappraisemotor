<?php
    include('../assets/dbfuncs.php');
    $_SESSION['main'] = '6';
    unset($_SESSION['sub']);

    include(DOCUMENT_ROOT . '/assets/inc-page_start.php');
?>
    <title>Contact <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='grid-x map-contact'>
            <div class='medium-4 cell'>
                <div class='address-panel space-above'>
                    <?php
                        echo "<p><strong>" . $_SESSION['SiteSettings']['Title'] . "</strong><br/>";
                        if ($_SESSION['SiteSettings']['Address1'] != '') {
                            echo $_SESSION['SiteSettings']['Address1'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['Address2'] != '') {
                            echo $_SESSION['SiteSettings']['Address2'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['Address3'] != '') {
                            echo $_SESSION['SiteSettings']['Address3'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['Town'] != '') {
                            echo $_SESSION['SiteSettings']['Town'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['County'] != '') {
                            echo $_SESSION['SiteSettings']['County'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['Postcode'] != '') {
                            echo $_SESSION['SiteSettings']['Postcode'];
                        }
                        echo "</p><p>";
                        if ($_SESSION['SiteSettings']['Telephone'] != '') {
                            echo "<span class='contact-tel'>" . $_SESSION['SiteSettings']['Telephone'] . "</span><br/>";
                        }
                        if ($_SESSION['SiteSettings']['Mobile'] != '') {
                            echo $_SESSION['SiteSettings']['Mobile'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['Email'] != '') {
                            echo "<a class='contact-email' href='mailto:" . $_SESSION['SiteSettings']['Email'] . "'>" . $_SESSION['SiteSettings']['Email'] . "</a>";
                        }
                        echo "</p>";
                    ?>
                </div>
            </div>
            <div class='medium-8 small-order-1 medium-order-2 cell'>
                <h1>Contact us</h1>
                <p>To contact us via e-mail complete the form below. Alternatively, to speak to us directly please use the phone number shown on this page.</p>
                <form action="contactformExec.php" method="post" enctype="multipart/form-data" name="form1" id="form1" class="standard">
                    <?php if (isset($_SESSION['nameerror'])) {
                        echo $_SESSION['nameerror'];
                        unset($_SESSION['nameerror']);
                    } ?>
                    <p>
                        <label for="FullName">Name:</label><input type="text" name="FullName" id="FullName" value="<?php echo check_output($_SESSION['FullName'] ?? null); ?>"/>
                    </p>
                    <?php if (isset($_SESSION['numbererror'])) {
                        echo $_SESSION['numbererror'];
                        unset($_SESSION['numbererror']);
                    } ?>
                    <p>
                        <label for="Telephone">Contact number:</label><input type="text" name="Telephone" id="Telephone" value="<?php echo check_output($_SESSION['Telephone'] ?? null); ?>"/>
                    </p>
                    <p>
                        <label for="Email">Email:</label><input type="email" name="Email" id="Email" value="<?php echo check_output($_SESSION['Email'] ?? null); ?>"/>
                    </p>
                    <p>
                        <label for="MessageBody">Message:</label><textarea name="MessageBody" id="MessageBody" rows="4"><?php echo check_output($_SESSION['MessageBody'] ?? null); ?></textarea>
                    </p>

                    <p>You can keep my details on my for future contact - in line with our
                        <a href='/privacy-policy-cookies' target='_blank'>Privacy and GDPR policy</a>.</p>
                    <div class='switch tiny'>
                        <input class='switch-input' id='KeepInformed' type='checkbox' name='KeepInformed' value='Yes'>
                        <label class='switch-paddle' for='KeepInformed'>
                            <span class='show-for-sr'>Keep in touch?</span>
                            <span class='switch-active' aria-hidden='true'>Yes</span>
                            <span class='switch-inactive' aria-hidden='true'>No</span>
                        </label>
                    </div>
                    <?php if (isset($_SESSION['captchaerror'])) {
                        echo $_SESSION['captchaerror'];
                        unset($_SESSION['captchaerror']);
                    } ?>
                    <?php
                        if ($_SESSION['SiteSettings']['G_RecaptchaSite'] != '') {
                            echo "<div class='g-recaptcha' data-sitekey='" . $_SESSION['SiteSettings']['G_RecaptchaSite'] . "'></div>\n";
                        }
                    ?>
                    <p>
                        <button class="button" name="submit" type="submit">Submit</button>
                    </p>
                </form>
                <?php
                    if ($_SESSION['SiteSettings']['RegNumber'] != '') {
                        echo "<div class='callout light-gray reg-details'>";
                        echo "<p>Company registered in " . $_SESSION['SiteSettings']['RegJurisdiction'] . ", number: <strong>" . $_SESSION['SiteSettings']['RegNumber'] . "</strong></p><p><em>Registered Office:</em><br/>";
                        if ($_SESSION['SiteSettings']['RegAddress1'] != '') {
                            echo $_SESSION['SiteSettings']['RegAddress1'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['RegAddress2'] != '') {
                            echo $_SESSION['SiteSettings']['RegAddress2'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['RegAddress3'] != '') {
                            echo $_SESSION['SiteSettings']['RegAddress3'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['RegTown'] != '') {
                            echo $_SESSION['SiteSettings']['RegTown'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['RegCounty'] != '') {
                            echo $_SESSION['SiteSettings']['RegCounty'] . "<br/>";
                        }
                        if ($_SESSION['SiteSettings']['RegPostcode'] != '') {
                            echo $_SESSION['SiteSettings']['RegPostcode'];
                        }
                        echo "</p>";
                        echo "</div>";
                    }
                ?>
            </div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT . '/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_end.php'); ?>