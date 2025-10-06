<?php
    use PeterBourneComms\CMS\HomeButton;

    $BO = new HomeButton();
    $Buttons = $BO->getAllHomeButtons();

    if (count($Buttons) >= 1)
    {
        $counter = 1;
        foreach ($Buttons as $Button)
        {
            if ($Button['LinkURL'] != '')
            {
                echo "<a href='".$Button['LinkURL']."'";
                if ($Button['NewWindow'] == 'Y') { echo " target='_blank'"; }
                echo ">";
            }
            echo "<div class='home-service-panel panel".$counter."'><span><h3>".$Button['Title']."</h3></span></div>";
            if ($Button['LinkURL'] != '')
            {
                echo "</a>";
            }
            $counter++;
        }

    }