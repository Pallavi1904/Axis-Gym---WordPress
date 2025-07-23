/**
 * Chocolate choices' functionality.
 *
 * @since 1.9.6.1
 */
// eslint-disable-next-line no-unused-vars
const WPFormsChocolateChoices = {
	/**
	 * Initializes the chocolate choices component.
	 *
	 * @since 1.9.6.1
	 *
	 * @param {Object} $grid            The grid container for the choices.
	 * @param {Object} options          Object with name and choices properties.
	 * @param {string} options.name     The name attribute for the checkbox inputs.
	 * @param {Array}  options.choices  Array of choice objects with label and value properties.
	 * @param {Array}  options.selected Array of selected choice values.
	 */
	init( $grid, options ) {
		const selected = options.selected?.map?.( String ) ?? [];
		const $ = jQuery;
		/**
		 * Generate a random ID string.
		 * The ID is based on current timestamp and converted to a base-16 string.
		 *
		 * @since 1.9.6.1
		 *
		 * @return {string} A hexadecimal string representation of the current timestamp.
		 */
		const getRandomId = () => new Date().getTime().toString( 16 );

		/**
		 * Creates a single choice item.
		 *
		 * @since 1.9.6.1
		 *
		 * @param {Object|string|number} itemData The choice data object with label and value.
		 * @param {number}               index    The index of the choice item.
		 *
		 * @return {jQuery} The created choice item element.
		 */
		const createChoiceItem = ( itemData, index ) => {
			const id = `choice-${ index }-${ getRandomId() }`;
			const itemValue = String( typeof itemData === 'object' ? itemData.value : itemData );

			// Create the container div.
			const $itemDiv = $( '<div>', {
				class: 'choice-item',
			} );

			// Create the checkbox input.
			const $checkbox = $( '<input>', {
				type: 'checkbox',
				id,
				value: itemValue,
				checked: selected.includes( itemValue ),
				name: options.name.replace( '{index}', index ),
			} );

			// Create the label.
			const $label = $( '<label>', { for: id } );
			$label.text( itemData.label ?? itemData );

			// Append elements.
			$itemDiv.append( $checkbox, $label );

			return $itemDiv;
		};

		// Clear existing content.
		$grid.html( '' );
		const choices = [];

		// Populate the grid with items.
		$.each( options.choices, function( index, choiceData ) {
			choices.push( createChoiceItem( choiceData, index ) );
		} );

		$grid.append( choices );
	},
};
