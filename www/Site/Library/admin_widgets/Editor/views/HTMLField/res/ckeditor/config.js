/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	config.toolbarGroups = [
        { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'undo'},
        { name: 'clipboard'},
        { name: 'others' },
        { name: 'tools' },
        { name: 'insert' },
		'/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph',   groups: [ 'align', 'indent'] },
        { name: 'lists' , groups: ['bidi', 'list','Code', 'blocks']},
        { name: 'colors' },
        '/',
        { name: 'styles' },
        { name: 'forms' },
        { name: 'links' },
        { name: 'about' }

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

    config.extraPlugins = 'syntaxhighlight,more';

};
