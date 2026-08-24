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

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '#vm-copy-distributed-code' );
		var code;
		var copyLabel;
		var copiedLabel;

		if ( ! button || ! navigator.clipboard ) {
			return;
		}

		code = document.getElementById( 'vm-distributed-code' );
		if ( ! code ) {
			return;
		}

		copyLabel = button.dataset.copyLabel || button.textContent || '';
		copiedLabel = button.dataset.copiedLabel || copyLabel;

		navigator.clipboard.writeText( code.textContent || '' ).then( function () {
			if ( copiedLabel === copyLabel ) {
				return;
			}

			button.textContent = copiedLabel;
			window.setTimeout( function () {
				button.textContent = copyLabel;
			}, 1600 );
		} );
	} );
}() );
