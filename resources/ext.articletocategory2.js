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

	$( () => {
		$( 'form[name="createbox"]' ).on( 'submit', function () {
			isEmptyX( this );
		} );

		$( '.createboxInput' ).on( 'focus', function () {
			clearText( this );
		} );

		$( '.add-page-btn' ).on( 'blur', function () {
			addText( this );
		} );

		$( '.add-cat-btn' ).on( 'blur', function () {
			addTextTitle( this );
		} );
	} );

}() );
