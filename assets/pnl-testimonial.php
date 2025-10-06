<?php
    use PeterBourneComms\CMS\Testimonial;

    //If home page - just display 1 randomly.
    //If lower level - select 1 from this section
    /*if ($_SESSION['main'] == 'home')
    {*/
        $sort = 'rand';
        $contentid = 0;
    /*}
    else
    {
        $sort = 'quote';
        $contentid = $_SESSION['sub'];
    }*/

    $TestimonialObj = new Testimonial(); // returns array of testimonial items
    $Testimonials = $TestimonialObj->getAllTestimonials($contentid,$sort);
    if (is_array($Testimonials) && count($Testimonials) >= 1)
    {
        //echo "<h3 class='testimonials-heading'>Our Customer Feedback</h3>";
        echo "<div class='testimonials-container owl-carousel owl-theme'>";
        //We only want to display 3
        $n = 0;
        $max = 2;
        foreach($Testimonials as $Testimonial) {
            //if ($Testimonial['URLText'] == '') { $link = "/content/testimonialview.php?id=".$Tesimonial['ID']; } else { $link = "/testimonial/".$Testimonial['URLText']; }

            //Display the quote
            echo "<div class='testimonial-panel'>";
            //echo "<div class='quote-icon-open'><img src='/assets/img/quote-mark.png' alt='Quote mark' /></div>";
            echo " <div class='quote-mark'>&ldquo;</div>";
            echo "<div class='quote-quote'>";
            echo "<p class='quote'>".nl2br($Testimonial['Quote'])."</p>";
            //echo "<div class='quote-icon-close'><img src='/assets/img/quote-close.png' alt='Quote mark' /></div>";

            //echo "<p class='quote-name'>- ".$Testimonial['Attribution']."</p>";
            /*if ($Testimonial['Content'] != '')
            {
                echo "<p><a href='".$link."'>Read the full testimonial &gt;</a></p>";
            }*/
            echo "</div>";
            echo "</div>";

            $n++;
            if ($n > $max) { break; }
        }
        echo "</div>";
    }