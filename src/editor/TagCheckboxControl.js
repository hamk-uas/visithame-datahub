/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

import { CheckboxControl, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
export const TagCheckboxControl = ( props ) => {
	const { onChange, tags } = props;
	const [ category, setCategory ] = useState( '' );

	let filteredTags = tags.filter( ( tag ) =>
		tag.tag.toLowerCase().includes( category.toLowerCase() )
	);

	return (
		<>
			<TextControl
				label={"Search"}
				value={ category }
				onChange={ ( value ) => setCategory( value ) }
			/>
			<div className="checkbox-container">
				{ filteredTags.map( ( tag ) => {
					return (
						<CheckboxControl
							className="checkbox-control"
							label={ tag.tag }
							checked={ tag.checked }
							onChange={ ( event ) => onChange( event, tag.tag ) }
							key={ tag.tag }
						/>
					);
				} ) }
			</div>
		</>
	);
};
