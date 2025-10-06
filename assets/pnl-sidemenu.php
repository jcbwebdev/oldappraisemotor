<?php

    use PeterBourneComms\CMS\Content;
    
    /*if (!is_numeric($_SESSION['main'])) {
        exit;
    }*/

    $SCO = new Content();
    if (!is_object($SCO)) {
        die();
    }
    if (isset($_SESSION['main']) && $_SESSION['main'] != '') {
        $SubMenu = $SCO->getAllContentByType($_SESSION['main']);
    } else { $SubMenu = null; }

    if (is_array($SubMenu) && count($SubMenu) >= 1) {
        echo "<div class='sidemenu-container'>";
        echo "<p>In this section:</p>";
        echo "<ul class='sidemenu vertical menu accordion-menu' data-accordion-menu data-multi-open='false' data-submenu-toggle='true'>";
        foreach ($SubMenu as $CI) {
            if ($CI['URLText'] != '') {
                $link = "/" . $CI['URLText'];
            } else {
                $link = "/content/?id=" . $CI['ID'];
            }
            echo "<li><a href='" . $link . "'";

            if ($_SESSION['sub'] == $CI['ID']) {
                echo " class='selected'";
            }
            echo ">" . $CI['MenuTitle'] . "</a>";

            //Lowest level pages?
            $LowerContent = $SCO->getLowerLevelPages($CI['ID']);
            if (is_array($LowerContent) && count($LowerContent) > 0) {
                echo "<ul class='menu vertical";
                if ($_SESSION['sub'] == $CI['ID']) { echo " is-active'"; }
                echo "'>";
                foreach ($LowerContent as $LLC) {
                    if ($LLC['URLText'] != '' && $CI['URLText'] != '') {
                        $link = "/" . $CI['URLText'] . "/" . $LLC['URLText'];
                    } else {
                        $link = "/content/?id=" . $LLC['ID'];
                    }
                    echo "<li><a href='" . $link . "'";
                    if ($_SESSION['subsub'] == $LLC['ID']) {
                        echo " class='selected'";
                    }
                    echo ">" . $LLC['MenuTitle'] . "</a></li>";
                }
                echo "</ul>";
            }

            echo "</li>";
        }
        echo "</ul>";
        echo "</div>";
    }