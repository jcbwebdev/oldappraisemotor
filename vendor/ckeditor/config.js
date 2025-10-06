/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.on('instanceReady', function (ev) {
// Ends self closing tags the HTML4 way, like <br>.
    ev.editor.dataProcessor.htmlFilter.addRules(
        {
            elements:
                {
                    $: function (element) {
                        // Output dimensions of images as width and height
                        if (element.name == 'img') {
                            var style = element.attributes.style;

                            if (style) {
                                // Get the width from the style.
                                var match = /(?:^|\s)width\s*:\s*(\d+)px/i.exec(style),
                                    width = match && match[1];

                                // Get the height from the style.
                                match = /(?:^|\s)height\s*:\s*(\d+)px/i.exec(style);
                                var height = match && match[1];

                                if (width) {
                                    element.attributes.style = element.attributes.style.replace(/(?:^|\s)width\s*:\s*(\d+)px;?/i, '');
                                    element.attributes.width = width;
                                }

                                if (height) {
                                    element.attributes.style = element.attributes.style.replace(/(?:^|\s)height\s*:\s*(\d+)px;?/i, '');
                                    element.attributes.height = height;
                                }
                            }
                        }



                        if (!element.attributes.style)
                            delete element.attributes.style;

                        return element;
                    }
                }
        });
});

CKEDITOR.stylesSet.add( 'pbc_styles',
    [
        // Block-level styles
        //{ name : 'Caption', element : 'p', attributes : { 'class' : 'caption' }}
        { name : 'Lead para', element : 'p', attributes : { 'class' : 'lead' }}

    ]);



CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here.
    // For complete reference see:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    config.filebrowserBrowseUrl = '/vendor/filemanager/dialog.php?type=2&editor=ckeditor&fldr=files';
    config.filebrowserUploadUrl = '/vendor/filemanager/dialog.php?type=2&editor=ckeditor&fldr=files';
    config.filebrowserImageBrowseUrl = '/vendor/filemanager/dialog.php?type=1&editor=ckeditor&fldr=images';
    config.filebrowserImageUploadUrl = '/vendor/filemanager/dialog.php?type=1&editor=ckeditor&fldr=images';

    config.entities = false;

    config.extraAllowedContent = 'div[id]; object[id,name,width,height]; param[name,value]; embed[src,type,allowscriptaccess,allowfullscreen,wmode,width,height]';

    config.extraPlugins = 'indent,indentblock,indentlist,justify,dialogadvtab,showborders';

    config.allowedContent = true;

    config.toolbar = 'Full';

    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
        { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
        { name: 'links' },
        { name: 'insert' },
        { name: 'forms' },
        { name: 'tools' },
        { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'others' },
        '/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
        { name: 'styles' },
        { name: 'colors' },
        { name: 'about' }
    ];

    // Remove some buttons provided by the standard plugins, which are
    // not needed in the Standard(s) toolbar.
    config.removeButtons = 'Underline';

    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Simplify the dialog windows.
    //config.removeDialogTabs = 'image:advanced;link:advanced';

    config.toolbar_Full =
        [
            { name: 'document', items : [ 'Source' ] },
            { name: 'clipboard', items : [ 'Cut','Copy','Paste','RemoveFormat','-','Undo','Redo' ] },
            { name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
            { name: 'tools', items : [ 'Maximize', 'ShowBlocks','-','About' ] },
            { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript' ] },
            { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight' ] },
            { name: 'links', items : [ 'Link','Unlink' ] },
            { name: 'insert', items : [ 'Image','Table','SpecialChar','Iframe' ] },
            { name: 'styles', items : [ 'Styles','Format' ] }
        ];

    config.toolbar_Basic =
        [
            ['Paste','RemoveFormat','-','Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link',]
        ];


    config.toolbar_Basic_NoLinks =
        [
            ['Paste','RemoveFormat','-','Bold', 'Italic', '-', 'NumberedList', 'BulletedList']
        ];


    //config.contentsCss = '/assets/css/editor_styles.min.css';

    config.stylesSet = 'pbc_styles';
};
