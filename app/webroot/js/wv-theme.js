(function($){
  function enhanceTables(scopeSelector, wrapperClass){
    $(scopeSelector).find('table.table').each(function(){
      var $table = $(this);
      if($table.closest('.table-responsive,.wv-table-scroll,.wv-admin-table-scroll').length){
        return;
      }
      $table.wrap('<div class="'+wrapperClass+'"></div>');
    });
  }

  function hardenBlankLinks(){
    $('a[target="_blank"]').each(function(){
      var rel = $.trim($(this).attr('rel') || '');
      var parts = rel ? rel.split(/\s+/) : [];
      if($.inArray('noopener', parts) === -1){ parts.push('noopener'); }
      if($.inArray('noreferrer', parts) === -1){ parts.push('noreferrer'); }
      $(this).attr('rel', parts.join(' '));
    });
  }

  function optimizeImages(){
    $('img').each(function(){
      var $img = $(this);
      if(!$img.attr('loading')){ $img.attr('loading','lazy'); }
      if(!$img.attr('decoding')){ $img.attr('decoding','async'); }
    });
  }

  function enhanceAdmin(){
    if(!$('body').hasClass('wv-admin-theme')){ return; }
    enhanceTables('body.wv-admin-theme', 'wv-admin-table-scroll');
    $('form[method="get"]').each(function(){
      var $form = $(this);
      if($form.closest('.navbar,.dropdown-menu').length){ return; }
      if($form.find('.form-control,select,input[type="text"],input[type="date"]').length){
        $form.addClass('wv-admin-filter-form');
      }
    });
  }

  function enhancePortal(){
    if(!$('body').hasClass('wv-front-theme') && !$('body').hasClass('wv-client-theme')){ return; }
    enhanceTables('body.wv-front-theme,body.wv-client-theme', 'wv-table-scroll');
  }

  $(function(){
    hardenBlankLinks();
    optimizeImages();
    enhanceAdmin();
    enhancePortal();
  });
})(jQuery);
