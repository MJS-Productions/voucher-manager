( function () {
	'use strict';

	document.addEventListener( 'submit', function ( event ) {
		var form = event.target;

		if ( ! form.matches( '.voucher-manager__distribution-form' ) ) {
			return;
		}

		if ( form.dataset.vmSubmitting === '1' ) {
			event.preventDefault();
			return;
		}

		form.dataset.vmSubmitting = '1';

		Array.prototype.forEach.call(
			form.querySelectorAll( 'input[type=submit], button[type=submit]' ),
			function ( button ) {
				button.disabled = true;
				button.setAttribute( 'aria-disabled', 'true' );
			}
		);
	} );
}() );
