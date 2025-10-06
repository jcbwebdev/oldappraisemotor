<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CMS\Content;
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = "content";
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Content | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above space-below'>
        <div class='grid-x grid-margin-x grid-margin-y '>
            <div class='medium-3 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-sidemenu-admin.php'); ?>
            </div>
            <div class='medium-9 cell'>
                <h1>Content</h1>
                <p>
                    <a href="content_details.php?state=new"><i class="fi-plus"></i> Add new content</a> or click the line that you want to edit.
                </p>
                <?php
                    $ContentObj = new Content();
                    $ContentTypes = $ContentObj->getAllContentTypes();
                    
                    foreach ($ContentTypes as $ContentType) {
                        echo "<h2>".$ContentType['Title']."</h2>";
                        
                        $SectionContent = $ContentObj->getAllContentByType($ContentType['ID']);
                        if (count($SectionContent) > 0) {
                            echo "<table class=\"standard\">";
                            echo "<tr><th style='width: 40%;'>Content title</th><th style='width: 20%;'>Last Updated</th><th style='width: 40%;'>Lower pages</th></tr>";
                            foreach ($SectionContent as $Item) {
                                echo "<tr onmouseover=\"this.className='row_selected'\" onmouseout=\"this.className=''\" onclick=\"location.href='content_details.php?state=edit&id=".$Item['ID']."'\" style=\"cursor: pointer;\">";
                                echo "<td>".$Item['Title']."<br/><span class='content-url'>/".$Item['URLText']."</span></td><td><span class='content-updated-date'>".format_date($Item['DateDisplay'])." by</span><br/><span class='content-updated-by'>".$Item['AuthorName']."</span></td><td>";
                                $LowerLevelContent = $ContentObj->getLowerLevelPages($Item['ID']);
                                if (count($LowerLevelContent) > 0) {
                                    foreach ($LowerLevelContent as $LLC) {
                                        echo "<span class='content-lower-title'>".$LLC['Title']."</span><br/><span class='content-lower-url'>/".$Item['URLText']."/".$LLC['URLText']."</span><br/>";
                                    }
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        } else {
                            echo "<p>There is no content for this section yet.</p>\n";
                        }
                    }
                    
                    //Orphaned content
                    echo "<h2>Content not assigned to a section</h2>";
                    $SectionContent = $ContentObj->getAllContentByType();
                    if (count($SectionContent) > 0) {
                        echo "<table class=\"standard\">";
                        echo "  <tr><th style='width: 40%;'>Content title</th><th style='width: 20%;'>Last Updated</th><th style='width: 40%;'>Lower pages</th></tr>";
                        foreach ($SectionContent as $Item) {
                            echo "<tr onmouseover=\"this.className='row_selected'\" onmouseout=\"this.className=''\" onclick=\"location.href='content_details.php?state=edit&id=".$Item['ID']."'\" style=\"cursor: pointer;\">\n";
                            echo "<td>".$Item['Title']."<br/><span class='content-url'>/".$Item['URLText']."</span></td><td><span class='content-updated-date'>".format_date($Item['DateDisplay'])." by</span><br/><span class='content-updated-by'>".$Item['AuthorName']."</span></td><td>";
                            $LowerLevelContent = $ContentObj->getLowerLevelPages($Item['ID']);
                            if (count($LowerLevelContent) > 0) {
                                foreach ($LowerLevelContent as $LLC) {
                                    echo "<span class='content-lower-title'>".$LLC['Title']."</span><br/><span class='content-lower-url'>/".$Item['URLText']."/".$LLC['URLText']."</span><br/>";
                                }
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "<p>All content belongs to a section.</p>\n";
                    }
                ?>
            </div>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>