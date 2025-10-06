<?php
    use PeterBourneComms\Schools\WarningAlert;
    $WA = new WarningAlert();
    $Alerts = $WA->listAllItems();

    if (is_array($Alerts) && count($Alerts) > 0)
    {
        echo "<div class='full-width alert-banner'>";
        echo "<div class='alert-banner-stripe'><!----></div>";

        foreach($Alerts as $Alert)
        {
            switch($Alert['MessageType'])
            {
                case 'snow':
                    $alt = "Snow information";
                    $icon = "/assets/img/alert-icon-snow.png";
                    $bg = "snowalert";
                    break;
                default:
                    $alt = "Information";
                    $icon = "/assets/img/alert-icon-info.png";
                    $bg = "infoalert";
                    break;
            }



            echo "<div class='grid-container ".$bg."'>";
            echo "<div class='grid-x'>";
            echo "<div class='cell'>";

            echo "<img src=\"".$icon."\" alt=\"".$alt."\" class=\"infoicon\" /><div class=\"alert-banner-content\"><h2>".$Alert['Title']."</h2>".$Alert['Content']."</div>";

            echo "</div>";
            echo "</div>";
            echo "</div>";
        }

        echo "<div class='alert-banner-stripe'><!----></div>";
        echo "</div>";
    }