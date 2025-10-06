            <div class='footer-container'>
                <!--Footer content too?-->
                <div class='grid-container'>
                    <h3>Contact us</h3>
                    <div class='grid-x grid-margin-x'>
                        <div class='medium-5 cell tel-and-email'>
                            <p>
                                <span class='item-label'>Tel: </span><span class='item-detail'><a href='tel:<?php echo $_SESSION['SiteSettings']['Telephone']; ?>' target='_blank'><?php echo $_SESSION['SiteSettings']['Telephone']; ?></a></span>
                            </p>
                            <p>
                                <span class='item-label'>Email: </span><span class='item-detail'><a href='mailto:<?php echo $_SESSION['SiteSettings']['Email']; ?>' target='_blank'><?php echo $_SESSION['SiteSettings']['Email']; ?></a></span>
                            </p>
                        </div>
                        <div class='medium-7 cell address-content'>
                            <p><strong>Address</strong><br/>
                            Unit 7 Rink Drive<br/>
                            Rinkway Business Park<br/>
                            Swadlincote<br/>
                            Derbyshire<br/>
                            DE11 8JL
                            </p>
                        </div>
                    </div>
                    <div class='grid-x grid-margin-x footer-admin-content'>
                        <div class='medium-8 cell'>
                            <p class="hide-for-print small-text-center medium-text-left">
                                <a href="/">Home</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/privacy-policy-cookies">Privacy Policy &amp; Cookies</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/sitemap/">Site map</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/admin/">Admin</a><br/>&copy; <?php echo date('Y', time())." ".$sitename; ?>. All rights reserved.
                            </p>
                        </div>
                        <div class='medium-4 cell'>
                            <p class="hide-for-print small-text-center medium-text-right">
                                <a href="https://www.peterbourne.co.uk" target="_blank">Auction website design &amp; development by<br/>Peter Bourne Communications</a>
                            </p>
                        </div>
                    </div>
                </div><!-- end of grid container -->
            </div><!-- end of footer container -->
        </div><!-- end of sticky navanchor -->
    </div><!-- end of off-canvas (normal) content-->
</div><!-- end of wrapper / page container-->
<script src="/assets/js/app.min.js"></script>
<?php
    if (isset($_SESSION['main']) && $_SESSION['main'] == 'admin') {
        echo "<script src='/assets/js/app-admin.min.js'></script>\n";
    }
    
    if (isset($_SESSION['SiteSettings']['GA_Code']) && $_SESSION['SiteSettings']['GA_Code'] != '') {
        ?>
        <script async src='https://www.googletagmanager.com/gtag/js?id=<?php echo $_SESSION['SiteSettings']['GA_Code']; ?>'></script>
        <script >
            window.dataLayer = window.dataLayer || [];
            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '<?php echo $_SESSION['SiteSettings']['GA_Code']; ?>');
        </script>
        <?php
    }
    if (isset($_SESSION['SiteSettings']['G_RecaptchaSite']) && $_SESSION['SiteSettings']['G_RecaptchaSite'] != '') {
        echo "<script src='https://www.google.com/recaptcha/api.js'></script>\n";
    }
?>