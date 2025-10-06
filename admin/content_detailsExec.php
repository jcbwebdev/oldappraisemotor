<?php
	include("../assets/dbfuncs.php");
	checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

    use PeterBourneComms\CMS\Content;
    
    if (isset($_POST['delete'])) {
        $delete = $_POST['delete'];
        $_SESSION['error'] = false;
    
        if ($delete == '1') {
            //Retrieve the item record so we can unlink (delete) the files
            $Content = new Content(clean_int($_POST['ID']));
            $result = $Content->deleteItem();
        
            if ($result === true) {
                $_SESSION['Message'] = array('Type' => 'warning', 'Message' => "Content has been deleted.");
            }
            header("Location:content_list.php");
            exit;
        }
    }



//---------------------1. Collect form variables -------------------------------->
	$ID							= clean_int($_POST['ID'] ?? null);
    $ParentID                   = clean_int($_POST['ParentID'] ?? null);
	$Title						= $_POST['Title'] ?? null;
	$SubTitle					= $_POST['SubTitle'] ?? null;
	$MenuTitle					= $_POST['MenuTitle'] ?? null;

	$Content					= $_POST['Content'] ?? null;
	$Col2Content			    = $_POST['Col2Content'] ?? null;
	$Col3Content			    = $_POST['Col3Content'] ?? null;

	$DisplayOrder				= $_POST['DisplayOrder'] ?? null;
	$Link						= $_POST['Link'] ?? null;

	$URLText					= $_POST['URLText'] ?? null;
    $MetaDesc					= $_POST['MetaDesc'] ?? null;
	$MetaKey					= $_POST['MetaKey'] ?? null;
	$MetaTitle					= $_POST['MetaTitle'] ?? null;

	$OldImgFilename				= $_POST['OldImgFilename'] ?? null;
    $SpecialContent             = $_POST['SpecialContent'] ?? null;


    $CTAPanel1 = $_POST['CTAPanel1'] ?? null;
    $CTAPanel2 = $_POST['CTAPanel2'] ?? null;
    $MoreText = $_POST['MoreText'] ?? null;
    $MoreLink = $_POST['MoreLink'] ?? null;
    $MoreButton = $_POST['MoreButton'] ?? null;
    


    //Also the Content Types (Categories)
	$posted = $_POST;
	reset($posted);
	$Categories = array();
	for ($i=1; $i <= count($posted); $i++)
	{
		if (stristr(key($posted), "Cat"))
		{
			//this is a checkbox - now just need to add its current value to $messages
			$Categories[] = current($posted);
		}
		next($posted);
	}

	$state = $_GET['state'] ?? null;
    $_SESSION['PostedForm']     = $_POST;


//-------------------------------------2. Check form for errors------------------------------------>

	//Convert the URLText to the format we want (remove spaces and dodgy characters)
	if ($URLText == '') { $URLText = $Title; }
	$URLText = ConvertURLText($URLText);

	$TCO = new Content();
	$URLTextValid = $TCO->URLTextValid($URLText, $ID, $ParentID);

	if ( $Title == '' || !$URLTextValid || ($state == 'lower' && $ParentID <= 0 ))
	{
		if ( $Title == '' ) { $_SESSION['titleerror'] = "<p class=\"error\">Please enter the content title:</p>"; }
		if ( !$URLTextValid ) { $_SESSION['urlerror'] = "<p class=\"error\">Please enter a unique URL to use:</p>"; }
        if ( $state == 'lower' && $ParentID <= 0 ) { $_SESSION['parenterror'] = "<p class=\"error\">Please select a piece of content to be the parent:</p>"; }

		$_SESSION['Message'] = array('Type'=>'alert','Message'=>"You have some errors in your information, please could you attend to any highlighted error messages.");
		$_SESSION['error'] = true;

        header("Location:content_details.php?state=$state&id=$ID&parentid=$ParentID");
		exit;
	}

//-------------------------------------3. Update object and database  ------------------------------------>

    //Populate some defaults - if nothing supplied
	if ($DisplayOrder <= 0) { $DisplayOrder = 1000; }
	if ($MetaTitle == '') { $MetaTitle = $Title; }
	if ($MenuTitle == '') { $MenuTitle = $Title; }
    if ($ParentID <= 0) { $flagLower = false; $ParentID = null; } else { $flagLower = true; }
	if ($ID <= 0) { $ID = null; }

    //Create a new Content object to populate and update/save
    $ContentObj = new Content($ID,$flagLower,1600,700,USER_UPLOADS.'/images/content-headers/');

    //Now populate the items
    $ContentObj->setTitle($Title);
    $ContentObj->setParentID($ParentID);
    $ContentObj->setSubTitle($SubTitle);
    $ContentObj->setMenuTitle($MenuTitle);
    $ContentObj->setContent($Content);
    $ContentObj->setCol2Content($Col2Content);
    $ContentObj->setCol3Content($Col3Content);
    $ContentObj->setLink($Link);
    $ContentObj->setDateDisplay(date('Y-m-d',time()));
    $ContentObj->setDisplayOrder($DisplayOrder);
    $ContentObj->setURLText($URLText);
    $ContentObj->setMetaDesc($MetaDesc);
    $ContentObj->setMetaKey($MetaKey);
    $ContentObj->setMetaTitle($MetaTitle);
    $ContentObj->setSpecialContent($SpecialContent);

    
    //Categories
    $ContentObj->setCategories($Categories);

    //Image
    if (isset($_POST['ImgFile']))
    {
        $ContentObj->uploadImage($_POST['ImgFile']);
    }

    //Now the author details
    $ContentObj->setAuthorID($_SESSION['UserDetails']['ID']);
    $ContentObj->setAuthorName($_SESSION['UserDetails']['FullName']);

    //Now save the item
    $result = $ContentObj->saveItem();


//-------------------------------------4. Move on and clean up session vars ------------------------------------>

    if ($result == true)
    {
        unset($_SESSION['PostedForm']);

        $_SESSION['Message'] = array('Type'=>'success','Message'=>"Content details have been updated for $Title");
        if ($state == 'lower')
        {
            header("Location: content_details.php?state=edit&id=$ParentID");
        }
        else
        {
            header("Location: content_list.php");
        }
    }
    else
    {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: content_details.php?state=edit&id=$ID&parentID=$ParentID");
    }