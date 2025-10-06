<?php
    include("../assets/dbfuncs.php");
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

    use PeterBourneComms\CMS\SiteSettings;


    if ($_SESSION['UserDetails']['ID'] != 1) {
        exit;
    }

    $_SESSION['main'] = "admin";
    unset($_SESSION['sub']);

    if (isset($_SESSION['error'])) {
        //This is an edit - take all variable from the SESSION
    } else {
        $SSO = new SiteSettings();
        if (!is_object($SSO)) {
            die();
        }
        $SS = $SSO->getItem(1);

        if (is_array($SS) && count($SS) > 0) {
            $_SESSION['PostedForm']['Title'] = $SS['Title'];
            $_SESSION['PostedForm']['FQDN'] = $SS['FQDN'];
            $_SESSION['PostedForm']['ImgPath'] = $SS['ImgPath'];
            //$_SESSION['PostedForm']['ImgFilename'] = $SS['ImgFilename'];
            $_SESSION['PostedForm']['OldImgFilename'] = $SS['ImgFilename'];
            $_SESSION['PostedForm']['ImgPath'] = $SS['ImgPath'];
            $_SESSION['PostedForm']['PrimaryColour'] = $SS['PrimaryColour'];
            $_SESSION['PostedForm']['SecondaryColour'] = $SS['SecondaryColour'];
            $_SESSION['PostedForm']['Strapline'] = $SS['Strapline'];
            $_SESSION['PostedForm']['Telephone'] = $SS['Telephone'];
            $_SESSION['PostedForm']['Mobile'] = $SS['Mobile'];
            $_SESSION['PostedForm']['Email'] = $SS['Email'];
            $_SESSION['PostedForm']['Address1'] = $SS['Address1'];
            $_SESSION['PostedForm']['Address2'] = $SS['Address2'];
            $_SESSION['PostedForm']['Address3'] = $SS['Address3'];
            $_SESSION['PostedForm']['Town'] = $SS['Town'];
            $_SESSION['PostedForm']['County'] = $SS['County'];
            $_SESSION['PostedForm']['Postcode'] = $SS['Postcode'];
            $_SESSION['PostedForm']['RegNumber'] = $SS['RegNumber'];
            $_SESSION['PostedForm']['RegAddress1'] = $SS['RegAddress1'];
            $_SESSION['PostedForm']['RegAddress2'] = $SS['RegAddress2'];
            $_SESSION['PostedForm']['RegAddress3'] = $SS['RegAddress3'];
            $_SESSION['PostedForm']['RegTown'] = $SS['RegTown'];
            $_SESSION['PostedForm']['RegCounty'] = $SS['RegCounty'];
            $_SESSION['PostedForm']['RegPostcode'] = $SS['RegPostcode'];
            $_SESSION['PostedForm']['RegJurisdiction'] = $SS['RegJurisdiction'];
            $_SESSION['PostedForm']['Social_Facebook'] = $SS['Social_Facebook'];
            $_SESSION['PostedForm']['Social_LinkedIn'] = $SS['Social_LinkedIn'];
            $_SESSION['PostedForm']['Social_Twitter'] = $SS['Social_Twitter'];
            $_SESSION['PostedForm']['Social_Pinterest'] = $SS['Social_Pinterest'];
            $_SESSION['PostedForm']['Social_Instagram'] = $SS['Social_Instagram'];
            $_SESSION['PostedForm']['Social_Google'] = $SS['Social_Google'];
            $_SESSION['PostedForm']['AddThisCode'] = $SS['AddThisCode'];
            //$_SESSION['PostedForm']['EnableCTAPanels'] = $SS['EnableCTAPanels'];
            //$_SESSION['PostedForm']['EnableTestimonials'] = $SS['EnableTestimonials'];
            //$_SESSION['PostedForm']['EnableImageLibrary'] = $SS['EnableImageLibrary'];
            //$_SESSION['PostedForm']['EnableNews'] = $SS['EnableNews'];
            $_SESSION['PostedForm']['EnableMap'] = $SS['EnableMap'];
            $_SESSION['PostedForm']['MapEmbed'] = $SS['MapEmbed'];
            $_SESSION['PostedForm']['DateSetup'] = $SS['DateSetup'];
            $_SESSION['PostedForm']['DefaultMetaDesc'] = $SS['DefaultMetaDesc'];
            $_SESSION['PostedForm']['DefaultMetaKey'] = $SS['DefaultMetaKey'];
            //$_SESSION['PostedForm']['Template'] = $SS['Template'];
            $_SESSION['PostedForm']['G_RecaptchaSite'] = $SS['G_RecaptchaSite'];
            $_SESSION['PostedForm']['G_RecaptchaSecret'] = $SS['G_RecaptchaSecret'];
            $_SESSION['PostedForm']['GA_Code'] = $SS['GA_Code'];
        } else {
            header("Location:/admin/");
            exit;
        }
    }

    unset($_SESSION['error']);

    include(DOCUMENT_ROOT . '/assets/inc-page_start.php');
?>
    <title>Homepage | Admin | <?php echo $sitetitle; ?></title>
    <link rel="stylesheet" media="screen" type="text/css" href="/vendor/colorpicker/css/colorpicker.css"/>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_head.php'); ?>
    <div class='grid-container'>
        <h1>Site Settings</h1>
        <h3>Not all settings are relevant to this site - USE WITH CARE!</h3>
        <form action="site_settingsExec.php" enctype="multipart/form-data" method="post" name="form1" id="form1" class="standard">

            <ul class="tabs" data-tabs id="site-settings">
                <li class="tabs-title is-active">
                    <a data-tabs-target="panel1" href="#panel1" aria-selected="true">General</a></li>
                <!--                        <li class="tabs-title"><a data-tabs-target="panel2" href="#panel2">Styling</a></li>-->
                <li class="tabs-title"><a data-tabs-target="panel3" href="#panel3">Contact</a></li>
                <li class="tabs-title"><a data-tabs-target="panel4" href="#panel4">SEO &amp; Social</a></li>
                <li class="tabs-title"><a data-tabs-target="panel5" href="#panel5">Advanced</a></li>
            </ul>

            <div class="tabs-content" data-tabs-content="site-settings">


                <div class="tabs-panel is-active" id="panel1">

                    <?php if (isset($_SESSION['titleerror'])) {
                        echo $_SESSION['titleerror'];
                        unset($_SESSION['titleerror']);
                    } ?>
                    <p>
                        <label for="Title">Title:</label><input name="Title" type="text" id="Title" value="<?php echo check_output($_SESSION['PostedForm']['Title'] ?? ''); ?>" onkeyUp='KeyCheck(this);'/>
                    </p>
                    <?php if (isset($_SESSION['fqdnerror'])) {
                        echo $_SESSION['fqdnerror'];
                        unset($_SESSION['fqdnerror']);
                    } ?>
                    <label for="FQDN">FQDN:</label>
                    <input name="FQDN" type="text" id="FQDN" placeholder='Include http://' value="<?php echo check_output($_SESSION['PostedForm']['FQDN'] ?? ''); ?>"/>
                    <p>
                        <label for="Strapline">Strap line:</label><input name="Strapline" type="text" id="Strapline" value="<?php echo check_output($_SESSION['PostedForm']['Strapline'] ?? ''); ?>"/>
                    </p>
                </div>


                <!--
                        <div class="tabs-panel" id="panel2">

                            <h2>Design</h2>
                            <p><label for='Template'>Template:</label><select name="Template" id="Template">
                                    <option value='DesignA' <?php if (isset($_SESSION['PostedForm']['Template']) && $_SESSION['PostedForm']['Template'] == 'DesignA') {
                    echo " selected='selected'";
                } ?>>Design A - Horizontal menu
                                    </option>
                                    <option value='DesignB' <?php if (isset($_SESSION['PostedForm']['Template']) && $_SESSION['PostedForm']['Template'] == 'DesignB') {
                    echo " selected='selected'";
                } ?>>Design B - Vertical menu
                                    </option>
                                </select></p>

                            <div class='grid-x grid-padding-x'>
                                <div class='medium-6 cell'>
                                    <h3>Colours</h3>
                                    <p>
                                        <label for="PrimaryColour">Primary colour:</label><input name="PrimaryColour" type="text" id="PrimaryColour" value="<?php echo check_output($_SESSION['PostedForm']['PrimaryColour']); ?>" />
                                    </p>
                                    <p>
                                        <label for="SecondaryColour">Secondary colour:</label><input name="SecondaryColour" type="text" id="SecondaryColour" value="<?php echo check_output($_SESSION['PostedForm']['SecondaryColour']); ?>" />
                                    </p>
                                </div>
                                <div class='medium-6 cell'>
                                    <h3>Logo</h3>
                                    <p class="help-text">Once you've dragged a logo on, you can crop to the desired size. You can only crop to the specified proportions.</p>
                                    <div id='logofile'></div>
                                </div>
                            </div>
                        </div>
-->

                <div class="tabs-panel" id="panel3">

                    <div class='callout secondary'>
                        <div class='grid-x grid-padding-x'>
                            <div class='medium-6 cell'>
                                <h3>Contact details</h3>
                                <p>
                                    <label for='Address1'>Street address:</label><input name="Address1" type="text" id="Address1" placeholder='Street address' value="<?php echo check_output($_SESSION['PostedForm']['Address1'] ?? ''); ?>"/>
                                    <input name="Address2" type="text" id="Address2" value="<?php echo check_output($_SESSION['PostedForm']['Address2'] ?? ''); ?>"/>
                                    <input name="Address3" type="text" id="Address3" value="<?php echo check_output($_SESSION['PostedForm']['Address3'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='Town'>Town:</label><input name="Town" type="text" id="Town" placeholder='Town' value="<?php echo check_output($_SESSION['PostedForm']['Town'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='County'>County:</label><input name="County" type="text" id="County" placeholder='County' value="<?php echo check_output($_SESSION['PostedForm']['County'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='Postcode'>Postcode:</label><input name="Postcode" type="text" id="Postcode" placeholder='Postcode' value="<?php echo check_output($_SESSION['PostedForm']['Postcode'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='Telephone'>Telephone:</label><input name="Telephone" type="text" id="Telephone" placeholder='Telephone' value="<?php echo check_output($_SESSION['PostedForm']['Telephone'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='Mobile'>Mobile:</label><input name="Mobile" type="text" id="Mobile" placeholder='Mobile' value="<?php echo check_output($_SESSION['PostedForm']['Mobile'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='Email'>Email:</label><input name="Email" type="email" id="Email" placeholder='Email' value="<?php echo check_output($_SESSION['PostedForm']['Email'] ?? ''); ?>"/>
                                </p>
                            </div>
                            <div class='medium-6 cell'>
                                <h3>Registered Office - if a Limited Company</h3>
                                <p>
                                    <label for='RegAddress1'>Street address:</label><input name="RegAddress1" type="text" id="RegAddress1" placeholder='Street address' value="<?php echo check_output($_SESSION['PostedForm']['RegAddress1'] ?? ''); ?>"/>
                                    <input name="RegAddress2" type="text" id="RegAddress2" value="<?php echo check_output($_SESSION['PostedForm']['RegAddress2'] ?? ''); ?>"/>
                                    <input name="RegAddress3" type="text" id="RegAddress3" value="<?php echo check_output($_SESSION['PostedForm']['RegAddress3'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='RegTown'>Town:</label><input name="RegTown" type="text" id="RegTown" placeholder='Town' value="<?php echo check_output($_SESSION['PostedForm']['RegTown'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='RegCounty'>County:</label><input name="RegCounty" type="text" id="RegCounty" placeholder='County' value="<?php echo check_output($_SESSION['PostedForm']['RegCounty'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='RegPostcode'>Postcode:</label><input name="RegPostcode" type="text" id="RegPostcode" placeholder='Postcode' value="<?php echo check_output($_SESSION['PostedForm']['RegPostcode'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='RegNumber'>Registered company number:</label><input name="RegNumber" type="text" id="RegNumber" placeholder='Company registration number from Companies House' value="<?php echo check_output($_SESSION['PostedForm']['RegNumber'] ?? ''); ?>"/>
                                </p>
                                <p>
                                    <label for='RegJurisdiction'>Jurisdiction:</label><select name="RegJurisdiction" id="RegJurisdiction">
                                        <option value='England and Wales' <?php if ($_SESSION['PostedForm']['RegJurisdiction'] == 'England and Wales') {
                                            echo " selected='selected'";
                                        } ?>>England and Wales
                                        </option>
                                        <option value='England' <?php if ($_SESSION['PostedForm']['RegJurisdiction'] == 'England') {
                                            echo " selected='selected'";
                                        } ?>>England
                                        </option>
                                    </select></p>
                                <p class='help-text'>Only use this Registered Office information if the business is a Limited Company and listed at Companies House.</p>
                            </div>
                        </div>
                    </div>

                    <div class='callout'>
                        <h3>Map</h3>
                        <div class='grid-x grid-padding-x'>
                            <div class='medium-6 cell'>
                                <label>Display map on contact page</label>
                                <div class="switch">
                                    <input class="switch-input" id="EnableMap" type="checkbox" name="EnableMap" value='Y' <?php if ($_SESSION['PostedForm']['EnableMap'] == 'Y') {
                                        echo " checked='checked'";
                                    } ?> />
                                    <label class="switch-paddle" for="EnableMap">
                                        <span class="show-for-sr">Enable embedded map</span>
                                        <span class="switch-active" aria-hidden="true">Yes</span>
                                        <span class="switch-inactive" aria-hidden="true">No</span>
                                    </label>
                                </div>
                            </div>
                            <div class='medium-6 cell'>
                                <label for='MapEmbed'>Google map embed code:</label><textarea id='MapEmbed' name='MapEmbed' style='width: 100%; height: 100px;'><?php echo check_output($_SESSION['PostedForm']['MapEmbed'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tabs-panel" id="panel4">

                    <div class='callout secondary'>
                        <h3>Google settings</h3>
                        <p>
                            <label for='GA_Code'>Analytics Tracking code:</label><input type='text' name='GA_Code' id='GA_Code' value='<?php echo check_output($_SESSION['PostedForm']['GA_Code'] ?? ''); ?>' placeholder='Format: ua-nnnnnnn'/>
                        </p>
                        <p>
                            <label for='G_RecaptchaSite'>Recaptcha site code:</label><input type='text' name='G_RecaptchaSite' id='G_RecaptchaSite' value='<?php echo check_output($_SESSION['PostedForm']['G_RecaptchaSite'] ?? ''); ?>' placeholder=''/>
                        </p>
                        <p>
                            <label for='G_RecaptchaSecret'>Recaptcha site secret:</label><input type='text' name='G_RecaptchaSecret' id='G_RecaptchaSecret' value='<?php echo check_output($_SESSION['PostedForm']['G_RecaptchaSecret'] ?? ''); ?>' placeholder=''/>
                        </p>
                    </div>


                    <div class="callout warning">
                        <h3>SEO</h3>
                        <label for="DefaultMetaDesc">Default Meta Description:</label><textarea id="DefaultMetaDesc" name="DefaultMetaDesc" style="overflow:auto; height: 70px;" placeholder="The hidden page description for SEO"><?php echo check_output($_SESSION['PostedForm']['DefaultMetaDesc'] ?? ''); ?></textarea>
                        <label for="DefaultMetaKey">Default Meta Keywords:</label><textarea id="DefaultMetaKey" name="DefaultMetaKey" style="overflow:auto; height: 50px;" placeholder="The hidden page key words for SEO"><?php echo check_output($_SESSION['PostedForm']['DefaultMetaKey'] ?? ''); ?></textarea>
                    </div>

                    <div class='callout primary'>
                        <h3>Social media links</h3>
                        <div class='grid-x grid-padding-x'>
                            <div class='small-2 medium-1 cell'>
                                <img src='/assets/img/icons/social_facebook.png' alt='Facebook'/>
                            </div>
                            <div class='small-10 medium-11 cell'>
                                <p>
                                    <label for='Social_Facebook'>Facebook:</label><input type='text' name='Social_Facebook' id='Social_Facebook' value='<?php echo check_output($_SESSION['PostedForm']['Social_Facebook'] ?? ''); ?>' placeholder='Include https://'/>
                                </p>
                            </div>
                        </div>
                        <div class='grid-x grid-padding-x'>
                            <div class='small-2 medium-1 cell'>
                                <img src='/assets/img/icons/social_twitter.png' alt='Twitter'/>
                            </div>
                            <div class='small-10 medium-11 cell'>
                                <p>
                                    <label for='Social_Twitter'>Twitter:</label><input type='text' name='Social_Twitter' id='Social_Twitter' value='<?php echo check_output($_SESSION['PostedForm']['Social_Twitter'] ?? ''); ?>' placeholder='Include https://'/>
                                </p>
                            </div>
                        </div>
                        <div class='grid-x grid-padding-x'>
                            <div class='small-2 medium-1 cell'>
                                <img src='/assets/img/icons/social_linkedin.png' alt='LinkedIn'/>
                            </div>
                            <div class='small-10 medium-11 cell'>
                                <p>
                                    <label for='Social_LinkedIn'>LinkedIn:</label><input type='text' name='Social_LinkedIn' id='Social_LinkedIn' value='<?php echo check_output($_SESSION['PostedForm']['Social_LinkedIn'] ?? ''); ?>' placeholder='Include https://'/>
                                </p>
                            </div>
                        </div>
                        <div class='grid-x grid-padding-x'>
                            <div class='small-2 medium-1 cell'>
                                <img src='/assets/img/icons/social_pinterest.png' alt='Pinterest'/>
                            </div>
                            <div class='small-10 medium-11 cell'>
                                <p>
                                    <label for='Social_Pinterest'>Pinterest:</label><input type='text' name='Social_Pinterest' id='Social_Pinterest' value='<?php echo check_output($_SESSION['PostedForm']['Social_Pinterest'] ?? ''); ?>' placeholder='Include https://'/>
                                </p>
                            </div>
                        </div>
                        <div class='grid-x grid-padding-x'>
                            <div class='small-2 medium-1 cell'>
                                <img src='/assets/img/icons/social_instagram.png' alt='Instagram'/>
                            </div>
                            <div class='small-10 medium-11 cell'>
                                <p>
                                    <label for='Social_Instagram'>Instagram:</label><input type='text' name='Social_Instagram' id='Social_Instagram' value='<?php echo check_output($_SESSION['PostedForm']['Social_Instagram'] ?? ''); ?>' placeholder='Include https://'/>
                                </p>
                            </div>
                        </div>
                        <div class='grid-x grid-padding-x'>
                            <div class='small-2 medium-1 cell'>
                                <img src='/assets/img/icons/social_googleplus.png' alt='GooglePlus'/>
                            </div>
                            <div class='small-10 medium-11 cell'>
                                <p>
                                    <label for='Social_Google'>Google Plus:</label><input type='text' name='Social_Google' id='Social_Google' value='<?php echo check_output($_SESSION['PostedForm']['Social_Google'] ?? ''); ?>' placeholder='Include https://'/>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tabs-panel" id="panel5">
                    <h3>Advanced options for higher level package(s)</h3>
                    <div class='grid-x grid-padding-x'>
                        <div class='medium-6 cell'>
                            <!--
                                    <div class='grid-x grid-padding-x small-up-2 medium-up-1 large-up-2'>
                                        <div class='cell'>
                                            <label>Call to Actions Panels</label>
                                            <div class="switch">
                                                <input class="switch-input" id="EnableCTAPanels" type="checkbox" name="EnableCTAPanels" value='Y' <?php if (isset($_SESSION['PostedForm']['EnableCTAPanels']) && $_SESSION['PostedForm']['EnableCTAPanels'] == 'Y') {
                                echo " checked='checked'";
                            } ?> />
                                                <label class="switch-paddle" for="EnableCTAPanels">
                                                    <span class="show-for-sr">Enable Call to Action panels</span>
                                                    <span class="switch-active" aria-hidden="true">Yes</span>
                                                    <span class="switch-inactive" aria-hidden="true">No</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class='cell'>
                                            <label>Testimonials</label>
                                            <div class="switch">
                                                <input class="switch-input" id="EnableTestimonials" type="checkbox" name="EnableTestimonials" value='Y' <?php if (isset($_SESSION['PostedForm']['EnableTestimonials']) && $_SESSION['PostedForm']['EnableTestimonials'] == 'Y') {
                                echo " checked='checked''";
                            } ?> />
                                                <label class="switch-paddle" for="EnableTestimonials">
                                                    <span class="show-for-sr">Enable Testimonials</span>
                                                    <span class="switch-active" aria-hidden="true">Yes</span>
                                                    <span class="switch-inactive" aria-hidden="true">No</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class='cell'>
                                            <label>Image library</label>
                                            <div class="switch">
                                                <input class="switch-input" id="EnableImageLibrary" type="checkbox" name="EnableImageLibrary" value='Y' <?php if (isset($_SESSION['PostedForm']['EnableImageLibrary']) && $_SESSION['PostedForm']['EnableImageLibrary'] == 'Y') {
                                echo " checked='checked''";
                            } ?> />
                                                <label class="switch-paddle" for="EnableImageLibrary">
                                                    <span class="show-for-sr">Enable Image Library</span>
                                                    <span class="switch-active" aria-hidden="true">Yes</span>
                                                    <span class="switch-inactive" aria-hidden="true">No</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class='cell'>
                                            <label>News</label>
                                            <div class="switch">
                                                <input class="switch-input" id="EnableNews" type="checkbox" name="EnableNews" value='Y' <?php if (isset($_SESSION['PostedForm']['EnableNews']) && $_SESSION['PostedForm']['EnableNews'] == 'Y') {
                                echo " checked='checked''";
                            } ?> />
                                                <label class="switch-paddle" for="EnableNews">
                                                    <span class="show-for-sr">Enable News</span>
                                                    <span class="switch-active" aria-hidden="true">Yes</span>
                                                    <span class="switch-inactive" aria-hidden="true">No</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    -->
                        </div>
                        <div class='medium-6 cell'>

                            <label for='AddThisCode'>AddThis Code:</label><textarea name='AddThisCode' id='AddThisCode' style='width:100%; height: 150px;'><?php echo check_output($_SESSION['PostedForm']['AddThisCode'] ?? ''); ?></textarea>

                        </div>
                    </div>
                </div>
            </div>

            <div class='callout success'>
                <h2>Save your changes</h2>
                <p class='lead'>Nothing is saved until you press the 'Save' button below:</p>
                <input type='hidden' id='ID' name='ID' value='1'/>
                <p>
                    <button class='button' type='submit' value='submit'>Save</button>
                </p>
            </div>
        </form>
        <p><a href="./"><i class="fi-eject"></i> Admin menu</a></p>
    </div>
<?php include(DOCUMENT_ROOT . '/assets/inc-body_end.php'); ?>
    <script src='/vendor/colorpicker/js/colorpicker.js'></script>
    <script src='/vendor/ckeditor/ckeditor.js'></script>
    <script type='text/javascript'>
        $(document).ready(function () {

            //Cropper init stuff
            var drop = new createImageCropper({
                imageContainer: 'logofile',
                dropTarget: 'drop-target',
                instruction: 'drop-instruction',
                loading: 'drop-loading',
                saveInstruction: 'drop-save-instruction',
                target: 'drop-target-img',
                elementToAnimate: 'current-drop',
                dropTargetButtons: 'drop-buttons',
                xRatio: 480,
                yRatio: 240,
                cWidth: 560,
                cHeight: 280,
                formName: 'form1',
                outputElem: 'ImgFile',
                origPath: '<?php echo FixOutput($_SESSION['PostedForm']['ImgPath'] ?? ''); ?>',
                origImg: '<?php echo FixOutput($_SESSION['PostedForm']['OldImgFilename'] ?? ''); ?>',
                deleteID: '<?php echo FixOutput($_SESSION['PostedForm']['ID'] ?? ''); ?>',
                dialogText: 'Are you sure you want to delete this logo?',
                thumbnails: 'N',
                restoreSize: 250,
                scriptToRun: '/admin/ajax/_imageHandler.php',
                contentType: 'logo'
            });

            $('#PrimaryColour').ColorPicker({
                livePreview: true,
                onChange: function (hsb, hex, rgb, el) {
                    $('#PrimaryColour').val(hex);
                },
                /*onSubmit: function(hsb, hex, rgb, el) {
                 $('#BGCol').val(hex);
                 $(this).ColorPickerHide();
                 },*/
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                }
            });
            $('#SecondaryColour').ColorPicker({
                livePreview: true,
                onChange: function (hsb, hex, rgb, el) {
                    $('#SecondaryColour').val(hex);
                },
                /*onSubmit: function(hsb, hex, rgb, el) {
                 $('#BGCol').val(hex);
                 $(this).ColorPickerHide();
                 },*/
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                }
            });

        });

        function CKupdate() {
            for (instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
                CKEDITOR.instances[instance].setData('');
            }
        }

    </script>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_end.php'); ?>