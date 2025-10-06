<?php
    include(__DIR__ . "/../assets/dbfuncs.php");
    
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\SiteSettings;
    use PeterBourneComms\CMS\Content;
    
    /*Script to run nightly and clear out any password resets greater than 24 hours old according to their Requested value

	*/
    $DO = new Database();
    $dbconn = $DO->getConnection();
    
    $stmt = $dbconn->prepare("DELETE FROM PasswordResets WHERE NOW() > DATE_ADD(Requested, INTERVAL 1 DAY)");
    $stmt->execute();
    

    $siteurl = "https://www.clickcarauction.co.uk/";

    

    $SSO = new SiteSettings(1);
    $SS = $SSO->getItem();

    /**
     * SITE MAP
     */

    //Sitemap creation routine
    $output = ""; //Empty it!
    $output .= "<?xml version='1.0' encoding='UTF-8'?>\n";
    $output .= "<urlset\n";
    $output .= "  xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'\n";
    $output .= "  xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'\n";
    $output .= "  xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9\n";
    $output .= "  http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd'>\n";

    $output .= "<url><loc>" . $siteurl . "</loc></url>\n";

    $CO = new Content();
    $ContentTypes = $CO->getAllContentTypes();

    if (is_array($ContentTypes) && count($ContentTypes) > 0)
    {
        foreach ($ContentTypes as $ContentType)
        {
            //Need to assess if there are multiple pages in a section. If there are - we need to use the drop down menu structure.
            $Contents = $CO->getAllContentByType($ContentType['ID']);

            //Now asses:
            if (is_array($Contents) && count($Contents) >= 1)
            {
                //Multiple pages in the section
                //OK - use ContentType as the TOP LEVEL heading
                $output .= "<url><loc>" . $siteurl;

                if ($Contents[0]['URLText'] != '')
                {
                    $output .= $Contents[0]['URLText'];
                }
                else
                {
                    $output .= "index.php?id=".$Contents[0]['ID'];
                }
                $output .= "</loc></url>\n";
            }

            //Any sub menu items?
            if (count($Contents) > 1)
            {
                foreach($Contents as $CI)
                {
                    $output .= "<url><loc>" . $siteurl;
                    if ($CI['URLText'] != '')
                    {
                        $output .= $CI['URLText'];
                    }
                    else
                    {
                        $output .= "index.php?id=".$CI['ID'];
                    }
                    $output .= "</loc></url>\n";

                    //Lowest level pages?
                    $LowerContent = $CO->getLowerLevelPages($CI['ID']);
                    if (is_array($LowerContent) && count($LowerContent) > 0)
                    {
                        foreach($LowerContent as $LLC)
                        {
                            $output .= "<url><loc>" . $siteurl;
                            if ($LLC['URLText'] != '' && $CI['URLText'] != '')
                            {
                                $output .= $CI['URLText']."/".$LLC['URLText'];
                            }
                            else
                            {
                                $output .= "index.php?id=" . $LLC['ID'];
                            }
                            $output .= "</loc></url>\n";
                        }
                    }
                }
            }
        }
    }


    $output .= "</urlset>";

    //Next save off the RSS output as a file to the root of the website.
    //Create the file
    $File = __DIR__."/../sitemap.xml";
    chmod($File,0777);
    $Handle = fopen($File, 'w');
    //Write to the file
    fwrite($Handle, $output);
    //Close the file
    fclose($Handle);
    
    
    