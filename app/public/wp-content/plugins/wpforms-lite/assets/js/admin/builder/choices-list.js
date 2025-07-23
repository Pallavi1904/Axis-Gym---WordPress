/**
 * Checkbox selection functionality.
 *
 * @since 1.9.6.1
 */
// eslint-disable-next-line no-unused-vars
const WPFormsChoicesList = {
	/**
	 * Creates and returns a checkbox selection manager.
	 *
	 * @since 1.9.6.1
	 *
	 * @param {jQuery} $container The container element with checkboxes.
	 */
	init( $container ) {
		if ( $container.data( 'choices-list-initialized' ) ) {
			return;
		}

		$container.data( 'choices-list-initialized', true );

		// Private variables to store state.
		const $selectAllCheckbox = $container.find( 'input[value="select-all"]' );
		const $itemCheckboxes = $container.find( '.item-checkbox' );

		/**
		 * Checks if the container has the necessary elements to initialize.
		 *
		 * @since 1.9.6.1
		 *
		 * @param {Object} $rootContainer The root container element to query for required elements.
		 *
		 * @return {boolean} True if the root container contains specific elements required for initialization, otherwise false.
		 */
		const canInitialize = ( $rootContainer ) => {
			const hasSelectAll = $rootContainer.find( 'input[value="select-all"]' ).length > 0;
			const hasItemCheckboxes = $rootContainer.find( '.item-checkbox' ).length > 0;
			return hasSelectAll && hasItemCheckboxes;
		};

		/**
		 * Updates the state of the "Select All" checkbox based on the current
		 * state of individual item checkboxes. Determines whether all, none,
		 * or some items are selected and updates the "Select All" checkbox.
		 *
		 * @since 1.9.6.1
		 */
		const updateSelectAllState = () => {
			this.updateSelectAllState( $container );
		};

		/**
		 * Handles the change event for the "Select All" checkbox. Updates the state
		 * of individual item checkboxes and clears the indeterminate state of the
		 * "Select All" checkbox.
		 *
		 * @since 1.9.6.1
		 *
		 * @param {Event} event The event object associated with the change event triggered on the "Select All" checkbox.
		 */
		const handleSelectAllChange = ( event ) => {
			const isChecked = jQuery( event.target ).prop( 'checked' );

			// Update all item checkboxes.
			$itemCheckboxes.prop( 'checked', isChecked );

			// Clear indeterminate state.
			$selectAllCheckbox.prop( 'indeterminate', false );
		};

		/**
		 * Binds event listeners to the "select all" checkbox and item checkboxes.
		 *
		 * @since 1.9.6.1
		 */
		const bindEvents = () => {
			// Add event listener to "select all" checkbox.
			$selectAllCheckbox.on( 'change', handleSelectAllChange );

			// Add event listeners to item checkboxes.
			$itemCheckboxes.on( 'change', updateSelectAllState );
		};

		// Return early if required elements aren't found.
		if ( ! canInitialize( $container ) ) {
			return;
		}

		// Initialize event bindings.
		bindEvents();

		// Initialize the state.
		updateSelectAllState();
	},

	/**
	 * Updates the select all checkbox state based on the current checked state of item checkboxes.
	 * This method can be called from outside the class to refresh the state after DOM changes.
	 *
	 * @since 1.9.6.1
	 *
	 * @param {jQuery} $container The container element with checkboxes.
	 */
	updateSelectAllState( $container ) {
		const $selectAllCheckbox = $container.find( 'input[value="select-all"]' );
		const $itemCheckboxes = $container.find( '.item-checkbox' );

		const totalItems = $itemCheckboxes.length;
		const checkedItems = $itemCheckboxes.filter( ':checked' ).length;

		if ( checkedItems === 0 ) {
			// None checked.
			$selectAllCheckbox.prop( {
				checked: false,
				indeterminate: false,
			} );
		} else if ( checkedItems === totalItems ) {
			// All checked.
			$selectAllCheckbox.prop( {
				checked: true,
				indeterminate: false,
			} );
		} else {
			// Some checked.
			$selectAllCheckbox.prop( {
				checked: false,
				indeterminate: true,
			} );
		}
	},
};
