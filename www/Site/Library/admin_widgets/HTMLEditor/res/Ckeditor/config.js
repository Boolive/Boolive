/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
        { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'undo'},
        { name: 'clipboard'},
        { name: 'others' },
        { name: 'about' },
        { name: 'tools' },
		'/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph',   groups: [ 'align', 'list', 'indent', 'blocks', 'bidi' ] },
        { name: 'colors' },
        '/',
        { name: 'styles' },
        { name: 'forms' },
        { name: 'links' },
        { name: 'insert' }

	];


	// Remove some buttons, provided by the standard plugins, which we don't
	// need to have in the Standard(s) toolbar.
	config.removeButtons = '';

	// Se the most common block elements.
	config.format_tags = 'p;h1;h2;h3;h4;h5;pre';

	// Make dialogs simpler.
	config.removeDialogTabs = 'image:advanced;link:advanced';

    config.filebrowserImageUploadUrl = '/?proto=Image';
    config.filebrowserFlashUploadUrl = '/?proto=Flash';
};
