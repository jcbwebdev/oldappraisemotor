<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CMS\Content;
    use PeterBourneComms\CMS\ContentLibrary;
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = "content";
    
    if (!isset($_GET['state']) || $_GET['state'] == '') {
        header('Location:content_list.php');
        exit;
    }
    $state = $_GET['state'];
    if (isset($_GET['id'])) {
        $id = clean_int($_GET['id']);
    } else {
        $id = null;
    }
    if (isset($_GET['parentid'])) {
        $parentid = clean_int($_GET['parentid']);
    } else {
        $parentid = null;
    }
    
    
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        //This is an edit - take all variable from the SESSION
    } elseif ($state == 'new' || ($state == 'lower' && $id <= 0)) {
        //New item
        $ContentObj = new Content();
        unset($_SESSION['PostedForm']);
        $pagetitle = 'Content item';
        $_SESSION['PostedForm']['ParentID'] = $parentid;
        $_SESSION['PostedForm']['ImgPath'] = USER_UPLOADS.'/images/content-headers/';
    } elseif ($state == 'edit' || ($state == 'lower' && $id > 0 && $parentid > 0)) {
        if ($state == 'lower') {
            $ContentObj = new Content($id, true);
            $pagetitle = 'Lower level content item';
        } else {
            $ContentObj = new Content($id);
            $pagetitle = 'Content item';
        }
        
        if (is_object($ContentObj)) {
            $ContentEntry = $ContentObj->getItemById($id);
            
            if (is_array($ContentEntry) && count($ContentEntry) > 0) {
                $_SESSION['PostedForm'] = $ContentEntry;
                $_SESSION['PostedForm']['OldImgFilename'] = $ContentEntry['ImgFilename'];
            }
        } else {
            header('Location:content_list.php');
            exit;
        }
    }
    
    unset($_SESSION['error']);
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Content admin | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above space-below'>
        <div class='grid-x grid-margin-x grid-margin-y '>
            <div class='medium-3 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-sidemenu-admin.php'); ?>
            </div>
            <div class='medium-9 cell'>
                <h1><?php echo $pagetitle; ?></h1>
                <form action='content_detailsExec.php?state=<?php echo check_output($state); ?>' enctype='multipart/form-data' method='post' name='form1' id='form1' class='standard'>

                    <div class='grid-x grid-margin-x' data-equalizer='row1' data-equalize-on='medium'>
                        <div class='medium-6 cell'>
                            <div class='callout' data-equalizer-watch='row1'>
                                <?php if (isset($_SESSION['titleerror'])) {
                                    echo $_SESSION['titleerror'];
                                    unset($_SESSION['titleerror']);
                                } ?>
                                <p>
                                    <label for='Title'>Title:</label><input name='Title' type='text' id='Title' value='<?php echo check_output($_SESSION['PostedForm']['Title'] ?? null); ?>'/>
                                </p>
                                <?php if (isset($_SESSION['menutitleerror'])) {
                                    echo $_SESSION['menutitleerror'];
                                    unset($_SESSION['menutitleerror']);
                                } ?>
                                <p>
                                    <label for='MenuTitle'>Menu Title:</label><input name='MenuTitle' type='text' id='MenuTitle' value='<?php echo check_output($_SESSION['PostedForm']['MenuTitle'] ?? null); ?>' placeholder='As displayed in the menu'/>
                                </p>

                                <p>
                                    <label for='SubTitle'>Sub Title:</label><input name='SubTitle' type='text' id='SubTitle' value='<?php echo check_output($_SESSION['PostedForm']['SubTitle'] ?? null); ?>'/>
                                </p>
                                
                                <?php
                                    //list any sub pages this page may have plus option to create a new one
                                    if ($state == 'edit') {
                                        echo "<div class='callout secondary'>";
                                        echo "<h3>Lower level pages</h3>";
                                        echo "<p><a href=\"content_details.php?state=lower&parentid=".$id."\"><i class=\"fi-plus\"></i> <strong>Add a new lower level page</strong></a></p>\n";
                                        $LowerPages = $ContentObj->getLowerLevelPages();
                                        if (count($LowerPages) > 0) {
                                            echo "<ol>";
                                            foreach ($LowerPages as $LowerPage) {
                                                echo "<li><a href=\"content_details.php?state=lower&parentid=".$id."&id=".$LowerPage['ID']."\">".$LowerPage['Title']."</a></li>";
                                            }
                                            echo "</ol>";
                                        }
                                        echo "</div>";
                                    }
                                    
                                    if ($state == 'lower') {
                                        echo "<div class='callout secondary'>";
                                        if (isset($_SESSION['parenterror'])) {
                                            echo $_SESSION['parenterror'];
                                            unset($_SESSION['parenterror']);
                                        }
                                        
                                        echo "<p><label for='ParentID'>Content Parent:</label><select name='ParentID' id='ParentID'>";
                                        echo "<option value=''";
                                        if (isset($_SESSION['PostedFrom']['ParentID']) && $_SESSION['PostedForm']['ParentID'] <= 0) {
                                            echo " selected='selected'";
                                        }
                                        echo ">Select the parent page</option>";
                                        
                                        foreach ($ContentObj->getAllParentContent() as $ParentItem) {
                                            echo "<option value=\"".$ParentItem['ID']."\"";
                                            if (isset($_SESSION['PostedForm']['ParentID']) && $ParentItem['ID'] == $_SESSION['PostedForm']['ParentID']) {
                                                echo " selected=\"selected\"";
                                            }
                                            echo ">".$ParentItem['Title']."</option>";
                                        }
                                        echo "</select></p>";
                                        
                                        echo "<p><a href=\"content_details.php?state=edit&id=".$parentid."\"><i class=\"fi-arrow-left\"></i> Return to the Content page</a></p>";
                                        echo "</div>";
                                    }
                                ?>
                            </div>
                        </div>
                        <div class='medium-6 cell'>
                            <div class='callout warning' data-equalizer-watch='row1'>
                                <p>
                                    <label for='MetaTitle'>MetaTitle:</label><input name='MetaTitle' type='text' id='MetaTitle' value='<?php echo check_output($_SESSION['PostedForm']['MetaTitle'] ?? null); ?>'/>
                                </p>
                                <?php if (isset($_SESSION['urlerror'])) {
                                    echo $_SESSION['urlerror'];
                                    unset($_SESSION['urlerror']);
                                } ?>
                                <?php
                                    $prelink = SITEFQDN."/";
                                    if ($state == 'lower') {
                                        if (isset($_SESSION['ParentForm']['ParentID']) && $_SESSION['PostedForm']['ParentID'] > 0) {
                                            //Retrieve parent URL
                                            $tempCO = new Content();
                                            if (is_object($tempCO)) {
                                                $ParentContent = $tempCO->getItemById($_SESSION['PostedForm']['ParentID']);
                                                if (is_array($ParentContent) && count($ParentContent) > 0) {
                                                    $prelink .= $ParentContent['URLText']."/";
                                                }
                                            }
                                        }
                                    }
                                ?>
                                <label for='URLText'>URL of page:</label>
                                <div class='input-group'>
                                    <span class='input-group-label'>https://<?php echo $prelink; ?></span>
                                    <input class='input-group-field' name='URLText' type='text' id='URLText' value='<?php echo check_output($_SESSION['PostedForm']['URLText'] ?? null); ?>'
                                            placeholder='eg: about-us'/>
                                </div>
                                <p class='help-text'>Use this to give a human readable URL to the page - make it unique! (Don't use spaces)<br/>eg: about-us
                                </p>
                                <p>
                                    <label for='MetaDesc'>Meta Description:</label><textarea id='MetaDesc' name='MetaDesc' style='overflow:auto; height: 70px;' placeholder='The hidden page description for SEO'><?php echo check_output($_SESSION['PostedForm']['MetaDesc'] ?? null); ?></textarea>
                                </p>
                                <p>
                                    <label for='MetaKey'>Meta Keywords:</label><textarea id='MetaKey' name='MetaKey' style='overflow:auto; height: 50px;' placeholder='The hidden page key words for SEO'><?php echo check_output($_SESSION['PostedForm']['MetaKey'] ?? null); ?></textarea>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    
                    <?php if (isset($_SESSION['contenterror'])) {
                        echo $_SESSION['contenterror'];
                        unset($_SESSION['contenterror']);
                    } ?>
                    <p><label for='Content'>Content:</label>
                        <textarea id='Content' name='Content' style='overflow:auto; height: 300px;'><?php echo check_output($_SESSION['PostedForm']['Content'] ?? null); ?></textarea>
                    </p>

                    <div class='callout'>
                        <h3>Image</h3>
                        <p class='help-text'>Once you've dragged an image on, you can select a portion to show at the top of the page. You can only crop to the specified
                            proportions.</p>
                        <div id='image1'></div>
                    </div>

                    <!--
        <div class='grid-x grid-margin-x'>
            <div class='medium-6 cell'>
                <p><label for='Col2Content'>Col2 Content:</label>
                    <textarea id='Col2Content' name='Col2Content' style='overflow:auto; height: 300px;'><?php echo check_output($_SESSION['PostedForm']['Col2Content'] ?? null); ?></textarea>
                </p>
            </div>
            <div class='medium-6 cell'>
                <p><label for='Col3Content'>Col3 Content:</label>
                    <textarea id='Col3Content' name='Col3Content' style='overflow:auto; height: 300px;'><?php echo check_output($_SESSION['PostedForm']['Col3Content'] ?? null); ?></textarea>
                </p>
            </div>
        </div>
        -->


                    <div class='grid-x grid-margin-x'>
                        <div class='medium-6 cell'>
                            <p>
                                <label for='DisplayOrder'>Display Order:</label><input name='DisplayOrder' type='number' id='DisplayOrder' value='<?php echo check_output($_SESSION['PostedForm']['DisplayOrder'] ?? null); ?>'/><span class='help-text'>Standard ascending numerical order (1 for top, 1000 for bottom etc)</span>
                            </p>

                            <div class='callout warning'>
                                <h3>Special content</h3>
                                <p>If you select content in the drop-down list below it will appear
                                    <strong>after</strong> the main content above. Important for displaying items like the Our People information etc.
                                </p>
                                <select name='SpecialContent' id='SpecialContent'>
                                    <?php
                                        foreach ($globalSpecialContentTypes as $Item) {
                                            echo "<option value='".$Item['Value']."'";
                                            if (isset($_SESSION['PostedForm']['SpecialContent']) && $_SESSION['PostedForm']['SpecialContent'] == $Item['Value']) {
                                                echo " selected='selected'";
                                            }
                                            echo ">".$Item['Label']."</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class='medium-6 cell'>
                            <?php
                                if ($state != 'lower') {
                                    echo "<div class='callout'>";
                                    echo "<h3>Which sections?</h3>";
                                    
                                    //Need to trawl the list of Content Types in table ContentTypes - to create the list.
                                    //THEN need to correlate it with any entries in the ContentByType table.
                                    
                                    $ContentAreas = $ContentObj->getAllContentTypes();
                                    
                                    if (is_array($ContentAreas) && count($ContentAreas) > 0) {
                                        foreach ($ContentAreas as $ContentArea) {
                                            echo "<p><input type='checkbox' name='Cat".$ContentArea['ID']."' id='Cat".$ContentArea['ID']."' value='".$ContentArea['ID']."' class='radio'";
                                            
                                            #Now check to see if there is a record in the ByCat table
                                            if ($state != 'new') {
                                                if ($ContentObj->checkSectionMatch($id, $ContentArea['ID']) === true) {
                                                    echo " checked='checked'";
                                                }
                                            } else {
                                                if (isset($_SESSION['Cat'.$ContentArea['ID']]) && $_SESSION['Cat'.$ContentArea['ID']] == $ContentArea['ID']) {
                                                    echo " checked='checked'";
                                                }
                                            }
                                            echo "/>&nbsp;".$ContentArea['Title']."</p>";
                                        }
                                    }
                                    echo '</div>';
                                }
                            ?>
                        </div>
                    </div>

                    <div class='callout success'>
                        <h2>Save your changes</h2>
                        <p class='lead'>Nothing is saved until you press the 'Save' button below:</p>
                        <input type='hidden' id='ID' name='ID' value='<?php echo FixOutput($_SESSION['PostedForm']['ID'] ?? null); ?>'/>
                        <p>
                            <button class='button' type='submit' value='submit'>Save</button>
                        </p>
                    </div>


                    <div class='callout alert'>
                        <p><strong>PBC use only</strong></p>
                        <div class='input-group'>
                            <span class='input-group-label'>Link:</span>
                            <input class='input-group-field' name='Link' type='text' id='Link' value='<?php echo check_output($_SESSION['PostedForm']['Link'] ?? null); ?>'/>
                            <div class='input-group-button'>
                                <button class='button' onclick="openFileManager('Link'); return false;" onmouseover="this.className='button down'" onmouseout="this.className='button'">Browse Server</button>
                            </div>
                        </div>
                        <span class='help-text'><strong>Notes:</strong><br/>1. You can type a link (of the form <em><strong>https://<?php echo SITEFQDN; ?>/filename.php</strong></em> or just a local link in the form of <em>/path/to/file.php</em>) or click <strong>Browse Server</strong> to upload/select a file.<br/>2. If you enter a page link (or document link) then the content below WILL NOT be displayed - and the link will be used instead.</span>
                    </div>

                    <div class='callout alert'>
                        <h3>Delete</h3>
                        <div class='switch large'>
                            <input class='switch-input' id='delete' type='checkbox' name='delete' value='1'>
                            <label class='switch-paddle' for='delete'>
                                <span class='show-for-sr'>Delete?</span>
                                <span class='switch-active' aria-hidden='true'>Yes</span>
                                <span class='switch-inactive' aria-hidden='true'>No</span>
                            </label>
                        </div>
                        <p class='help-text'>Slide switch to Yes and Click Submit - page will be deleted.</p>
                    </div>

                </form>
            </div>
        </div>
        
        
        <?php
            if (($state == 'edit' || $state == 'lower') && $id > 0) {
                echo "<div class='library-container' id='content-library'></div>";
            }
        ?>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./content_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> Content list</a>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script src='/vendor/ckeditor/ckeditor.js'></script>
    <script>
        $(document).ready(function () {
            //Cropper init stuff
            var drop = new createImageCropper({
                imageContainer: 'image1',
                instanceCounter: '1',
                width: 1600,
                height: 700,
                formName: 'form1',
                outputElem: 'ImgFile',
                oldField: 'OldImgFilename',
                origPath: '<?php echo FixOutput($_SESSION['PostedForm']['ImgPath'] ?? ''); ?>',
                origImg: '<?php echo FixOutput($_SESSION['PostedForm']['OldImgFilename'] ?? ''); ?>',
                deleteID: '<?php echo FixOutput($_SESSION['PostedForm']['ID'] ?? ''); ?>',
                dialogText: 'Are you sure you want to delete this image?',
                thumbnails: 'N',
                restoreSize: 250,
                scriptToRun: '/admin/ajax/_imageHandler.php',
                contentType: 'content'
            });
            CKEDITOR.replace('Content', {
                height: '450px'
            });
            /*CKEDITOR.replace('Col2Content', {
                height: '300px'
            });
            CKEDITOR.replace('Col3Content', {
                height: '300px'
            });*/

            //Setup ContentLibrary
            var cl = new createContentLibrary({
                libraryContainer: 'content-library',
                maxDimension: 1000,
                contentParentTable: 'Content',
                contentID: '<?php echo $id; ?>'
            });

        });
        function openFileManager(field) {
            window.Filemanager = {
                callBack: function (url) {
                    $(field).val(url);
                    console.log(url);
                    window.Filemanager = null;
                }
            };
            window.open('/vendor/filemanager/dialog.php', 'filemanager_box',
                'status=0, toolbar=0, location=0, menubar=0, directories=0, ' +
                'resizable=1, scrollbars=0, width=800, height=600'
            );
        }
        

    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>