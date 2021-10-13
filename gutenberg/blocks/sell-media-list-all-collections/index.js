import { registerBlockType } from '@wordpress/blocks';
import { components } from '@wordpress/components';
import { editor } from '@wordpress/editor';

const { __, _x, sprintf } = wp.i18n;
const {
    ServerSideRender,    
} = wp.components;

registerBlockType( 'sellmedia/sell-media-list-all-collections', {
    title: 'Sell Media Collection Items',
    description: __( 'Block showing a Sell Media Collection Items' ),
    icon: 'grid-view',
    category: 'sellmedia-blocks',
    attributes: {
        align: {
            type: 'string',
            default: 'full',
        },
    },
    supports: {
        align: true
    },  
    edit( props ) {
        function do_serverside_render() {
                return <ServerSideRender
                block="sellmedia/sell-media-list-all-collections"                
                />
        }
        return [          
            do_serverside_render(),
        ];
    },
    save: props => {
    },

});