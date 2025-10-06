<?php
    use PeterBourneComms\TheARR\SupportingPartner;
    
    $SPO = new SupportingPartner();
    if (is_object($SPO)) {
        $Partners = $SPO->listAllItems();
        if (is_array($Partners) && count($Partners) > 0) {
            //Create carousel
            echo "<div class='grid-container space-above'>";
            echo "<h2>Supporting Partners</h2>";
            echo "<div id='supporting-partners' class='owl-carousel owl-theme partner-logos'>";
            foreach ($Partners as $Partner) {
                echo "<div class='partner'>";
                if ($Partner['Link'] != '') {
                    echo "<a href='".$Partner['Link']."' target='_blank'>";
                }
                echo "<img src='".FixOutput($Partner['ImgPath'].$Partner['ImgFilename'])."' alt='".FixOutput($Partner['Title'])."' />";
                if ($Partner['Link'] != '') {
                    echo "</a>";
                }
                echo "</div>";
            }
            echo "</div>"; //end of container
            echo "</div>"; //end of grid
        }
    }
    