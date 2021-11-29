/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { CheckboxControl, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
export const TargetGroupCheckboxControl = (props) => {
    const { onChange, targetGroups } = props;

    return (
        <>
            <div className="CheckboxContainer">
                {targetGroups.map((targetGroup) => {
                    return (
                        <CheckboxControl
                            className="CheckboxControl"
                            label={targetGroup.label}
                            checked={targetGroup.checked}
                            onChange={(event) => onChange(event, targetGroup.value)}
                            key={targetGroup.value}
                        />
                    )
                })}
            </div>
        </>
    )
}