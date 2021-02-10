import { registerBlockType } from '@wordpress/blocks';
import { components } from '@wordpress/components';
import { editor } from '@wordpress/editor';

const { __, _x, sprintf } = wp.i18n;
const {
    ServerSideRender,
    RadioControl,
    PanelBody,
    ToggleControl,
    TextControl,
    SelectControl,
} = wp.components;
const {
    InspectorControls
} = wp.editor;
 
registerBlockType( 'sellmedia/sell-media-filters', {
    title: 'Sell Media Filters',
    description: __( 'Block showing a Sell Media Items with different settings' ),
    icon: 'search',
    category: 'sellmedia-blocks',
    attributes: {
        all: {
            type: 'boolean',
            default: 1
        },
        newest: {
            type: 'boolean',
            default: 0
        },
        most_popular: {
            type: 'boolean',
            default: 0
        },
        collections: {
            type: 'boolean',
            default: 0
        },
        keywords: {
            type: 'boolean',
            default: 0
        },
        align: {
            type: 'string',
            default: 'full',
        },
    },
    supports: {
        align: true
    },
    edit( props ) {
        const { attributes: { all, newest, most_popular, collections, keywords },
                setAttributes } = props;
        function handle_all_option_otherevent(all) {
            if (all==true) {
                props.setAttributes( { newest: 1 } );
                props.setAttributes( { most_popular: 1 } );
                props.setAttributes( { collections: 1 } );
                props.setAttributes( { keywords: 1 } );
            }
        }
        function handle_other_option_allevent(newest,most_popular,collections,keywords) {
            if (newest==1 || most_popular==1 || collections==1 || keywords==1) {
                props.setAttributes( { all: 0 } );
            }

            if (newest==1 && most_popular==1 && collections==1 && keywords==1) {
                props.setAttributes( { all: 1 } );
                handle_all_option_otherevent(all);
            }
        }
        const panelbody_header = (
                <PanelBody
                    title={__('Settings', 'sell_media')}
                >
                    <ToggleControl
                        label={__('All', 'sell_media')}
                        checked={!!all}
                        onChange={all => {handle_all_option_otherevent(all); setAttributes({ all })}}
                    />
                    <ToggleControl
                        label={__('Newest', 'sell_media')}
                        checked={!!newest}
                        onChange={newest => {handle_other_option_allevent(newest,most_popular,collections,keywords); setAttributes({ newest })}}
                    />
                    <ToggleControl
                        label={__('Most Popular', 'sell_media')}
                        checked={!!most_popular}
                        onChange={most_popular => {handle_other_option_allevent(newest,most_popular,collections,keywords); setAttributes({ most_popular })}}
                    />
                    <ToggleControl
                        label={__('Collections', 'sell_media')}
                        checked={!!collections}
                        onChange={collections => {handle_other_option_allevent(newest,most_popular,collections,keywords); setAttributes({ collections })}}
                    />
                    <ToggleControl
                        label={__('Keywords', 'sell_media')}
                        checked={!!keywords}
                        onChange={keywords => {handle_other_option_allevent(newest,most_popular,collections,keywords); setAttributes({ keywords })}}
                    />
                </PanelBody>
            );

        const inspectorControls = (
                <InspectorControls>
                    { panelbody_header }
                </InspectorControls>
            );

        function do_serverside_render( attributes ) {
                return <ServerSideRender
                block="sellmedia/sell-media-filters"
                attributes={ attributes }
                />
                
        }

        return [
            inspectorControls,
            do_serverside_render( props.attributes )
        ];
    },
    save: props => {

    },

} );