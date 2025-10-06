<?php
	include('../assets/dbfuncs.php');

    use PeterBourneComms\CMS\PBEmail;

	#1. Collect the form variables
	#2. Check for magic quotes addslashes/stripslashes accordingly
	#3. Check form for errors
	#4. Mail

//---------------------1. Collect the form variables-------------------------------->
	$FullName							= $_POST['FullName'] ?? null;
	$Telephone 							= $_POST['Telephone'] ?? null;
	$Email 								= $_POST['Email'] ?? null;
	$MessageBody						= $_POST['MessageBody'] ?? null;
	$KeepInformed						= $_POST['KeepInformed'] ?? null;

	$_SESSION['PostedForm'] = $_POST;

//-------------------------------------3. Check form for errors------------------------------------>
	if ( $FullName == '' || $Telephone == '' || !ValidEmail($Email) || ($_SESSION['SiteSettings']['G_RecaptchaSecret'] != '' && !isValidSubmission()))
	{
		if ( $FullName == '' ) { $_SESSION['nameerror'] = "<p class=\"error\">Please enter your Name:</p>"; }
		if ( $Telephone == '' ) { $_SESSION['numbererror'] = "<p class=\"error\">Please enter your telephone number:</p>"; }
		if ( !ValidEmail($Email)) { $_SESSION['emailerror'] = "<p class=\"error\">Please check your email address exists and is valid:</p>"; }
		#if ( $Address == '' ) { $_SESSION['addresserror'] = "<p class=\"error\">Please enter your address:</p>"; }
		#if ( $HearAbout == '' ) { $_SESSION['hearabouterror'] = "<p class=\"error\">Please tell us how you heard about us:</p>"; }
        if ($_SESSION['SiteSettings']['G_RecaptchaSecret'] != '' && !isValidSubmission()) { $_SESSION['captchaerror'] = "<p class='error'>Please show that you are a human by checking the box!</p>"; }


        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"You have some errors in the form, please could you attend to any highlighted error messages.");
		header("Location: /contact/");
		exit;
	}



//-------------------------------------4. mail ------------------------------------>

	$body = "<h2>The following is a request for further information, generated from the ".SITENAME." website.</h2>";
	$body = $body."<p>Name: ".$FullName."<br />";
	$body = $body."Telephone: ".$Telephone."<br />";
	$body = $body."Email: ".$Email."</p>";
	$body = $body."<p>And they had the following enquiry:<br />";
	$body = $body.nl2br($MessageBody)."<br/><br/>";
	if ($KeepInformed != 'Yes') { $KeepInformed = 'No'; }
	$body = $body."Keep details on record: ".$KeepInformed."</p>";
	$body = $body."<p>END OF EMAIL</p>";

	$text = "The following is a request for further information, generated from the ".SITENAME." website.\r\n\r\n";
	$text .= "Name: ".$FullName."\r\n";
	$text .= "Telephone: ".$Telephone."\r\n";
	$text .= "Email: ".$Email."\r\n\r\n";
    $text .= "And they had the following enquiry:\r\n\r\n";
    $text .= $MessageBody."\r\n\r\n";
    $text .= "Keep details on record:: ".$KeepInformed."\r\n\r\n";
    $text .= "END OF EMAIL"."\r\n\r\n";



    //Set up the email object
    $email = new PBEmail();
    $email->setRecipient($_SESSION['SiteSettings']['Email']);
    $email->setReplytoEmail($Email);
    $email->setSenderEmail(SITESENDEREMAIL);
    $email->setSenderName(FixOutput($_SESSION['SiteSettings']['Title']).' Website');
    $email->setSubject('Enquiry on the '.FixOutput($_SESSION['SiteSettings']['Title']).' website');
    $email->setHtmlMessage($body);
    $email->setTextMessage($text);
    $email->setTemplateFile(DOCUMENT_ROOT.'/emails/template.htm');

    //Send it
    $email->sendMail();

    //Send copy
    $email->setRecipient('hello@peterbourne.co.uk');
    $email->setSubject('COPY OF: Enquiry on the '.SITENAME.' website');
    $email->sendMail();

	//REMOVE SESSION VARIABLES
	unset($_SESSION['FullName']);
	unset($_SESSION['Email']);
	unset($_SESSION['Telephone']);
	unset($_SESSION['MessageBody']);
	unset($_SESSION['KeepInformed']);

	//MOVE USER ON
	header("Location: contactformThanks.php");