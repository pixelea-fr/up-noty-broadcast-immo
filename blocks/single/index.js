(function (blocks, element) {
	var el = element.createElement;
	var registerBlockType = blocks.registerBlockType;

	registerBlockType('noty-broadcast-immo/single', {
		edit: function () {
			return el(
				'div',
				{ className: 'wp-block-noty-broadcast-immo-single', style: { padding: '20px', border: '1px dashed #ccc', textAlign: 'center' } },
				'Aperçu de l\'annonce (détaillé) - Visible uniquement en frontend'
			);
		},
		save: function () {
			return null;
		}
	});
})(window.wp.blocks, window.wp.element);
