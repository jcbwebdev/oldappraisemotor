<?php
    use PeterBourneComms\HETA\User;
    use PeterBourneComms\HETA\Log;
    use PeterBourneComms\CMS\Database;

	include("../../assets/dbfuncs.php");

//---------------------Collect the form variables-------------------------------->

	$Password1					= $_POST['Password1'];
	$Password2					= $_POST['Password2'];
	$token						= $_POST['token'];

//-------------------------------------3. Check form for errors------------------------------------>

    $UO = new User();
    if (!is_object($UO)) { die(); }
    
    $passwordsecure = $UO->checkPasswordSecurity($Password1);
    
	if ( $Password1 == '' || $Password1 != $Password2 || !$passwordsecure )
	{
		if ( $Password1 == '' || $Password1 != $Password2 ) { $_SESSION['passworderror'] = "<p class=\"error\">Please enter your password in both boxes:</p>"; }
        if ( !$passwordsecure) { $_SESSION['passworderror'] = "<p class=\"error\">Your password needs to be at least 8 characters long, and contain at least one: capital, number and other character</p>"; }
		$_SESSION['Message'] = array('Type'=>'error','Message'=>"You have some errors in your information, please could you attend to any highlighted error messages.");
		header("Location:passwordreset.php?t=$token");
		exit;
	}

	//Double check the token hasn't expired
    $DO = new Database();
    $dbconn = $DO->getConnection();

	$stmt = $dbconn->prepare("SELECT PasswordResets.ID, Users.ID AS UserID FROM PasswordResets LEFT JOIN Users ON Users.ID = PasswordResets.UserID WHERE PasswordResets.Token = :token AND NOW() <= DATE_ADD(PasswordResets.Requested, INTERVAL 1 DAY) LIMIT 1");
    $stmt->execute([
        'token' => $token
    ]);
	$item = $stmt->fetch();

	if ($item['ID'] > 0)
	{
		//Store password
        $UO = new User($item['UserID']);
        if (!is_object($UO)) { die(); }

        $UO->setPassword($Password1);
        $result = $UO->saveItem();

        //Now delete the token from the DB
		$stmt = $dbconn->prepare("DELETE FROM PasswordResets WHERE ID = :tokenid LIMIT 1");
		$stmt->execute([
		    'tokenid' => $item['ID']
        ]);
	}

	unset($_SESSION['Email']);
	unset($_SESSION['Password1']);
	unset($_SESSION['Password2']);
	unset($_SESSION['token']);

	//MOVE USER ON
	if ($result === true)
	{
        $LO = new Log();
        if (is_object($LO)) {
            $LO->setUserId($_SESSION['UserDetails']['ID']);
            $LO->setUserName($_SESSION['UserDetails']['FullName']);
            $LO->setAction('PASSWORD RESET');
            $LO->setDetail('Reset password for: '.$UO->getFirstname()." ".$UO->getSurname());
            $LO->saveItem();
        }
        
		$_SESSION['Message'] = array('Type'=>'success','Message'=>"Your password has been successfully reset. <strong>You can now log in with your new password.</strong>");
		header("Location: /login/");
	}
	else
	{
        $_SESSION['Message'] = array('Type'=>'error','Message'=>"Sorry - there was a problem updating your details - please try again.");
		header("Location: /");
	}