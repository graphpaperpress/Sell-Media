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
} = wp.components;
const {
    InspectorControls,
    RichText
} = wp.editor;
 
registerBlockType( 'sellmedia/sell-media-items-slider',{
    title: 'Sell Media Items Slider',
    description: __( 'Block showing a Sell Media Recent Items as a Slider' ),
    icon: 'grid-view',
    category: 'sellmedia-blocks',
    attributes: {
        item_title: {
            type: 'string',
            default: __('Recent Products', 'sell_media'),
        },
        total_items: {
            type: 'string',
            default: 10,
        },
        total_visible_items:{
            type: 'string',
            default: 3,
        },        
        show_title: {
            type: 'boolean',
            default: 1
        }, 
        gutter: {
            type: 'string',
            default: 10,
        },
        slider_controls:{
            type: 'boolean',
            default: 1
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
        const { attributes: { total_items, show_title, slider_controls, gutter,item_title,total_visible_items  },
                setAttributes } = props;
        
        const panelbody_header = (
                <PanelBody title={__('Settings', 'sell_media')}                
                >
                    <TextControl
                        label={__('Title', 'sell_media')}
                        value={item_title}
                        type={'string'}
                        onChange={item_title => setAttributes({ item_title })}
                    />
                    <TextControl
                        label={__('Total Items', 'sell_media')}
                        value={total_items}
                        type={'number'}
                        onChange={total_items => setAttributes({ total_items })}
                    />
                    <TextControl
                        label={__('Total Visible Items', 'sell_media')}
                        value={total_visible_items}
                        type={'number'}
                        onChange={total_visible_items => setAttributes({ total_visible_items })}
                    />
                    <ToggleControl
                        label={__('Show Slider Controls', 'sell_media')}
                        checked={!!slider_controls}
                        onChange={slider_controls => setAttributes({ slider_controls })}
                    />
                    <ToggleControl
                        label={__('Show Title', 'sell_media')}
                        value={show_title}
                        checked={!!show_title}
                        onChange={show_title => setAttributes({ show_title })}
                    />
                    <TextControl
                        label={__('Gutter', 'sell_media')}
                        value={gutter}
                        type={'number'}
                        onChange={gutter => setAttributes({ gutter })}
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
                block="sellmedia/sell-media-items-slider"
                attributes={ attributes }
                />                
        }

        return [
            inspectorControls,
            do_serverside_render( props.attributes ),
            recent_items(total_visible_items,slider_controls,gutter)
        ];
    },
    save: props => {
    },

});

function recent_items(total_visible_items,slider_controls,gutter) {    
   
    setTimeout(function() {
        const slider = tns({
            container: "#sell-media-recent-items",
            items: total_visible_items,
            navPosition:"bottom",
            controls:false,
            gutter:gutter,
            autoplay:true,
            nav:false,
            mouseDrag:true,
            autoplayButtonOutput:false,
        }); 
    }, 2000);
}