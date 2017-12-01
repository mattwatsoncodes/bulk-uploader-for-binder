jQuery( document ).ready( function( $ ) {

    tinymce.PluginManager.add( 'mkdo_binder_document', function( editor, url ) {
        editor.addButton( 'mkdo_binder_document', {
            tooltip: 'Insert a document',
            icon: 'format-aside',
            onclick: function() {
				$( '[id="insert-media-button"]' ).click();
				$( '.media-menu .media-menu-item:contains("' + shortcodeUIData.strings.media_frame_menu_insert_label + '")' ).click();
				$( '[data-shortcode="binder_document"]' ).click();
            }
        } );
    });

} );
