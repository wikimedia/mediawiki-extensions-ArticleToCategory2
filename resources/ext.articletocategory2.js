( function () {

function clearText( theField ) {
	if ( theField.defaultValue === theField.value ) {
		theField.value = '';
	}
}

function addText( theField ) {
	if ( theField.value === '' ) {
		theField.value = theField.defaultValue;
	}
}

function addTextTitle( theField ) {
	if ( theField.value === '' ) {
		theField.value = theField.defaultValue;
	} else {
		theField.value = mw.config.get( 'wgFormattedNamespaces' )[ 14 ] + ':' + theField.value;
	}
}

function isEmptyX( form ) {
	if ( form.title.value === '' || form.title.value === form.title.defaultValue ) {
		return false;
	}
	return true;
}

$( function () {
	$( 'form[name="createbox"]' ).submit( function () {
		isEmptyX( this );
	} );

	$( '.createboxInput' ).focus( function () {
		clearText( this );
	} );

	$( '.add-page-btn' ).blur( function () {
		addText( this );
	} );

	$( '.add-cat-btn' ).blur( function () {
		addTextTitle( this );
	} );
} );

}() );
