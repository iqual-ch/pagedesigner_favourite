// after init
(function ($, Drupal) {
  Drupal.behaviors.pagedesigner_favourite_after_init = {
    attach: function (context, settings) {
      // Add favourite button to blocks
      $(document).on('pagedesigner-render-block-element', function (e, block, element) {
        $blockElement = $(element);
        var blockIsFavourite = (block.get('additional').favourite);
        if ($blockElement.find('.gjs-am-favourite').length == 0) {
          if (blockIsFavourite) {
            var $btn = $('<a class="gjs-am-favourite" title="' + Drupal.t('Remove from favourites') + '"><i class="fas fa-star"></i></a>');
          } else {
            var $btn = $('<a class="gjs-am-favourite" title="' + Drupal.t('Add to favourites') + '"><i class="far fa-star"></i></a>');
          }
          $btn.click(function () {
            if (blockIsFavourite) {
              Drupal.restconsumer.delete('/pagedesigner/favourite/' + block.get('id'))
              block.set('category', Drupal.t(block.get('additional').category));
            } else {
              Drupal.restconsumer.post('/pagedesigner/favourite/', { id: block.get('id') })
              block.set('category', 'favourites');
            }
            var additionalBlockData = block.get('additional');
            additionalBlockData.favourite = !additionalBlockData.favourite;
            block.set('additional', additionalBlockData);
            var favouriteCategory = editor.BlockManager.getCategories().filter({ 'id': 'favourites' })[0];
            if (favouriteCategory) {
              favouriteCategory.set('order', 0);
            }
            editor.BlockManager.render();
          });

          $blockElement.append($btn);
        }
      });

      // Move favourites to category
      $(document).on('pagedesigner-init-events', function (e, editor, options) {
        editor.on('run:open-blocks', () => {
          editor.BlockManager.getCategories().each(ctg => ctg.set('open', true).set('order', 1));
          var favouriteBlocks = editor.BlockManager.getAll().filter(block => (block.get('additional').favourite));
          if (favouriteBlocks.length) {
            editor.BlockManager.getAll().filter(block => (block.get('additional').favourite)).forEach(function (block) {
              block.set('category', {
                'id': 'favourites',
                'label': Drupal.t('Favourites'),
                'attributes': {
                  'open': true,
                  'order': 0
                }
              });
            });
            editor.BlockManager.render();
          }
        });
      });
    }
  };

})(jQuery, Drupal);
