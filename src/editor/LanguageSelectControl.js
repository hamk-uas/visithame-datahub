/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
export const LanguageSelectControl = ( props ) => {
	const { onChange, languages, value } = props;

	return (
		<>
			<SelectControl
				className="select-control"
				label={"Language"}
				value={ value }
				onChange={ onChange }
				options={ languages }
			/>
		</>
	);
};
