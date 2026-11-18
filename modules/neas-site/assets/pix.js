( function () {
	function copyToClipboard( text ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text );
		}
		// Fallback for older browsers and plain-HTTP pages.
		return new Promise( function ( resolve, reject ) {
			var field = document.createElement( 'textarea' );
			field.value = text;
			field.setAttribute( 'readonly', '' );
			field.style.position = 'absolute';
			field.style.left = '-9999px';
			document.body.appendChild( field );
			field.select();
			try { document.execCommand( 'copy' ) ? resolve() : reject(); } catch ( error ) { reject( error ); }
			document.body.removeChild( field );
		} );
	}
	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.neas-pix-button' );
		if ( ! button ) { return; }
		var label = button.querySelector( '.neas-pix-text' );
		var originalText = label.textContent;
		var key = button.getAttribute( 'data-key' );
		copyToClipboard( key ).then( function () {
			label.textContent = 'Chave copiada!';
		}, function () {
			label.textContent = 'Copie: ' + key;
		} );
		setTimeout( function () { label.textContent = originalText; }, 2500 );
	} );
} )();
