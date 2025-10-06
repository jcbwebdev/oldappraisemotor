<?php
    include("../assets/dbfuncs.php");

    use PeterBourneComms\CMS\People;

    //Set up object
    $PO = new People();

    if (!is_object($PO)) {
        header('HTTP/1.0 404 Not Found');
        header("Location: /");
        exit;
    }
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    } else { $id = null; }
    
    if (is_numeric($id) && clean_int($id) > 0) {
        $Person = $PO->getItemById($id);
    } else {
        //check to see if other stuff provided for permalink!
        $permalink = str_replace("/people/", "", $_SERVER['REQUEST_URI']);
        $permalink = parse_url($permalink)['path'];
        if ($permalink != '') {
            $Person = $PO->getItemByUrl($permalink);
        } else {
            header('HTTP/1.0 404 Not Found');
            header("Location: /");
            exit;
        }
    }

    if (!is_array($Person) || count($Person) <= 0) {
        header('HTTP/1.0 404 Not Found');
        header("Location: /");
        exit;
    }


    //Set up defaults
    //Page image
    //MetaTitle
    if ($Person['MetaTitle'] == '') {
        $MetaTitle = FixOutput($Person['Firstname']." ".$Person['Surname']);
    } else {
        $MetaTitle = FixOutput($Person['MetaTitle']);
    }
    //MetaDesc & Keywords
    if ($Person['MetaDesc'] == '') {
        $MetaDesc = $globalMetaDesc;
    } else {
        $MetaDesc = $Person['MetaDesc'];
    }
    if ($Person['MetaKey'] == '') {
        $MetaKey = $globalMetaKey;
    } else {
        $MetaKey = $Person['MetaKey'];
    }

    include(DOCUMENT_ROOT . '/assets/inc-page_start.php');
?>
    <title><?php echo $MetaTitle; ?> | People | <?php echo $sitename; ?></title>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='grid-x grid-margin-x'>
            <div class='medium-4 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-sidemenu.php'); ?>
            </div>
            <div class='medium-4 cell'>
                <?php
                    echo "<h1>" . $Person['Firstname'] . " " . $Person['Surname'] . "</h1>";
                    if ($Person['Title'] != '') {
                        echo "<h2>" . $Person['Title'] . "</h2>";
                    }
    
                    if ($Person['Email'] != '') {
                        echo "<p class='contact-detail-email'><a href='mailto:".$Person['Email']."' target='_blank'>" . $Person['Email'] . "</a></p>";
                    }
                    /*if ($Person['Telephone'] != '') {
                        echo "<p class='contact-detail-telephone'><a href='tel:".$Person['Telephone']."' target='_blank'>" . $Person['Telephone'] . "</a></p>";
                    }*/
                    
                    echo $Person['Content'];

                    echo "<p><a href='javascript:window.history.back();'>« Back</a></p>";
                ?>
            </div>
            <div class='medium-4 cell'>
                <?php
                    if ($Person['ImgFilename'] != '' && file_exists(FixOutput(DOCUMENT_ROOT . $Person['ImgPath'] . "large/" . $Person['ImgFilename']))) {
                        echo "<p>";
                        echo "<img src='" . FixOutput($Person['ImgPath'] . "large/" . $Person['ImgFilename']) . "' alt='" . Fixoutput($Person['Firstname'] . " " . $Person['Surname']) . "' title='" . FixOutput($Person['Firstname'] . " " . $Person['Surname']) . "' />";
                        echo "</p>";
                    }
                ?>
            </div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT . '/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_end.php'); ?>