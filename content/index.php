<?php
    include('../assets/dbfuncs.php');
    
    use PeterBourneComms\CMS\Content;
    use PeterBourneComms\CMS\ImageLibrary;
    //use PeterBourneComms\TheARR\FAQ;
    use PeterBourneComms\CMS\People;
    use PeterBourneComms\Schools\Policy;
    use PeterBourneComms\Schools\PolicyCat;
    
    //Set up a Content object
    $ContentO = new Content();
    
    if (!is_object($ContentO)) {
        header("Location: /");
        exit;
    }
    
    //Do we have a second level identified?
    //If so - use it
    if (isset($_GET['b']) && $_GET['b'] != '') {
        //Flag as lowerlevel
        $flagLowerLevel = true;
        
        $b = $_GET['b'];
        $a = $_GET['a'];
        //Check if its numeric or not
        if (is_numeric($b)) {
            $ContentEntry = $ContentO->getItemById($b);
        } else {
            $ContentEntry = $ContentO->getItemByUrl($b, $a);
        }
    } else {
        $flagLowerLevel = false;
        
        //Just one param passed - so use that instead
        if (isset($_GET['id']) && clean_int($_GET['id']) > 0) {
            $a = clean_int($_GET['id']);
        } else {
            $a = $_GET['a'];
        }
        
        //Check if its numeric or not
        if (is_numeric($a)) {
            $ContentEntry = $ContentO->getItemById($a);
        } else {
            $ContentEntry = $ContentO->getItemByUrl($a);
        }
    }
    
    //Any content?
    if (!is_array($ContentEntry) || count($ContentEntry) <= 0) {
        header('HTTP/1.0 404 Not Found');
        header("Location: /404/");
        exit;
    }
    
    
    // Is there a link we need to redirect to?
    // If so, let's not hang around!
    if ($ContentEntry['Link'] != '') {
        /*if (substr($Link, 0, 1) == '/')
        {
            //An internal link is assumed
            $link = $Link;
        }
        else
        {
            //external - so add "http://"
            $link = "https://" . $Link;
        }*/
        header("Location:".$ContentEntry['Link']);
        exit;
    }
    
    
    //Set up defaults
    //Page image
    if ($ContentEntry['ImgFilename'] != "" && file_exists(FixOutput(DOCUMENT_ROOT.$ContentEntry['ImgPath'].$ContentEntry['ImgFilename']))) {
        $HeaderImg = FixOutput($ContentEntry['ImgPath'].$ContentEntry['ImgFilename']);
    } else {
        unset($HeaderImg);
    }
    //MetaTitle
    if ($ContentEntry['MetaTitle'] == '') {
        $MetaTitle = FixOutput($ContentEntry['Title']);
    } else {
        $MetaTitle = FixOutput($ContentEntry['MetaTitle']);
    }
    //MetaDesc & Keywords
    if ($ContentEntry['MetaDesc'] == '') {
        $MetaDesc = $globalMetaDesc;
    } else {
        $MetaDesc = $ContentEntry['MetaDesc'];
    }
    if ($ContentEntry['MetaKey'] == '') {
        $MetaKey = $globalMetaKey;
    } else {
        $MetaKey = $ContentEntry['MetaKey'];
    }
    
    
    //Set up session locations
    $_SESSION['main'] = $ContentO->getContentTypeID();
    if ($flagLowerLevel === true) {
        $_SESSION['sub'] = $ContentEntry['ParentID'];
        $_SESSION['subsub'] = $ContentEntry['ID'];
    } else {
        $_SESSION['sub'] = $ContentEntry['ID'];
        unset($_SESSION['subsub']);
    }
    
    /*echo "main = ".$_SESSION['main']."<br/>";
    echo "sub = ".$_SESSION['sub']."<br/>";
    echo "subsub = ".$_SESSION['subsub']."<br/>";*/
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title><?php echo $MetaTitle; ?> | <?php echo $sitetitle; ?></title>
    <link href="/vendor/lightbox2/css/lightbox.min.css" rel="stylesheet"/>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
<?php
    //Header image - if present
    if (isset($HeaderImg)) {
        echo "<div class='max-width'>";
        echo "<div class='header-image'>";
        echo "<img src='".$HeaderImg."' alt='".FixOutput($ContentEntry['Title'])."' />";
        echo "</div>"; //end of header img
        echo "</div>"; //end of max-width
    }
?>
    <div class='grid-container space-above'>
        <div class='grid-x grid-margin-x'>
            <div class='medium-4 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-sidemenu.php'); ?>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-testimonial.php'); ?>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-quick-links.php'); ?>
            </div>
            <div class='medium-8 cell'>
                <?php
                    echo "<h1>".$ContentEntry['Title']."</h1>";
                    if (isset($ContentEntry['SubTitle']) && $ContentEntry['SubTitle'] != '') {
                        echo "<h2>".$ContentEntry['SubTitle']."</h2>";
                    }
                    
                    echo $ContentEntry['Content'];
                    
                    //Any special content?
                    if (isset($ContentEntry['SpecialContent']) && $ContentEntry['SpecialContent'] != '') {
                        //Check its in our recognised list
                        if (in_array_r($ContentEntry['SpecialContent'], $globalSpecialContentAreas)) {
                            //Display the content
                            
                            //
                            // Policies
                            //
                            if ($ContentEntry['SpecialContent'] == 'Policies') {
                                $PCO = new PolicyCat();
                                $Cats = $PCO->listAllItems();
    
                                if (is_array($Cats) && count($Cats) > 0) {
                                    echo "<div class='accordion pb-accordion' data-accordion data-multi-expand='true' data-allow-all-closed='true'>";
                                    foreach ($Cats as $Cat) {
                                        echo "<div class='accordion-item' data-accordion-item>";
                                        echo "<a href='#' class='accordion-title'>" . $Cat['Title'] . "</a>";
                                        echo "<div class='accordion-content' data-tab-content>";
            
                                        //Now the docs
                                        $Docs = $PCO->returnDocsInCategory($Cat['ID']);
                                        if (is_array($Docs) && count($Docs) > 0) {
                                            foreach ($Docs as $Doc) {
                                                echo "<div class='gov-doc'>";
                                                //foreach ($Doc['FileInfo'] as $File)
                                                //{
                                                echo "<div class='grid-x grid-x-padding'>";
                                                echo "<div class='small-3 cell'><img src='" . $Doc['FileInfo'][0]['Icon']['Path'] . $Doc['FileInfo'][0]['Icon']['File'] . "' alt='" . $Doc['FileInfo'][0]['Icon']['Type'] . "' title='" . $Doc['FileInfo'][0]['Icon']['Type'] . "'/>";
                                                echo "</div>";
                                                echo "<div class='small-9 cell'><p><strong>" . $Doc['DocInfo']['Title'] . "</strong><br/><em>" . format_date($Doc['DocInfo']['DateDisplay'] ?? null) . "</em></p>";
                                                echo $Doc['DocInfo']['Content'];
                                                echo "<a class='button' href='/content/viewdoc.php?contenttype=policies&contentid=" . $Doc['DocInfo']['ID'] . "&id=" . $Doc['FileInfo'][0]['ID'] . "' target='_blank'>View document</a>";
                                                echo "</div>";
                                                echo "</div>";
                                                //}
                                                echo "</div>";
                                            }
                                        } else {
                                            echo "<em>Sorry - no documents in this category yet.</em>";
                                        }
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                    echo "</div>";
                                }
                            } elseif ($ContentEntry['SpecialContent'] == 'FAQs') {
                                //FAQs
                                //See if there are any FAQs for this page
                                $FO = new FAQ();
                                if (is_object($FO)) {
                                    $FAQs = $FO->listAllItems($ContentEntry['ID'], 'contentid');
                                    if (is_array($FAQs) && count($FAQs) > 0) {
                                        echo "<ul class='accordion' data-accordion data-multi-expand='true' data-allow-all-closed='true'>";
                                        foreach ($FAQs as $Item) {
                                            echo "<li class='accordion-item' data-accordion-item>";
                                            echo "<a href='#' class='accordion-title'>" . $Item['Title'] . "</a>";
                                            echo "<div class='accordion-content' data-tab-content>";
                                            echo $Item['Content'];
                                            echo "</div>";
                                            echo "</li>";
                                        }
                                        echo "</ul>";
                                    }
                                }
                            }
                            
                            //
                            // People - Staff or Governors
                            //
                            if ($ContentEntry['SpecialContent'] == 'People - Staff' || $ContentEntry['SpecialContent'] == 'People - Governors') {
                                switch ($ContentEntry['SpecialContent']) {
                                    case 'People - Staff':
                                        $section = 'Staff';
                                        break;
                                    case 'People - Governors':
                                        $section = 'Governors';
                                        break;
                                }
                                $PO = new People();
                                if (is_object($PO)) {
                                    $Types = $PO->listPeopleTypes();
                                    //print_r($Types);
                                    if (is_array($Types) && count($Types) > 0) {
                                        foreach ($Types as $Type) {
                                            //print_r($Type);
                                            //echo "section = ".$section."<br/>";
                                            //echo "type = ".$Type['ContactType']."<br/><br/>";
                                            //If Board - each member SHOULD have a ContactType, but if Contact - don't worry
                                            //if ((($section == 'Staff') && $Type['ContactType'] != '') || $section == 'Staff') {
                                                $People = $PO->listPeopleBySection($section, $Type['ContactType']);
                                                //print_r($People);
                                                if (is_array($People) && count($People) > 0) {
                                                    //Display them!
                                                    echo "<h2>".$Type['ContactType']."</h2>";
                                                    echo "<div class='grid-x small-up-2 medium-up-3'>";
                                                    foreach ($People as $Item) {
                                                        echo "<div class='cell'><div class='contact-head-panel' ";
                                                        if ($Item['Content'] != '') {
                                                            if ($Item['URLText'] != '') {
                                                                $link = "/people/".$Item['URLText'];
                                                            } else {
                                                                $link = "/content/people-detail.php?id=" . $Item['ID'];
                                                            }
                                                            echo "' onclick=\"location.href='".$link."'\" style='cursor:pointer;'";
                                                        } elseif ($Item['Link'] != '') {
                                                            echo " onclick=\"openTab('".$Item['Link']."');\" style='cursor:pointer;'";
                                                        }
                                                        echo ">";
                                                        //Image
                                                        if ($Item['ImgFilename'] != '' && file_exists(DOCUMENT_ROOT.$Item['ImgPath']."large/".$Item['ImgFilename'])) {
                                                            echo "<div class='contact-head-image'><img src='".$Item['ImgPath']."large/".$Item['ImgFilename']."' alt='".FixOutput($Item['Firstname']." ".$Item['Surname'])."' /></div>";
                                                        } else {
                                                            echo "<div class='contact-head-image'><img src='/assets/img/silhouette_150.png' alt='".FixOutput($Item['Firstname']." ".$Item['Surname'])."' /></div>";
                                                        }
                                                        echo "<p><span class='contact-name'>".$Item['Firstname']." ".$Item['Surname']."</span>";
                                                        if ($Item['Title'] != '') {
                                                            echo "<br/><span class='contact-role'>".$Item['Title']."</span>";
                                                        }
                                                        if ($Item['Email'] != '') {
                                                            echo "<br/><span class='contact-email'><a href='mailto:".$Item['Email']."'>".$Item['Email']."</a></span>";
                                                        }
                                                        /*if ($Item['Telephone'] != '') {
                                                            echo "<br/><span class='contact-tel'>".$Item['Telephone']."</span>";
                                                        }*/
                                                        if ($Item['Content'] != '') {
                                                            echo "<br/><span class='contact-link'<a href='".$link."'>Click to read more &gt;</a></span>";
                                                        }
                                                        echo "</p>";
                                                        echo "</div>";
                                                        echo "</div>";
                                                    }
                                                    echo "</div>";
                                                }
                                            //}
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    
                    if ($_SESSION['SiteSettings']['AddThisCode'] != '') {
                        echo "<!-- Go to www.addthis.com/dashboard to customize your tools -->\n<div class='addthis_sharing_toolbox'></div>\n";
                    }
    
                    include(DOCUMENT_ROOT."/assets/inc-content-library.php");
                    outputLibrary('Content',$ContentEntry['ID']);
                ?>
            </div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script src="/vendor/lightbox2/js/lightbox.min.js"></script>
    <script>
        function openTab(url) {
            window.open(url,'_blank');
        }
    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>