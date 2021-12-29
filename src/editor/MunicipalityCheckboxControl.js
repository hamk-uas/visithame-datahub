/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { CheckboxControl, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
export const MunicipalityCheckboxControl = ( props ) => {
	const { onChange, municipalities } = props;
	const [ searchValue, setSearchValue ] = useState( '' );

	let filteredMunicipalities = municipalities.filter( ( municipality ) =>
		municipality.city.toLowerCase().includes( searchValue.toLowerCase() )
	);
	return (
		<>
			<TextControl
				label={"Search"}
				value={ searchValue }
				onChange={ ( value ) => setSearchValue( value ) }
			/>
			<div className="checkbox-container">
				{ filteredMunicipalities.map( ( municipality ) => {
					return (
						<CheckboxControl
							className="checkbox-control"
							label={ municipality.city }
							checked={ municipality.checked }
							onChange={ ( event ) =>
								onChange(
									event,
									municipality.city,
									municipality.city_code
								)
							}
							key={ municipality.city }
						/>
					);
				} ) }
			</div>
		</>
	);
};
