<?php
	include("../assets/dbfuncs.php");
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

    use PeterBourneComms\CMS\Testimonial;
    
    if (isset($_POST['delete'])) {
        $delete = $_POST['delete'];
        $_SESSION['error'] = false;
    
        if ($delete == '1') {
            //Retrieve the item record so we can unlink (delete) the files
            $TO = new Testimonial(clean_int($_POST['ID']));
            $result = $TO->deleteItem();
        
            if ($result == true) {
                $_SESSION['Message'] = array('Type' => 'warning', 'Message' => "Testimonial item has been deleted.");
            }
            header("Location:testimonial_list.php");
            exit;
        }
    }


//---------------------1. Collect variables -------------------------------->

	$ID						                	= clean_int($_POST['ID'] ?? null);
	$ContentID						            = clean_int($_POST['ContentID'] ?? null);
	$Quote                                      = $_POST['Quote'] ?? null;
	$Content					                = $_POST['Content'] ?? null;
	$Attribution                                = $_POST['Attribution'] ?? null;

	$URLText					                = $_POST['URLText'] ?? null;
	$MetaDesc					                = $_POST['MetaDesc'] ?? null;
	$MetaKey					                = $_POST['MetaKey'] ?? null;
	$MetaTitle                                  = $_POST['MetaTitle'] ?? null;


	$state = $_GET['state'] ?? null;

	$_SESSION['PostedForm']                     = $_POST;


//-------------------------------------2. Check form for errors------------------------------------>

    //Convert the URLText to the format we want (remove spaces and dodgy characters)
    /*if ($URLText == '') { $URLText = $Quote; }
	$URLText = ConvertURLText($URLText);
    $_SESSION['PostedForm']['URLText'] = $URLText;

    //URL Text check needs to be based on News Object
    $TempContent = new Testimonial();
    $URL_is_valid = $TempContent->URLTextValid($ID, $URLText);
*/

	if ( $Quote == '' )
	{
		if ( $Quote == '' ) { $_SESSION['titleerror'] = "<p class=\"error\">Please enter the quote:</p>"; }
		//if ( $Content == '' ) { $_SESSION['contenterror'] = "<p class=\"error\">Please enter some content:</p>"; }
		//if ( !$URL_is_valid ) { $_SESSION['urlerror'] = "<p class=\"error\">Please enter a unique URL to use:</p>"; }

		$_SESSION['Message'] = array('Type'=>'alert','Message'=>"You have some errors in your information, please could you attend to any highlighted error messages.");
        $_SESSION['error'] = true;

        header("Location:testimonial_edit.php?state=$state&id=$ID");
		exit;
	}

//-------------------------------------3. Update object and database  ------------------------------------>

	//if ($DateDisplay == '' || $DateDisplay == '0000-00-00') { $DateDisplay = date('Y-m-d H:i:s'); }	else { $DateDisplay = convert_jquery_date($DateDisplay); }
    if ($ID <= 0) { $ID = null; }
    if ($ContentID <= 0) { $ContentID = null; }
    if ($MetaTitle == '') { $MetaTitle = $Quote; }

	//Create a new News object and populate it - we've set the image size we want to use as well
    $TO = new Testimonial($ID);

	//Now populate the items
    $TO->setQuote($Quote);
    $TO->setContentID($ContentID);
    $TO->setContent($Content);
    $TO->setAttribution($Attribution);
    $TO->setURLText($URLText);
    $TO->setMetaDesc($MetaDesc);
    $TO->setMetaKey($MetaKey);
    $TO->setMetaTitle($MetaTitle);

    //Now the author details
    $TO->setAuthorID($_SESSION['UserDetails']['ID']);
    $TO->setAuthorName($_SESSION['UserDetails']['FullName']);
    
    //Now save the item
    $result = $TO->saveItem();

    if ($state == 'new') { $ID = $TO->getID(); }

//-------------------------------------4. Move on and clean up session vars ------------------------------------>

    if ($result == true)
    {
        unset($_SESSION['PostedForm']);

		$_SESSION['Message'] = array('Type'=>'success','Message'=>"Testimonial details have been updated");
        header("Location: testimonial_list.php");
	}
	else
    {
        $_SESSION['Message'] = array('Type' => 'alert', 'Message' => "Sorry - there was a problem updating the database. Please try again.");
        header("Location: testimonial_edit.php?state=$state&id=" . $ID);
    }