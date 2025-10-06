<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */
    
    use PeterBourneComms\CCA\User;
    use PeterBourneComms\CMS\Token;
    use PeterBourneComms\CMS\PBEmail;
    use PeterBourneComms\CMS\Database;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
        
        
        $ID = $_POST['id'] ?? null;
        
        if (!isset($ID) || clean_int($ID) <= 0 ) {
            $error = "No user details supplied";
        } else {
            $UO = new User();
            $TO = new Token();
            if (!is_object($UO) || !is_object($TO)) {
                $error = "Can not create objects";
            } else {
                $token = $TO->createToken(100);
                //Retrieve Email
                $User = $UO->getItemById($ID);
                if (is_array($User) && count($User) > 0)
                {
                    //Check token doesn't exist already
                    $DO = new Database();
                    $dbconn = $DO->getConnection();
                    
                    $sql = "SELECT ID FROM PasswordResets WHERE Token = :token";
                    $stmt = $dbconn->prepare($sql);
                    $stmt->execute([
                        'token' => $token
                    ]);
                    $item = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (is_numeric($item) && $item['ID'] > 0) {
                        $error = "There was a problem resetting your password. Please check the email address matches an account";
                    } else {
                        //Store our token
                        $stmt = $dbconn->prepare("INSERT INTO PasswordResets SET UserID = :userid, Token = :token, Requested = NOW()");
                        $stmt->execute([
                            'userid' => $User['ID'],
                            'token' => $token
                        ]);
                        
                        //Now send the URL token through...
                        $body = "<h3>Password reset request</h3>";
                        $body .= "<p>You have requested a new password for the ".$sitename." website. You will need to follow the link below within 24 hours to reset your password. If you do not reset your password within 24 hours this link will expire and you will need to request a new one.</p>\n";
                        $body .="<p><a href='https://".SITEFQDN."/my-details/reset-password/?t=".$token."'>Click here to reset your password &gt;</a></p>";
                        
                        $text = "PASSWORD RESET REQUEST\r\n\r\n";
                        $text .= "You have requested a new password for the ".$sitename." website. You will need to follow the link below within 24 hours to reset your password. If you do not reset your password within 24 hours this link will expire and you will need to request a new one.\r\n\r\n";
                        $text .= "Copy this link into your browser:\r\n\r\n";
                        $text .= "https://".SITEFQDN."/my-details/reset-password/?t=".$token."\r\n\r\n";
                        
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
                        
                        if ($result != true) {
                            $error = "Could not send email";
                        }
                    }
                } else {
                    $error = "Could not find the user";
                }
            }
        }
        
        
        
        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true);
        }
    
        echo json_encode($retdata);
    }