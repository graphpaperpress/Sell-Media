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
 
registerBlockType( 'sellmedia/sell-media-all-items', {
    title: 'Sell Media Items',
    description: __( 'Block showing a Sell Media Items with different settings' ),
    icon: 'grid-view',
    category: 'sellmedia-blocks',
    attributes: {
        per_page: {
            type: 'string',
            default: 24,
        },
        show_title: {
            type: 'boolean',
            default: 1
        },
        quick_view: {
            type: 'boolean',
            default: 1
        },
        thumbnail_crop: {
            type: 'string',
            default: "medium"
        },
        thumbnail_layout: {
            type: 'string',
            default: "sell-media-three-col"
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
        const { attributes: { per_page, show_title, quick_view, thumbnail_crop, thumbnail_layout },
                setAttributes } = props;
        
        const panelbody_header = (
                <PanelBody
                    title={__('Settings', 'sell_media')}
                >
                    
                    <TextControl
                        label={__('Per Page', 'sell_media')}
                        value={per_page || ''}
                        type={'number'}
                        onChange={per_page => { setAttributes({ per_page }); macy_init(thumbnail_layout)}}
                    />
                    <SelectControl
                        key="thumbnail_crop"
                        label={__('Thumbnail Crop', 'sell_media')}
                        value={thumbnail_crop}
                        options={[
                            {
                                label: __('No Crop', 'sell_media'),
                                value: 'medium',
                            },
                            {
                                label: __('Square Crop', 'sell_media'),
                                value: 'sell_media_square',
                            }
                        ]}
                        onChange={ thumbnail_crop => { setAttributes({ thumbnail_crop }); macy_init(thumbnail_layout)}}
                    />
                    <SelectControl
                        key="thumbnail_layout"
                        label={__('Thumbnail Layout', 'sell_media')}
                        value={thumbnail_layout}
                        options={[
                            {
                                label: __('One Column', 'sell_media'),
                                value: 'sell-media-one-col',
                            },
                            {
                                label: __('Two Columns', 'sell_media'),
                                value: 'sell-media-two-col',
                            },
                            {
                                label: __('Three Columns', 'sell_media'),
                                value: 'sell-media-three-col',
                            },
                            {
                                label: __('Four Columns', 'sell_media'),
                                value: 'sell-media-four-col',
                            },
                            {
                                label: __('Five Columns', 'sell_media'),
                                value: 'sell-media-five-col',
                            },
                            {
                                label: __('Masonry Layout', 'sell_media'),
                                value: 'sell-media-masonry',
                            },
                            {
                                label: __('Horizontal Masonry Layout', 'sell_media'),
                                value: 'sell-media-horizontal-masonry',
                            }
                        ]}
                        onChange={ thumbnail_layout => { setAttributes({ thumbnail_layout }); macy_init(thumbnail_layout)}}
                    />
                    <ToggleControl
                        label={__('Show Title', 'sell_media')}
                        checked={!!show_title}
                        onChange={show_title => { setAttributes({ show_title }); macy_init(thumbnail_layout)}}
                    />
                    <ToggleControl
                        label={__('Quick View', 'sell_media')}
                        checked={!!quick_view}
                        onChange={quick_view => { setAttributes({ quick_view }); macy_init(thumbnail_layout)}}
                    />
                </PanelBody>
            );

        const inspectorControls = (
                <InspectorControls>
                    { panelbody_header }
                </InspectorControls>
            );

        function do_serverside_render( attributes ) {
                macy_init(thumbnail_layout)
                return <ServerSideRender
                block="sellmedia/sell-media-all-items"
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

function macy_init(thumbnail_layout) {
         
    if( thumbnail_layout == "sell-media-masonry" ) {
        setTimeout(function(){
            Macy.init({
            container: ".sell-media-grid-item-masonry-container",
            trueOrder: false,
            waitForImages: false,
            margin: 10,
            columns: 4,
            breakAt: {
                940: 3,
                768: 2,
                420: 1
            }
        }); }, 2000); 
    } 
}