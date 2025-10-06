<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */

    use PeterBourneComms\CMS\ContentLibrary;

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        include("../../assets/dbfuncs.php");
        
        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

        $mode = $_POST['mode'] ?? null;
        $ContentID = $_POST['ContentID'] ?? null;
        $ContentParentTable = $_POST['ContentParentTable'] ?? null;
        $MediaType = $_POST['MediaType'] ?? null;
        //$DisplayOrder = $_POST['DisplayOrder'];
        $Caption = $_POST['Caption'] ?? null;
        //$Filename = $_POST['UploadFilename'];
        $MediaMimeType = $_POST['MediaMimeType'] ?? null;
        $MediaPath = $_POST['MediaPath'] ?? null;
        $ID = $_POST['ID'] ?? null;
        $FileBlob = $_POST['FileBlob'] ?? null;
        
        if ($MediaPath == '') { $MediaPath = "/user_uploads/images/content-library/"; }
        
        //Create File object
        $CLO = new ContentLibrary($ID, 1000, 250, $MediaPath);
        if (!is_object($CLO)) {
            echo "ooh";
            die();
        }
        
        $html = "";
        $img = "";
        
        /*
         * Various modes: list, insert, delete
         */
        switch ($mode) {
            case 'list':
                $Files = $CLO->listAllItems($ContentID,'content-id',$ContentParentTable);
                //print_r($Files);
                if (is_array($Files) && count($Files) > 0) {
                    $html = "";
                    foreach ($Files as $File) {
                        //Only display selected doc type (passport, cert etc)
                        $html .= "<div class='media-panel' id='MediaPanel_" . $File['ID'] . "' data-upload-id='" . $File['ID'] . "' data-content-id='" . $ContentID . "' data-table='".$ContentParentTable."' data-display-order='".$File['DisplayOrder']."'>";
                        //Delete button
                        $html .= "<button class='close-button upload-delete-button' type='button' data-upload-id='" . $File['ID'] . "' data-table='".$ContentParentTable."' data-content-id='".$ContentID."' >&times;</button>";
                        //Content
                        $html .= "<img src='" . FixOutput($File['FullPath']) . "' alt='" . FixOutput($File['Caption']) . "'/>";
                        $html .= "<p><label>Caption:</label>";
                        $html .= "<textarea class='media-update' name='Caption_" . $File['ID'] . "' id='Caption_" . $File['ID'] . "' data-upload-id='" . $File['ID'] . "' data-content-id='" . $ContentID . "' data-contenttype='" . $File['ContentParentTable'] . "' data-parent-table='".$File['ContentParentTable']."' data-mode='update-caption' data-field='Caption'>" . check_output($File['Caption']) . "</textarea>";
                        /*if ($File['Caption'] != '') {
                            $html .= "<label>Caption</label><strong>" . $File['Caption'] . "</strong><br/>";
                        }*/
                        $html .= "</p>";
                        $html .= "</div>";
                    }
                } else {
                    $html = "<p><em>No files added for this content yet</em></p>";
                }
                break;


            case 'update-order':
                $i = 1;
                if (isset($_POST['MediaPanel']) && is_array($_POST['MediaPanel'])) {
                    foreach ($_POST['MediaPanel'] as $value) {
                        $CLO->getItemById($value);
                        $CLO->setDisplayOrder($i);
                        $CLO->saveItem();
                        $i++;
                    }
                }
                //print_r($_POST);

                break;
    
                
            case 'update-caption':
                $File = $CLO->getItemById($ID);
                if (is_array($File) && count($File) > 0) {
                    $CLO->setCaption($Caption);
                    $result = $CLO->saveItem();
                }
    
                if ($result != true) {
                    $error = "Could not save Caption";
                }
                break;


            case 'insert':
                if ($FileBlob == '') {
                    $error = "Please upload an image or PDF.";
                } else {
                    $CLO->createItem();
                    $CLO->setContentId($ContentID);
                    $CLO->setMediaType($MediaType);
                    $CLO->setCaption($Caption);
                    $CLO->setContentParentTable($ContentParentTable);
                    //$CLO->setDisplayOrder($DisplayOrder);
        
                    //Do different things - if this is a Virtual tour etc - they are external links.
                    if ($MediaType == 'VirtualTour' || $MediaType == 'AudioTour') { // Not relevant in the generic setting
                        //No blob - just a complete path
                        $CLO->setMediaPath($MediaPath);
                        $CLO->setMediaFilename(null);
                        $CLO->setMediaExtension(null);
                        $CLO->setMediaMimeType(null);
                    } else {
                        //A File of some sort... is it an image or a PDF...?
                        $CLO->setMediaPath($MediaPath);
                        //Derive the extension from the MIME type
                        $ext = $CLO->deriveExtension($MediaMimeType);
                        $CLO->setMediaExtension($ext);
                        $Filename = $ContentID . "_" . $MediaType . "_" . date('YmdHis', time());
                        $CLO->setMediaFilename($Filename);
            
                        //Do we process as image or file
                        if ($MediaMimeType == 'image/jpeg' || $MediaMimeType == 'image/jpg' || $MediaMimeType == 'image/png') {
                            //Set thumbnails up
                            $CLO->setMediaThumb('Y');
                            //Process
                            $CLO->processImage($FileBlob, $Filename);
                        } else {
                            $CLO->processFile($FileBlob, $MediaMimeType, $Filename);
                        }
            
                    }
                    $result = $CLO->saveItem();
                    $ID = $CLO->getID();
        
                    if ($result == true) {
                        //Nothing (content will get reloaded by AJAX on host page)
                        //But lets send an imag back as an additional parameter for testing
                        $item = $CLO->getItemById($ID);
                        $img = "<img src='".$item['MediaPath']."small/".$item['MediaFilename'].".".$item['MediaExtension']."' />";
                    } else {
                        $error = "There was a problem uploading your media to the system. Please try again.";
                    }
                }
                break;


            case 'delete':
                if (!is_numeric($ID) || $ID <= 0) {
                    $error = "No ID passed.";
                } else {
                    $File = $CLO->getItemById($ID);
                    if (!is_array($File)) {
                        $result = false;
                    } else {
                        $result = $CLO->deleteItem();
                    }

                    if ($result == true) {
                        $ret_result = true;
                        //Page will reload on true (well the ajax data will)
                    } else {
                        $error = "There was a problem deleting that image. Please try again.";
                    }
                }
                break;


            default:
                die();
                break;
        }


        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true, 'detail' => $html, 'additional' => $img);
        }

        echo json_encode($retdata);
    }