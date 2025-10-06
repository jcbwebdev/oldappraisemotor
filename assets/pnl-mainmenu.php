<?php
    use PeterBourneComms\CMS\Content;
    
    $menutodisplay = "";
    $offcanvas = "";

//Now start generating them.....
    $menutodisplay .= "<div class='menu-container'><div class='grid-container'>";
    
    $menutodisplay .= "<div class='hamburger-toggle hide-for-large' data-toggle='offCanvasRight'><!--<span class='hamburger-menu-text'>Menu</span>--><button class='menu-icon' type='button'></button></div>";
    
    $menutodisplay .= "<div class='menu-logo'>";
    $menutodisplay .= "<a href='/'><img src='/assets/img/logo.svg' alt='Logo' /></a>";
    $menutodisplay .= "</div>";
    
    
    $menutodisplay .= "<div class='top-bar'>";
    $menutodisplay .= "<div class='top-bar-left'>";
    $menutodisplay .= "<ul class='menu mainmenu dropdown show-for-large' data-dropdown-menu data-alignment='left'>"; //data-autoclose='false'
    $menutodisplay .= "<li class='home-link'><a href='/'>Home</a></li>";
    
    if (isset($_SESSION['UserDetails']['AdminLevel']) && ($_SESSION['UserDetails']['AdminLevel'] === 'F'))
    {
        $menutodisplay .= "<li><a href='/admin/' class='admin-button'>Admin</a></li>";
    }
    

//As we're going through this - we'll create a string of html ready for the Foundation off-canvas menu
    //Search for off canvas menu
    $offcanvas .= "<form action='/search/' method='get' class='off-canvas-searchbox'><p><input type='search' value='' name='SearchField' class='searchquery' placeholder='Search' /><input type='hidden' name='action' value='search' /></p></form>";
    
    
    $offcanvas .= "<ul class='off-canvas-menu vertical menu' data-drilldown><li><label>Please select:</label></li><li><a href='#' data-toggle='offCanvasRight' onclick='javascript:return null;' class='off-canvas-return'>« Return to page</a></li><!--<li><a href='/login/' class='login-link'><i class='fi-unlock'></i> Member Login</a></li>--><li><a href='/'>Home</a></li>";
    
    
//Generate the menu
    $CO = new Content();
    $ContentTypes = $CO->getAllContentTypes();
    
    if (is_array($ContentTypes) && count($ContentTypes) > 0)
    {
        foreach ($ContentTypes as $ContentType)
        {
            $Contents = $CO->getAllContentByType($ContentType['ID']);
            if (is_array($Contents) && count($Contents) >= 1)
            {
                if ($Contents[0]['URLText'] != '') {
                    $link = "/" . $Contents[0]['URLText'];
                } else {
                    $link = "/content/?id=".$Contents[0]['ID'];
                }
                
                $menutodisplay .= "<li><a href='".$link."'";
                $offcanvas .= "<li><a href='".$link."'";
                
                if (isset($_SESSION['main']) && $_SESSION['main'] == $ContentType['ID'])
                {
                    $menutodisplay .= " class='active'";
                }
                $menutodisplay .= ">" . $ContentType['Title'] . "</a>";
                $offcanvas .= ">" . $ContentType['Title'] . "</a>";
                
                if (count($Contents) > 1 || count($CO->getLowerLevelPages($Contents[0]['ID'])) > 0) {
                    $menutodisplay .= "<ul class='menu submenu align-left";
                    //Check if this page is active - if so roll-out the menu
                    if (isset($_SESSION['main']) && $_SESSION['main'] == $ContentType['ID']) {
                        $menutodisplay .= " is-active";
                    }
                    $menutodisplay .= "'>";
                    $offcanvas .= "<ul class='off-canvas-menu vertical menu'>";
                    
                    foreach ($Contents as $CI) {
                        if ($CI['URLText'] != '') {
                            $link = "/" . $CI['URLText'];
                        } else {
                            $link = "/content/?id=" . $CI['ID'];
                        }
                        $menutodisplay .= "<li><a href='" . $link . "'";
                        $offcanvas .= "<li><a href='" . $link . "'";
                        
                        if (isset($_SESSION['sub']) && $_SESSION['sub'] == $CI['ID']) {
                            $menutodisplay .= " class='active'";
                        }
                        $menutodisplay .= ">" . $CI['MenuTitle'] . "</a>";
                        $offcanvas .= ">" . $CI['MenuTitle'] . "</a>";
                        
                        
                        //Lowest level pages?
                        $LowerContent = $CO->getLowerLevelPages($CI['ID']);
                        if (is_array($LowerContent) && count($LowerContent) > 0) {
                            $menutodisplay .= "<ul class='menu nested align-left'>";
                            $offcanvas .= "<ul class='off-canvas-menu vertical menu nested'>";
                            
                            //Add the parent item first for off-canvas
                            $offcanvas .= "<li><a href='" . $link . "'>" . $CI['MenuTitle'] . "</a>";
                            
                            foreach ($LowerContent as $LLC) {
                                if ($LLC['URLText'] != '' && $CI['URLText'] != '') {
                                    $link = "/" . $CI['URLText'] . "/" . $LLC['URLText'];
                                } else {
                                    $link = "/content/?id=" . $LLC['ID'];
                                }
                                
                                $menutodisplay .= "<li><a href='" . $link . "'";
                                $offcanvas .= "<li><a href='" . $link . "'";
                                
                                if (isset($_SESSION['subsub']) && $_SESSION['subsub'] == $LLC['ID']) {
                                    $menutodisplay .= " class='active'";
                                }
                                
                                $menutodisplay .= ">" . $LLC['MenuTitle'] . "</a></li>";
                                $offcanvas .= ">" . $LLC['MenuTitle'] . "</a></li>";
                            }
                            $menutodisplay .= "</ul>";
                            $offcanvas .= "</ul>";
                        }
                        $menutodisplay .= "</li>";
                        $offcanvas .= "</li>";
                    }
                    //Finish the menu item
                    $menutodisplay .= "</ul>";
                    $offcanvas .= "</ul>";
                }
                $menutodisplay .= "</li>";
                $offcanvas .= "</li>";
            }
        }
    }
    
    
    //End of menu and search
    /*$menutodisplay .= "<div class='search-section'>";
    $menutodisplay .= "<form action='/search/' method='get' class='searchbox'><p><input type='search' value='' name='SearchField' class='searchquery' placeholder='SEARCH' /><input type='hidden' name='action' value='search' /></p></form>";
    $menutodisplay .= "</div>";
    $menutodisplay .= "<div style='clear:both;'><!----></div>";*/
    
    //Finish off list menus
    $menutodisplay .= "</ul>";
    $offcanvas .= "</ul>";
    
    //Socials
    $offcanvas .= "<p class='off-canvas-social-links'>";
    if ($_SESSION['SiteSettings']['Social_Facebook'] != '') {
        $offcanvas .= "<a href='".$_SESSION['SiteSettings']['Social_Facebook']."' target='_blank'><img src='/assets/img/icons/icon-fb-white.svg' alt='Facebook' title='Facebook' /></a>";
    }
    if ($_SESSION['SiteSettings']['Social_Twitter'] != '') {
        $offcanvas .= "<a href='".$_SESSION['SiteSettings']['Social_Twitter']."' target='_blank'><img src='/assets/img/icons/icon-twitter-white.svg' alt='Twitter' title='Twitter' /></a>";
    }
    if ($_SESSION['SiteSettings']['Social_LinkedIn'] != '') {
        $offcanvas .= "<a href='".$_SESSION['SiteSettings']['Social_LinkedIn']."' target='_blank'><img src='/assets/img/icons/icon-linkedin-white.svg' alt='LinkedIn' title='LinkedIn' /></a>";
    }
    $offcanvas .= "</p>";
    
    $menutodisplay .= "</div>"; //end of top-bar-left
    
    $menutodisplay .= "</div></div></div>"; // end of rest!