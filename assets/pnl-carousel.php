<?php
    use PeterBourneComms\CMS\Carousel;

    $CO = new Carousel();

    if (is_object($CO)) {

        $Carousel = $CO->listAllItems();

        if (is_array($Carousel) && count($Carousel) >= 1) {
            echo "<div class='max-width'>";
            echo "<div class='carousel owl-carousel owl-theme'>";

            foreach ($Carousel as $Slide) {
                echo "<div class='carousel-slide'";
                if ($Slide['CTALink'] != '') {
                    echo " onclick=\"location.href='" . $Slide['CTALink'] . "'\"";
                }
                echo ">";

                //Slide image
                //echo "<div class='slide-image' style='background-image: url(" . FixOutput($Slide['ImgPath'].$Slide['ImgFilename']) . ");'><!----></div>";
                echo "<div class='slide-image'>";

                if ($Slide['CTALink'] != '') {
                    echo "<a href='".$Slide['CTALink']."'>";
                }
                echo "<img src='" . FixOutput($Slide['ImgPath'] . $Slide['ImgFilename']) . "' alt='" . FixOutput($Slide['Title']) . "' />";
                if ($Slide['CTALink'] != '') {
                    echo "</a>";
                }
                echo "</div>";

                //Text box
                echo "<div class='slide-text-container'>";
                echo "<div class='slide-text'>";
                if ($Slide['Title'] != '' || $Slide['Content'] != '')
                {
                    //Content
                    echo "<h2>" . $Slide['Title'] . "</h2>";
                    if ($Slide['Content'] != '')
                    {
                        echo "<p>" . $Slide['Content'] . "</p>";
                    }
                    if ($Slide['CTALabel'] != '')
                    {
                        echo "<h3>".$Slide['CTALabel']."</h3>";
                    }
                }
                echo "</div>"; //end of slide text
                echo "</div>"; //end of slide-text-container

                echo "</div>"; //end of slide
            }

            echo "</div>"; //end of carousel
            echo "</div>"; //end of max width
        }
    }