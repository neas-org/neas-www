( function ( wp ) {
	var el = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var ServerSideRender = wp.serverSideRender;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var TextControl = wp.components.TextControl;

	// Registers a server-rendered block, optionally with a settings panel.
	function registerBlock( name, title, settingsPanel ) {
		wp.blocks.registerBlockType( name, {
			title: title,
			edit: function ( props ) {
				var children = [];
				if ( settingsPanel ) {
					children.push( el( InspectorControls, { key: 'settings' }, el( PanelBody, { title: 'Configurações' }, settingsPanel( props ) ) ) );
				}
				children.push( el( 'div', { key: 'preview', style: { pointerEvents: 'none' } }, el( ServerSideRender, { block: name, attributes: props.attributes } ) ) );
				return el( 'div', useBlockProps(), children );
			},
			save: function () {
				return null;
			}
		} );
	}

	function textControl( props, attribute, label ) {
		return el( TextControl, {
			key: attribute,
			label: label,
			value: props.attributes[ attribute ],
			onChange: function ( value ) {
				var change = {};
				change[ attribute ] = value;
				props.setAttributes( change );
			}
		} );
	}

	registerBlock( 'neas/next-lecture', 'Próxima palestra (cartão)' );
	registerBlock( 'neas/upcoming-lectures', 'Próximas palestras (lista)', function ( props ) {
		return el( RangeControl, {
			label: 'Quantas palestras mostrar',
			min: 1,
			max: 20,
			value: props.attributes.count,
			onChange: function ( value ) { props.setAttributes( { count: value } ); }
		} );
	} );
	registerBlock( 'neas/lecture-schedule', 'Programação completa de palestras' );
	registerBlock( 'neas/pix', 'Doação Pix', function ( props ) {
		return [
			textControl( props, 'key', 'Chave Pix (só números)' ),
			textControl( props, 'display', 'Chave como aparece no site' ),
			textControl( props, 'qr', 'Endereço da imagem do QR Code (opcional)' )
		];
	} );
	registerBlock( 'neas/map', 'Mapa (Google Maps)', function ( props ) {
		return textControl( props, 'address', 'Endereço' );
	} );
} )( window.wp );
