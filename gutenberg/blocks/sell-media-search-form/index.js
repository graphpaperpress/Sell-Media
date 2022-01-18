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
    TextareaControl,
    FormFileUpload,
    ColorPicker,
    SelectControl,
    Button,
    Spinner,
    ResponsiveWrapper,
    ToolbarGroup,
    ToolbarButton,
} = wp.components;
const {
    InspectorControls,
    MediaUpload,
    MediaUploadCheck,
    BlockControls,
    BlockAlignmentToolbar,
} = wp.editor;
 
registerBlockType( 'sellmedia/sell-media-search-form', {
    title: 'Sell Media Search Form',
    description: __( 'Block showing a Sell Media Search Form with different settings' , 'sell_media' ),
    icon: 'search',
    category: 'sellmedia-blocks',
    attributes: {
        custom_label: {
            type: 'string',
            default:  __( 'Search Form' , 'sell_media' ),
        },
        custom_description: {
            type: 'string',
            default: __( 'You can search for the items based on keywords, different media files i.e images, videos, audios' , 'sell_media' ),
        },
        custom_color: {
            type: 'string',
            default: '#ccc'
        },
        bgImage: {
            type: 'object',
        },
        bgImageId: {
            type: 'integer',
        },
        position_image: {
            type: 'string',
            default: 'wide'
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
        const { attributes: { custom_label, custom_description, custom_color, bgImage, bgImageId, position_image },
                setAttributes } = props;

        const instructions = <p>{ __( 'To edit the background image, you need permission to upload media.', 'sell_media' ) }</p>;
        const ALLOWED_MEDIA_TYPES = [ 'image' ];
        const onUpdateImage = ( image ) => {
            setAttributes( {
                bgImageId: image.id,
                bgImage: image
            } );
        };

        const onRemoveImage = () => {
            setAttributes( {
                bgImageId: undefined,
                bgImage: ''
            } );
        };

        

        const panelbody_header = (
                <PanelBody
                    title={__('Settings', 'sell_media')}
                >

                    <BlockControls>
                        <BlockAlignmentToolbar
                            value={ position_image }
                            onChange={ ( position_image ) => {
                                setAttributes( { position_image:position_image || 'wide' } );
                            } }
                            controls={ [ 'left', 'wide', 'full' ] }
                        />
                    </BlockControls>
                    
                    <TextControl
                        label={__('Form Label', 'sell_media')}
                        value={custom_label || ''}
                        help={__('Set Custom label to show in search form', 'sell_media')}
                        type={'text'}
                        onChange={custom_label => setAttributes({ custom_label })}
                    />
                    <TextareaControl
                        label={__('Form Description', 'sell_media')}
                        value={custom_description || ''}
                        help={__('Set Custom Description to show in search form', 'sell_media')}
                        onChange={custom_description => setAttributes({ custom_description })}
                    />
                    <div class="components-base-control">
                        <label className="components-base-control__label">
                          {__('Search form background color', 'sell_media')}
                        </label>
                    </div>
                    <ColorPicker
                        color={custom_color}
                        onChangeComplete={(newval) => setAttributes({ custom_color: newval.hex })}
                        disableAlpha
                    />

                    <RadioControl
                        label={__('Layout', 'sell_media')}
                        className="layout_radio_control_custom"
                        help={__('Set the form layout for image position', 'sell_media')}
                        selected={ position_image }
                        options={ [
                            { label: 'Background', value: 'wide' },
                            { label: 'Left', value: 'right' },
                            { label: 'Top', value: 'full' },
                        ] }
                        onChange={ ( position_image ) => { setAttributes( { position_image } ) } }
                    />
                    <div className="wp-block-image-selector-example-image">

                        <MediaUploadCheck fallback={ instructions }>
                            <MediaUpload
                                title={ __( 'Layout image', 'image-selector-example' ) }
                                onSelect={ onUpdateImage }
                                allowedTypes={ ALLOWED_MEDIA_TYPES }
                                value={ bgImageId }
                                render={ ( { open } ) => (
                                    <Button
                                        className={ ! bgImageId ? 'editor-post-featured-image__toggle' : 'editor-post-featured-image__preview' }
                                        onClick={ open }>
                                        { ! bgImageId && ( __( 'Set Layout image', 'image-selector-example' ) ) }
                                        { !! bgImageId && ! bgImage && <Spinner /> }
                                        { !! bgImageId && bgImage &&
                                             
                                                <img src={ bgImage.url } alt={ __( 'Layout image', 'image-selector-example' ) } style={{width:'100%',height:200,position:'relative'}} />
                                             
                                        }
                                    </Button>
                                ) }
                            />
                        </MediaUploadCheck>

                        { !! bgImageId && bgImage &&
                            <MediaUploadCheck>
                                <MediaUpload
                                    title={ __( 'Background image', 'image-selector-example' ) }
                                    onSelect={ onUpdateImage }
                                    allowedTypes={ ALLOWED_MEDIA_TYPES }
                                    value={ bgImageId }
                                    render={ ( { open } ) => (
                                        <Button onClick={ open } isDefault isLarge>
                                            { __( 'Replace layout image', 'image-selector-example' ) }
                                        </Button>
                                    ) }
                                />
                            </MediaUploadCheck>
                        }
                        { !! bgImageId &&
                            <MediaUploadCheck>
                                <Button style={{paddingTop: 10}} onClick={ onRemoveImage } isLink isDestructive>
                                    { __( 'Remove layout image', 'image-selector-example' ) }
                                </Button>
                            </MediaUploadCheck>
                        }
                    </div>

                </PanelBody>
            );

        const inspectorControls = (
                <InspectorControls>
                    { panelbody_header }
                </InspectorControls>
            );

        function do_serverside_render( attributes ) {
                return <ServerSideRender
                block="sellmedia/sell-media-search-form"
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