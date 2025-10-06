<?php
    use PeterBourneComms\CMS\ContentLibrary;
    
    function outputLibrary($table,$contentid) {
        if (is_string($table) && $table != '' && is_numeric($contentid) && $contentid > 0) {
            $CLO = new ContentLibrary();
            if (is_object($CLO)) {
                $Items = $CLO->listAllItems($contentid, 'content-id', $table);
                if (is_array($Items) && count($Items) > 0) {
                    echo "<h2 class='clearfix'>Image Gallery</h2>";
                    echo "<div class='content-library'>";
                    foreach ($Items as $Item) {
                        echo "<div class='library-item'>";
                        echo "<a href='".FixOutput($Item['FullPath'])."' data-lightbox='Content".$contentid."' title='".FixOutput($Item['Caption'])."'><img src='".FixOutput($Item['MediaPath']."small/".$Item['MediaFilename'].".".$Item['MediaExtension'])."' alt='".FixOutput($Item['Caption'])."' /></a>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
            }
        }
    }