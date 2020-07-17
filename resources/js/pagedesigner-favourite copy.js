// after init
(function ($, Drupal) {

  function addFavouriteButtons() {

    var blocks = editor.BlockManager.getAll();
    blocks.forEach(function (block) {

      var $blockElement = $('.gjs-block-category').find('.gjs-blocks-c > .gjs-block');
      var blockIsFavourite = (block.get('additional').favourite);
      if ($blockElement.find('.gjs-am-favourite').length == 0) {
        if (blockIsFavourite) {
          var $btn = $('<a class="gjs-am-favourite" title="' + Drupal.t('Remove from favourites') + '"><i class="fas fa-star"></i></a>');
          block.set('category', Drupal.t('Favourites'));
        } else {
          var $btn = $('<a class="gjs-am-favourite" title="' + Drupal.t('Add to favourites') + '"><i class="far fa-star"></i></a>');
        }
        $btn.click(function () {

          if (blockIsFavourite) {
            Drupal.restconsumer.delete('/pagedesigner/favourite/' + block.get('id'))
            block.set('category', Drupal.t(block.get('additional').category));
          } else {
            Drupal.restconsumer.post('/pagedesigner/favourite/', { id: block.get('id') })
            block.set('category', Drupal.t('Favourites'));
          }
          var favouriteCategory = editor.BlockManager.getCategories().filter({ 'id': Drupal.t('Favourites') })[0];
          if (favouriteCategory) {
            favouriteCategory.set('open', true);
            favouriteCategory.set('order', 0);
          }
          editor.BlockManager.render();
          addFavouriteButtons();
        });

        $blockElement.append($btn);
      }
    });

    editor.BlockManager.render();
  }

  Drupal.behaviors.pagedesigner_favourite_after_init = {
    attach: function (context, settings) {
      // Add favourite button to blocks
      $(document).on('pagedesigner-init-events', function (e, editor, options) {
        editor.on('run:open-blocks', () => {
          editor.BlockManager.getCategories().each(ctg => ctg.set('open', false).set('order', 1));

          var favouriteCategory = editor.BlockManager.getCategories().filter({ 'id': Drupal.t('Favourites') })[0];
          if (favouriteCategory) {
            favouriteCategory.set('open', true);
            favouriteCategory.set('order', 0);
          }
          addFavouriteButtons();
        });
      });
    }
  };

})(jQuery, Drupal);
