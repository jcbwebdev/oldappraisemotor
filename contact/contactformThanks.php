<?php
    include('../assets/dbfuncs.php');
    $_SESSION['main'] = '16';
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
                <h2>Thank you</h2>
                <p>Thanks for your message - we'll be back in touch soon.</p>
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