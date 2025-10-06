<?php
    include("../assets/dbfuncs.php");

    use PeterBourneComms\CCA\User;
    use PeterBourneComms\CCA\Customer;

    //Deal with any referrer information
    $referrer = urldecode($_POST['referrer']) ?? null;

    $Username = $_POST['Email'] ?? null;
    $Password = $_POST['Password'] ?? null;

    //Check that some data was passed
    if ($Username == '' || $Password == '') {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"You need to supply your email address and a password");
        header("Location:/login/");
        exit;
    }

    //Set up redirect location
    if ($referrer != '') {
        $nextloc = $referrer;
    }  else  {
        $nextloc = "/";
    }

    //Do we check the MemberDetails table OR the Parents table?
    //If its an email - its a MemberDetails
    if (filter_var( $Username, FILTER_VALIDATE_EMAIL )) {
        //Email - check Parents
        $UO = new User();
        $CO = new Customer();
        
        $PassResult = $UO->checkPassword($Username,$Password);
        if ($PassResult['Success'] == true)
        {
            //Success - they are a parent who we can login
            //Retrieve further info
            $User = $UO->getItemById($PassResult['ID']);
            if (is_array($User) && count($User) > 0) {
                $Customer = $CO->getItemById($User['CustomerID']);
            }
            
            
            //Update last logged in
            try {
                $UO->updateLastLoggedIn();
            } catch (Exception $e) {
                error_log('loginExec.php -> Could not update lastLogged In for User record: '.$e);
            }

            //Store additional stuff in the session
            $_SESSION['UserDetails']['Authenticated'] = true;
            $_SESSION['UserDetails']['ID'] = $User['ID'];
            $_SESSION['UserDetails']['FullName'] = $User['Firstname']." ".$User['Surname'];
            $_SESSION['UserDetails']['Email'] = $User['Email'];
            // $_SESSION['UserDetails']['Organisation'] = $User['Organisation'];
            $_SESSION['UserDetails']['CustomerID'] = $User['CustomerID'];
            $_SESSION['UserDetails']['Company'] = $Customer['Company'];
            

            //Admin?
            echo('here');
            var_dump($User['AdminLevel']);
            exit();
            $AdminLevel = $User['AdminLevel'] ?? null;
            // if ($AdminLevel != 'N' && $AdminLevel != '')
            if ($AdminLevel == null)
            {
                $_SESSION['UserDetails']['AdminLevel'] = $AdminLevel;
                $nextloc = "/admin/";
            }

            //Set the message
            $_SESSION['Message'] = array('Type'=>'success','Message'=>"Welcome back <strong>".$_SESSION['UserDetails']['FullName']."</strong>. You are now logged into the website.");
            header("Location:".$nextloc);
        }
        elseif ($PassResult['Success'] != true && (isset($PassResult['ID']) && $PassResult['ID'] >= 0))
        {
            //Found parent, password matched - but Membership disabled
            $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - your account has been disabled. <a href='/contact/'>Please contact us</a> to rectify this, if you believe it to be in error.");
            header("Location:".$nextloc);
        }
        else
        {
            //No member/password match found
            $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - we could not find your records with the details you entered. You may wish to try again - check you password is entered correctly (case sensitive!) and check your Email address is the one you originally registered on the website with. <a href='/contact/'>Please contact us</a> to rectify this, if you believe it to be in error.");
            header("Location:/login/");
        }
    }
