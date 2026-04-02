(function (blocks, element) {
	var el = element.createElement;
	var registerBlockType = blocks.registerBlockType;

	registerBlockType('noty-broadcast-immo/card', {
		edit: function () {
			return el(
				'div',
				{ className: 'wp-block-noty-broadcast-immo-card', style: { padding: '20px', border: '1px dashed #ccc', textAlign: 'center' } },
				'Aperçu de l\'annonce (carte) - Visible uniquement en frontend'
			);
		},
		save: function () {
			return null;
		}
	});
})(window.wp.blocks, window.wp.element);
