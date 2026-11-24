(function(window, $){
    if(!$){ return; }

    function isSkippableForm($form){
        return $form.hasClass('wv-no-auto-layout') ||
            $form.closest([
                '.wv-config-page',
                '.wv-lead-edit-page',
                '.wv-contact-add-page',
                '.wv-contact-edit-page',
                '.wv-weblogo-page',
                '.wv-payment-add-page',
                '.wv-profile-page',
                '.wv-media-page',
                '.wv-help-page',
                '.wv-forgot-page'
            ].join(',')).length > 0;
    }

    function isRealFieldElement($el){
        if(!$el || !$el.length){ return false; }
        if($el.is('input[type="hidden"],script,style')){ return false; }
        if($el.is('input:not([type="button"]):not([type="submit"]):not([type="reset"]),select,textarea')){ return true; }
        return $el.find('input:not([type="hidden"]):not([type="button"]):not([type="submit"]):not([type="reset"]),select,textarea,.select2-container,.btn-group,.multiselect-native-select').length > 0;
    }

    function isActionGroup($group){
        var realFields = $group.find('input:not([type="hidden"]):not([type="button"]):not([type="submit"]):not([type="reset"]),select,textarea').length;
        var actionButtons = $group.find('button,.btn,input[type="submit"],input[type="button"],input[type="reset"]').length;
        return actionButtons > 0 && realFields === 0;
    }

    function nextDirectField($label, groupNode){
        var $next = $label.nextAll().filter(function(){
            var $candidate = $(this);
            return this.parentNode === groupNode &&
                !$candidate.is('label,input[type="hidden"],script,style') &&
                !$candidate.hasClass('wv-auto-field') &&
                isRealFieldElement($candidate);
        }).first();
        return $next;
    }

    function polishGroup(){
        var $group = $(this);
        if($group.data('wvFormPolished') || $group.hasClass('wv-no-auto-layout')){
            return;
        }
        if(isActionGroup($group)){
            $group.addClass('wv-form-actions');
            $group.data('wvFormPolished', true);
            return;
        }

        var groupNode = $group.get(0);
        var wrapped = 0;
        $group.children('label').each(function(){
            var $label = $(this);
            if($label.closest('.wv-auto-field').length || $label.parent().get(0) !== groupNode){
                return;
            }
            var $field = nextDirectField($label, groupNode);
            if(!$field.length){
                return;
            }
            var $wrap = $('<div class="wv-auto-field"></div>');
            $label.before($wrap);
            $wrap.append($label.detach());
            $wrap.append($field.detach());
            wrapped++;
        });

        if(wrapped > 0){
            $group.addClass('wv-auto-grid');
        }
        $group.data('wvFormPolished', true);
    }

    function polishForms(scope){
        var $scope = scope ? $(scope) : $(document);
        $scope.find('form.form-horizontal').each(function(){
            var $form = $(this);
            if(isSkippableForm($form)){
                $form.addClass('wv-no-auto-layout');
                return;
            }
            $form.addClass('wv-auto-form');
            $form.children('.form-group').each(polishGroup);
            $form.find('.panel-body > .form-group,.well > .form-group,.tab-pane > .form-group').each(polishGroup);
        });
    }

    $(function(){
        polishForms(document);
    });

    $(document).on('shown.bs.modal', function(e){
        polishForms(e.target);
    });
})(window, window.jQuery);
