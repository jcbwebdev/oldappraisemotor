<?php
    include('../assets/dbfuncs.php');

    use PeterBourneComms\CMS\Content;

    unset($_SESSION['main']);
    unset($_SESSION['sub']);

    $CO = new Content();
    if (!is_object($CO)) {
        die();
    }

    include(DOCUMENT_ROOT . '/assets/inc-page_start.php');
?>
    <title>Site map | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_head.php'); ?>
    <div class='grid-container space-below'>
        <div class='grid-x grid-margin-x '>
            <div class='medium-2 cell'>
                <!---->
            </div>
            <div class='medium-8 cell'>
                <h1>Site Map</h1>
                <ul>
                    <li><a href="/">Home</a></li>
                    <?php
                        $ContentTypes = $CO->getAllContentTypes();

                        if (is_array($ContentTypes) && count($ContentTypes) > 0) {
                            foreach ($ContentTypes as $ContentType) {
                                //Need to assess if there are multiple pages in a section. If there are - we need to use the drop down menu structure.
                                $Contents = $CO->getAllContentByType($ContentType['ID']);

                                //Now asses:
                                if (is_array($Contents) && count($Contents) >= 1) {

                                    if ($Contents[0]['URLText'] != '') {
                                        $link = "/" . $Contents[0]['URLText'];
                                    } else {
                                        $link = "/content/?id=" . $Contents[0]['ID'];
                                    }
                                    echo "<li><a href='" . $link . "'>" . $ContentType['Title'] . "</a>";

                                    if (count($Contents) > 1) {
                                        echo "<ul>";

                                        foreach ($Contents as $CI) {
                                            echo "<li>";
                                            if ($CI['URLText'] != '') {
                                                echo "<a href='/" . $CI['URLText'] . "'";
                                            } else {
                                                echo "<a href='/content/?id=" . $CI['ID'] . "'";
                                            }
                                            echo ">" . $CI['MenuTitle'] . "</a>";

                                            //Lowest level pages?
                                            $LowerContent = $CO->getLowerLevelPages($CI['ID']);
                                            if (is_array($LowerContent) && count($LowerContent) > 0) {
                                                echo "<ul>";
                                                foreach ($LowerContent as $LLC) {
                                                    echo "<li>";
                                                    if ($LLC['URLText'] != '' && $CI['URLText'] != '') {
                                                        echo "<a href='/" . $CI['URLText'] . "/" . $LLC['URLText'] . "'";
                                                    } else {
                                                        echo "<a href='/content/?id=" . $LLC['ID'] . "'";
                                                    }
                                                    echo ">" . $LLC['MenuTitle'] . "</a></li>";
                                                }
                                                echo "</ul>";
                                            }

                                            echo "</li>";
                                        }
                                        //Finish the submenu
                                        echo "</ul>";
                                    }
                                    //End of item
                                    echo "</li>";
                                }
                            }
                        }
                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT . '/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_end.php'); ?>