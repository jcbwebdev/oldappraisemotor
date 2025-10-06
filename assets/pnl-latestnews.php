<?php
    use PeterBourneComms\CMS\News;

    $NO = new News(); // returns array of current news items
    $News = $NO->listAllItems('Panel');

    if (count($News) > 0) {

        echo "<div class='panel rainbow-panel latest-news-outer'>";
        echo "<h3>Latest News</h3>";
        echo "<div class='owl-carousel latest-news'>";//owl-carousel
        foreach ($News as $NewsItem) {
            //loop through headlines here
            //Sort link
            if ($NewsItem['URLText'] != '') {
                $newslink = "/news/" . $NewsItem['URLText'];
            } else {
                $newslink = "/news/?id=" . $NewsItem['ID'];
            }

            echo "<div class='panel' onclick=\"location.href='" . $newslink . "'\" style='cursor: pointer; '>";
            //Image
            if ($NewsItem['ImgFilename'] != '') {
                echo "<img src='" . FixOutput($NewsItem['ImgPath']) . "small/" . FixOutput($NewsItem['ImgFilename']) . "' alt='" . $NewsItem['Title'] . "' title='" . $NewsItem['Title'] . "' />";
                $numchars = 80;
            } else {
                $numchars = 160;
            }

            //News info
            echo "<div class='panel-content'>";
            echo "<p class='headline'>" . $NewsItem['Title'] . "</p>";
            echo "<p class='date'>".format_shortdate($NewsItem['DateDisplay'])."</p>";
            echo "<p class='news-intro'>" . substr(FixOutput(strip_tags($NewsItem['Content'])), 0, $numchars) . "...</p>";
            echo "<p class='readmore'><a href='" . $newslink . "'>Read more &gt;</a></p>";
            echo "</div>"; //end of panel content
            echo "</div>"; //end of panel
        }
        echo "</div>"; //end of latest-news carousel
        echo "</div>"; //end of outer
    }