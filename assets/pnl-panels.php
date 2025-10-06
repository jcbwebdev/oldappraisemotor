<?php
    use PeterBourneComms\CMS\HomePanel;
    
    $PanelsObj = new HomePanel();
    $Panels = $PanelsObj->getAllHomePanels();

    echo "<div class='panel-container'>";
    
    if (count($Panels) >= 1) {
        $n = 1;
        foreach ($Panels as $Panel) {
            echo "<div class='rainbow-panel panel";
            //if ($n%2) { echo " green-panel"; } else { echo " yellow-panel"; }
            echo "'";
            if ($Panel['LinkURL'] != '') {
                echo " style='cursor :pointer;' onclick=\"location.href='" . $Panel['LinkURL'] . "';\"";
            }
            echo ">";
            if ($Panel['ImgFilename'] != '' && file_exists(DOCUMENT_ROOT.$Panel['ImgPath'].$Panel['ImgFilename'])) {
                echo "<img src='".FixOutput($Panel['ImgPath'].$Panel['ImgFilename'])."' alt='".FixOutput($Panel['Title'])."' />";
            }
            echo "<div class='panel-content'>";
            echo "<h3>".$Panel['Title']."</h3>";
            echo "<p>".$Panel['Content']."</p>";
            echo "<p><a href='".$Panel['LinkURL']."'>".$Panel['LinkText']."</a></p>";
            //echo "<p><a href='".$Panel['LinkURL']."'>Read more &gt;</a></p>";
            echo "</div>"; //end of panel content

            echo "</div>"; //end of panel
            $n++;
        }
    }
    
    //Latest news always the first panel
    include(DOCUMENT_ROOT.'/assets/pnl-latestnews.php');
    
    echo "</div>"; //end of panel container