<?php
    use PeterBourneComms\HETA\User;
    use PeterBourneComms\HETA\Log;
    use PeterBourneComms\CMS\Token;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\PBEmail;

	include("../../assets/dbfuncs.php");
    
//---------------------1. Collect the form variables-------------------------------->

	$Email						= $_POST['Email'];

//-------------------------------------3. Check form for errors------------------------------------>
	if ( !ValidEmail($Email) )
	{
        $_SESSION['Message'] = array('Type'=>'error','Message'=>"Please ensure that you enter your email address.");
		header("Location: ./");
		exit;
	}

//-------------------------------------4. update DB and email ------------------------------------>

	
	$UO = new User();
	if (!is_object($UO)) { die(); }

    $TO = new Token();
	if (!is_object($TO)) { die(); }

	$token = $TO->createToken(100);


	//Retrieve Email
    $Users = $UO->listAllItems($Email,'email');
	if (is_array($Users) && count($Users) == 1)
	{
	    $User = $Users[0];

        //Check token doesn't exist already
        $DO = new Database();
        $dbconn = $DO->getConnection();

        $sql = "SELECT ID FROM PasswordResets WHERE Token = :token";
        $stmt = $dbconn->prepare($sql);
        $stmt->execute([
            'token' => $token
        ]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item['ID'] > 0)
        {
            //Need to fail here and try again
            $_SESSION['Message'] = array('Type'=>'error','Message'=>"Sorry - there was a problem resetting your password. Please check the email address matches your account or <a href='/contact/'>contact us</a>.");
            header("Location: ./");
            exit;
        }
        else
        {
            //Store our token
            $stmt = $dbconn->prepare("INSERT INTO PasswordResets SET UserID = :userid, Token = :token, Requested = NOW()");
            $stmt->execute([
                            'userid' => $User['ID'],
                            'token' => $token
            ]);

            //Now send the URL token through...
            $body = "<h3>Password reset request</h3>";
            $body .= "<p>You have requested a new password for the HETA website. You will need to follow the link below within 24 hours to reset your password. If you do not reset your password within 24 hours this link will expire and you will need to request a new one.</p>\n";
            $body .="<p><a href='https://".SITEFQDN."/my-details/passwordreset/passwordreset.php?t=".$token."'>Click here to reset your password &gt;</a></p>";
            
            $text = "PASSWORD RESET REQUEST\r\n\r\n";
            $text .= "You have requested a new password for the HETA website. You will need to follow the link below within 24 hours to reset your password. If you do not reset your password within 24 hours this link will expire and you will need to request a new one.\r\n\r\n";
            $text .= "Copy this link into your browser:\r\n\r\n";
            $text .= "https://".SITEFQDN."/my-details/passwordreset/passwordreset.php?t=".$token."\r\n\r\n";

            //Set up the email object
            $email = new PBEmail();
            $email->setRecipient($User['Email']);
            $email->setSenderEmail(SITESENDEREMAIL);
            $email->setSenderName(SITENAME);
            $email->setSubject('Reset your password on the '.SITENAME.' website');
            $email->setHtmlMessage($body);
            $email->setTextMessage($text);
            $email->setTemplateFile(DOCUMENT_ROOT.'/emails/template.htm');

            //Send it
            $result = $email->sendMail();

            if ($result === true) {
                $LO = new Log();
                if (is_object($LO)) {
                    $LO->setUserId($_SESSION['UserDetails']['ID']);
                    $LO->setUserName($_SESSION['UserDetails']['FullName']);
                    $LO->setAction('PASSWORD RESET REQUEST');
                    $LO->setDetail('Password Reset Request sent out to: '.$User['Firstname']." ".$User['Surname']);
                    $LO->saveItem();
                }
                
                $_SESSION['Message'] = array('Type'=>'success','Message'=>"Please check your email for details of how to reset your password. Be sure to check your junk email folder and ensure any further emails from HETA are set as ‘safe’.");
            } else {
                $_SESSION['Message'] = array('Type'=>'error','Message'=>"Sorry - there was a problem resetting your password. Please check the email address matches your account or <a href='/contact/'>contact us</a>.");
            }

            //REMOVE SESSION VARIABLES
            unset($_SESSION['Email']);

            //MOVE USER ON
            header("Location: /");
        }
	}
	else
    {
        //Fail - too many - or none returned
        $_SESSION['Message'] = array('Type'=>'error','Message'=>"Sorry - there was a problem resetting your password. Please check the email address matches your account or <a href='/contact'>contact us</a>.");
        header("Location: ./");
    }
