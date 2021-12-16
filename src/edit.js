/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

// import {creds} from "../cred";
import { TagCheckboxControl } from './editor/TagCheckboxControl';
import { MunicipalityCheckboxControl } from './editor/MunicipalityCheckboxControl';
import { LanguageSelectControl } from './editor/LanguageSelectControl';
import { TargetGroupCheckboxControl } from './editor/TargetGroupCheckboxControl';
import { useState, useEffect } from '@wordpress/element';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const languages = [
		{ value: 'de', label: 'Saksa', checked: false },
		{ value: 'en', label: 'Englanti', checked: false },
		{ value: 'fi', label: 'Suomi', checked: false },
		{ value: 'ja', label: 'Japani', checked: false },
		{ value: 'ru', label: 'Venäjä', checked: false },
		{ value: 'sv', label: 'Ruotsi', checked: false },
		{ value: 'zh', label: 'Kiina', checked: false },
	];
	const targetGroupsList = [
		{ value: 'b2b', label: 'B2B', checked: false },
		{ value: 'b2c', label: 'B2C', checked: false },
		{ value: 'group', label: 'Group', checked: false },
		{ value: 'individual', label: 'Individual', checked: false },
	];

	const [ tags, setTags ] = useState( searchParams.tags );
	const [ municipalities, setMunicipalities ] = useState(
		searchParams.municipalities
	);
	const [ language, setLanguage ] = useState( languages );
	const [ targetGroups, setTargetGroups ] = useState( targetGroupsList );

	const handleTagChange = ( event, name ) => {
		let updatedTags = [ ...tags ];

		for ( let i in updatedTags ) {
			if ( updatedTags[ i ].tag == name ) {
				updatedTags[ i ].checked = event;
				break;
			}
		}

		setTags( updatedTags );

		let usedCategories = [];
		for ( let i in updatedTags ) {
			if ( updatedTags[ i ].checked == true ) {
				usedCategories.push( updatedTags[ i ].tag );
			}
		}
		const cats = usedCategories.join();
		setAttributes( { categories: cats } );
	};

	const handleMunicipalityChange = ( event, city, cityCode ) => {
		let updatedMunicipalities = [ ...municipalities ];

		for ( let i in updatedMunicipalities ) {
			if (
				updatedMunicipalities[ i ].city_code == cityCode &&
				updatedMunicipalities[ i ].city == city
			) {
				updatedMunicipalities[ i ].checked = event;
				break;
			}
		}

		setMunicipalities( updatedMunicipalities );

		let usedMunicipalities = [];
		for ( let i in updatedMunicipalities ) {
			if ( updatedMunicipalities[ i ].checked == true ) {
				usedMunicipalities.push( updatedMunicipalities[ i ].city_code );
			}
		}
		const cities = usedMunicipalities.join();
		setAttributes( { municipalities: cities } );
	};

	const handleLanguageChange = ( event ) => {
		let updatedLanguage = [ ...language ];

		for ( let i in updatedLanguage ) {
			if ( updatedLanguage[ i ].value == event ) {
				updatedLanguage[ i ].checked = true;
			} else {
				updatedLanguage[ i ].checked = false;
			}
		}

		setLanguage( updatedLanguage );
		setAttributes( { language: event } );
	};

	const handleTargetGroupChange = ( event, value ) => {
		let updatedTargetGroups = [ ...targetGroups ];

		for ( let i in updatedTargetGroups ) {
			if ( updatedTargetGroups[ i ].value == value ) {
				updatedTargetGroups[ i ].checked = event;
				break;
			}
		}

		setTargetGroups( updatedTargetGroups );
		let targets = [];
		for ( let i in updatedTargetGroups ) {
			if ( updatedTargetGroups[ i ].checked == true ) {
				targets.push( updatedTargetGroups[ i ].value );
			}
		}
		const targetsFinal = targets.join();
		setAttributes( { target_groups: targetsFinal } );
	};

	useEffect( () => {
		let usedTags = attributes.categories.split( ',' );

		usedTags.forEach( ( tag ) => {
			tags.find( ( o, i ) => {
				if ( tag === o.tag ) {
					o.checked = true;
					return true;
				}
			} );
		} );

		let usedMunicipalities = attributes.municipalities.split( ',' );

		usedMunicipalities.forEach( ( municipality ) => {
			municipalities.find( ( o ) => {
				if ( municipality === o.city_code ) {
					o.checked = true;
					return true;
				}
			} );
		} );

		let usedLanguage = attributes.language;
		language.find( ( o ) => {
			if ( usedLanguage == o.value ) {
				o.checked = true;
				return true;
			}
		} );

		let usedTargets = attributes.target_groups.split( ',' );
		usedTargets.forEach( ( target ) => {
			targetGroups.find( ( o ) => {
				if ( target === o.value ) {
					o.checked = true;
					return true;
				}
			} );
		} );
	}, [] );

	return (
		<div { ...useBlockProps() }>
			{
				<InspectorControls key="setting">
					<div id="visithame-datahub-controls">
						<fieldset>
							<legend className="blocks-base-control__label">
								{ __( 'Categories', 'visithame-datahub' ) }
							</legend>
							<TagCheckboxControl
								tags={ tags }
								onChange={ handleTagChange }
							/>
						</fieldset>
						<fieldset>
							<legend className="blocks-base-control__label">
								{ __( 'Municipalities', 'visithame-datahub' ) }
							</legend>
							<MunicipalityCheckboxControl
								municipalities={ municipalities }
								onChange={ handleMunicipalityChange }
							/>
						</fieldset>
						<fieldset>
							<legend className="blocks-base-control__label">
								{ __( 'Language', 'visithame-datahub' ) }
							</legend>
							<LanguageSelectControl
								languages={ language }
								value={ attributes.language }
								onChange={ handleLanguageChange }
							/>
						</fieldset>
						<fieldset>
							<legend className="blocks-base-control__label">
								Target group
							</legend>
							<TargetGroupCheckboxControl
								targetGroups={ targetGroups }
								// value={attributes.target_groups}
								onChange={ handleTargetGroupChange }
							/>
						</fieldset>
					</div>
				</InspectorControls>
			}
			<div>
				{ __(
					'DataHub – hello from the editor!',
					'visithame-datahub'
				) }
				<br />
			</div>
		</div>
	);
}
