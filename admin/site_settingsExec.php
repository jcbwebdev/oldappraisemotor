<?php
	include("../assets/dbfuncs.php");
	checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    if ($_SESSION['UserDetails']['ID'] != 1) { exit; }

    use PeterBourneComms\CMS\SiteSettings;


//---------------------1. Collect form variables -------------------------------->
	$ID                         = 1;

    $Title						= $_POST['Title'] ?? null;
	$FQDN                       = $_POST['FQDN'] ?? null;
	$ImgPath                    = $_POST['ImgPath'] ?? null;
	$OldImgFilename				= $_POST['OldImgFilename'] ?? null;
	$PrimaryColour              = $_POST['PrimaryColour'] ?? null;
	$SecondaryColour            = $_POST['SecondaryColour'] ?? null;
	$Strapline                  = $_POST['Strapline'] ?? null;
	$Telephone                  = $_POST['Telephone'] ?? null;
	$Mobile                     = $_POST['Mobile'] ?? null;
	$Email                      = $_POST['Email'] ?? null;
	$Address1                   = $_POST['Address1'] ?? null;
	$Address2                   = $_POST['Address2'] ?? null;
	$Address3                   = $_POST['Address3'] ?? null;
	$Town                       = $_POST['Town'] ?? null;
	$County                     = $_POST['County'] ?? null;
	$Postcode                   = $_POST['Postcode'] ?? null;
	$RegAddress1                = $_POST['RegAddress1'] ?? null;
    $RegAddress2                = $_POST['RegAddress2'] ?? null;
    $RegAddress3                = $_POST['RegAddress3'] ?? null;
    $RegTown                    = $_POST['RegTown'] ?? null;
    $RegCounty                  = $_POST['RegCounty'] ?? null;
    $RegPostcode                = $_POST['RegPostcode'] ?? null;
    $RegNumber                  = $_POST['RegNumber'] ?? null;
    $RegJurisdiction            = $_POST['RegJurisdiction'] ?? null;
    $Social_Facebook            = $_POST['Social_Facebook'] ?? null;
    $Social_LinkedIn            = $_POST['Social_LinkedIn'] ?? null;
    $Social_Twitter             = $_POST['Social_Twitter'] ?? null;
    $Social_Pinterest           = $_POST['Social_Pinterest'] ?? null;
    $Social_Instagram           = $_POST['Social_Instagram'] ?? null;
    $Social_Google              = $_POST['Social_Google'] ?? null;

    $AddThisCode                = $_POST['AddThisCode'] ?? null;
    $GA_Code                    = $_POST['GA_Code'] ?? null;
    $G_RecaptchaSite            = $_POST['G_RecaptchaSite'] ?? null;
    $G_RecaptchaSecret          = $_POST['G_RecaptchaSecret'] ?? null;

    $EnableMap                  = $_POST['EnableMap'] ?? null;
    $MapEmbed                   = $_POST['MapEmbed'] ?? null;

    $DefaultMetaDesc			= $_POST['DefaultMetaDesc'] ?? null;
	$DefaultMetaKey				= $_POST['DefaultMetaKey'] ?? null;

    $state = $_GET['state'] ?? null;
    $_SESSION['PostedForm']     = $_POST;


//-------------------------------------2. Check form for errors------------------------------------>

	if ( $Title == '' || $FQDN == '' )
	{
		if ( $Title == '' ) { $_SESSION['titleerror'] = "<p class='error'>Please enter the site title:</p>"; }
		if ( $FQDN == '' ) { $_SESSION['fqdnerror'] = "<p class='error'>Please enter the site FQDN:</p>"; }

		$_SESSION['Message'] = array('Type'=>'error','Message'=>"You have some errors in your information, please could you attend to any highlighted error messages.");
		$_SESSION['error'] = true;
		header("Location:site_settings.php");
		exit;
	}

//-------------------------------------3. Update object and database  ------------------------------------>

    //Populate some defaults - if nothing supplied
    $FQDN = addHttp($FQDN);
	$Social_Facebook = addHttp($Social_Facebook);
	$Social_Twitter = addHttp($Social_Twitter);
	$Social_LinkedIn = addHttp($Social_LinkedIn);
	$Social_Google = addHttp($Social_Google);
	$Social_Pinterest = addHttp($Social_Pinterest);
	$Social_Instagram = addHttp($Social_Instagram);

    //Create a new Content object to populate and update/save
    $SSO = new SiteSettings($ID, 400, 200, USER_UPLOADS.'/images/');

    //Now populate the items
    $SSO->setTitle($Title);
    $SSO->setFQDN($FQDN);
    $SSO->setPrimaryColour($PrimaryColour);
    $SSO->setSecondaryColour($SecondaryColour);
    $SSO->setStrapline($Strapline);

    $SSO->setTelephone($Telephone);
    $SSO->setMobile($Mobile);
    $SSO->setEmail($Email);
    $SSO->setAddress1($Address1);
    $SSO->setAddress2($Address2);
    $SSO->setAddress3($Address3);
    $SSO->setTown($Town);
    $SSO->setCounty($County);
    $SSO->setPostcode($Postcode);
    $SSO->setRegNumber($RegNumber);
    $SSO->setRegJurisdiction($RegJurisdiction);
    $SSO->setRegAddress1($RegAddress1);
    $SSO->setRegAddress2($RegAddress2);
    $SSO->setRegAddress3($RegAddress3);
    $SSO->setRegTown($RegTown);
    $SSO->setRegCounty($RegCounty);
    $SSO->setRegPostcode($RegPostcode);

    $SSO->setSocialFacebook($Social_Facebook);
    $SSO->setSocialLinkedIn($Social_LinkedIn);
    $SSO->setSocialTwitter($Social_Twitter);
    $SSO->setSocialPinterest($Social_Pinterest);
    $SSO->setSocialInstagram($Social_Instagram);
    $SSO->setSocialGoogle($Social_Google);

    $SSO->setAddThisCode($AddThisCode);
    $SSO->setGaCode($GA_Code);
    $SSO->setGRecaptchaSecret($G_RecaptchaSecret);
    $SSO->setGRecaptchaSite($G_RecaptchaSite);

    $SSO->setEnableMap($EnableMap);
    $SSO->setMapEmbed($MapEmbed);

    $SSO->setDefaultMetaDesc($DefaultMetaDesc);
    $SSO->setDefaultMetaKey($DefaultMetaKey);

    //Image
    if (isset($_POST['ImgFile']))
    {
        $SSO->uploadImage($_POST['ImgFile']);
    }


    //Now save the item
    $result = $SSO->saveItem();


//-------------------------------------4. Move on and clean up session vars ------------------------------------>

    if ($result === true)
    {
        unset($_SESSION['PostedForm']);

        //Reset the session info
        $SSO2 = new SiteSettings(1);
        $SS = $SSO2->getItem();

        $_SESSION['SiteSettings'] = $SS;

        $_SESSION['Message'] = array('Type'=>'success','Message'=>"Site settings have been updated");
        header("Location: /admin/");
    }
    else
    {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: site_settings.php?state=edit");
    }
    exit;