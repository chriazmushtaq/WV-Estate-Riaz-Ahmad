/**
 * White Villas — Admin Media Picker & Universal Gallery Selector
 * Enhanced with Multi-Select, Instant Previews, and Real-Time Attachment
 */
(function(window, $){
    if(!$){ return; }
    var cfg = window.WV_ADMIN_MEDIA_PICKER || {};
    var pickerUrl = cfg.pickerUrl || (window.location.pathname.indexOf('/whitevillas') === 0 ? '/whitevillas/admin/MediaGalleries/index' : '/admin/MediaGalleries/index');
    var labels = cfg.labels || {};
    var activeInput = null;
    var activeType = '';
    var loading = false;
    var selectedMediaItems = []; // For multi-select mode

    window.wvOpenMediaPicker = function(targetInput, type){
        var $input = typeof targetInput === 'string' ? $('#' + targetInput) : $(targetInput);
        if(!$input.length){
            $input = $('<input type="hidden">');
        }
        openPicker($input);
    };

    function t(key, fallback){
        return labels[key] || fallback || key;
    }

    function cleanFileName(name){
        name = String(name || 'media-file').replace(/[\\\/:*?"<>|]+/g, '-');
        return $.trim(name) || 'media-file';
    }

    function fileExtension(name){
        var parts = String(name || '').split('.');
        return parts.length > 1 ? parts.pop().toLowerCase() : '';
    }

    function inputKey($input){
        return (($input.attr('id') || '') + ' ' + ($input.attr('name') || '') + ' ' + ($input.attr('accept') || '')).toLowerCase();
    }

    function isImportInput($input){
        var key = inputKey($input);
        if(key.indexOf('import') !== -1 || key.indexOf('csv_file') !== -1 || key.indexOf('ardbackup') !== -1 || key.indexOf('backup') !== -1){
            return true;
        }
        var accept = String($input.attr('accept') || '').toLowerCase();
        return accept !== '' && accept.indexOf('image') === -1 && accept.indexOf('pdf') === -1 && accept.indexOf('doc') === -1 && accept.indexOf('jpg') === -1 && accept.indexOf('jpeg') === -1 && accept.indexOf('png') === -1 && accept.indexOf('gif') === -1 && accept.indexOf('webp') === -1;
    }

    function shouldEnhance($input){
        if(!$input.length || $input.data('wvGalleryReady') || $input.data('wvNoGallery')){
            return false;
        }
        if(isImportInput($input)){
            return false;
        }
        var key = inputKey($input);
        return key.indexOf('image') !== -1 ||
            key.indexOf('photo') !== -1 ||
            key.indexOf('logo') !== -1 ||
            key.indexOf('document') !== -1 ||
            key.indexOf('floorplan') !== -1 ||
            key.indexOf('slip') !== -1 ||
            key.indexOf('cnic') !== -1 ||
            key.indexOf('id_front') !== -1 ||
            key.indexOf('id_back') !== -1 ||
            key.indexOf('profile') !== -1 ||
            key.indexOf('upload_file') !== -1 ||
            key.indexOf('file') !== -1 ||
            key.indexOf('video') !== -1 ||
            key.indexOf('mp4') !== -1 ||
            key.indexOf('media') !== -1 ||
            key.indexOf('pdf') !== -1;
    }

    function inferFileType($input){
        var key = inputKey($input);
        var accept = String($input.attr('accept') || '').toLowerCase();
        var hasImageAccept = accept.indexOf('image') !== -1 || accept.indexOf('jpg') !== -1 || accept.indexOf('jpeg') !== -1 || accept.indexOf('png') !== -1 || accept.indexOf('webp') !== -1 || accept.indexOf('gif') !== -1;
        var hasVideoAccept = accept.indexOf('video') !== -1 || accept.indexOf('mp4') !== -1 || accept.indexOf('webm') !== -1 || accept.indexOf('mov') !== -1;
        var hasDocumentAccept = accept.indexOf('pdf') !== -1 || accept.indexOf('doc') !== -1 || accept.indexOf('xls') !== -1 || accept.indexOf('txt') !== -1;
        if((hasVideoAccept && !hasImageAccept) || key.indexOf('video') !== -1 || key.indexOf('mp4') !== -1){
            return 'video';
        }
        if((hasDocumentAccept && !hasImageAccept) || key.indexOf('payment_plan') !== -1 || key.indexOf('brochure') !== -1 || key.indexOf('document') !== -1 || key.indexOf('pdf') !== -1){
            return 'document';
        }
        if((hasImageAccept && !hasDocumentAccept) || key.indexOf('image') !== -1 || key.indexOf('photo') !== -1 || key.indexOf('logo') !== -1 || key.indexOf('floorplan') !== -1 || key.indexOf('cnic') !== -1 || key.indexOf('id_front') !== -1 || key.indexOf('id_back') !== -1 || key.indexOf('profile') !== -1){
            return 'image';
        }
        return '';
    }

    function fileIcon(fileName, fileType){
        var ext = String(fileName || '').split('.').pop().toLowerCase();
        if(fileType === 'video' || /^(mp4|webm|mov|mkv|avi|wmv)$/.test(ext)){
            return 'fa fa-file-video-o';
        }
        if(fileType === 'image' || /^(jpg|jpeg|png|gif|webp)$/.test(ext)){
            return 'fa fa-file-image-o';
        }
        if(ext === 'pdf'){ return 'fa fa-file-pdf-o'; }
        if(/^(doc|docx)$/.test(ext)){ return 'fa fa-file-word-o'; }
        if(/^(xls|xlsx|csv)$/.test(ext)){ return 'fa fa-file-excel-o'; }
        return 'fa fa-file-o';
    }

    function ensureModal(){
        var $modal = $('#wvMediaPickerModal');
        if($modal.length){
            return $modal;
        }
        var html = ''+
            '<div class="modal fade wv-media-picker-modal" id="wvMediaPickerModal" tabindex="-1" role="dialog" aria-hidden="true">'+
                '<div class="modal-dialog modal-lg" role="document">'+
                    '<div class="modal-content">'+
                        '<div class="modal-header">'+
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                            '<h4 class="modal-title"><i class="fa fa-picture-o"></i> '+t('title','Select from Media Gallery')+'</h4>'+
                        '</div>'+
                        '<div class="wv-media-picker-filter">'+
                            '<div class="input-group" style="max-width:320px;display:inline-flex;">'+
                                '<input type="text" class="form-control" id="wvMediaPickerKeyword" placeholder="'+t('search','Search media...')+'">'+
                                '<span class="input-group-btn">'+
                                    '<button type="button" class="btn btn-primary" id="wvMediaPickerSearch"><i class="fa fa-search"></i></button>'+
                                '</span>'+
                            '</div>'+
                            '<div class="btn-group wv-picker-type-group" role="group">'+
                                '<button type="button" class="btn btn-default active" data-wv-picker-type=""><i class="fa fa-th-large"></i> '+t('all','All')+'</button>'+
                                '<button type="button" class="btn btn-default" data-wv-picker-type="image"><i class="fa fa-image"></i> '+t('images','Images')+'</button>'+
                                '<button type="button" class="btn btn-default" data-wv-picker-type="video"><i class="fa fa-video-camera"></i> '+t('videos','Videos')+'</button>'+
                                '<button type="button" class="btn btn-default" data-wv-picker-type="document"><i class="fa fa-file-pdf-o"></i> '+t('documents','Documents / PDF')+'</button>'+
                            '</div>'+
                            '<div class="wv-picker-multi-actions" id="wvPickerMultiActions" style="display:none;margin-left:auto;">'+
                                '<span class="wv-picker-selected-count text-muted" id="wvPickerSelectedCount" style="margin-right:10px;font-weight:700;">0 selected</span>'+
                                '<button type="button" class="btn btn-success" id="wvPickerAttachSelected"><i class="fa fa-check-circle"></i> Attach Selected</button>'+
                            '</div>'+
                        '</div>'+
                        '<div class="wv-media-picker-grid" id="wvMediaPickerGrid"></div>'+
                    '</div>'+
                '</div>'+
            '</div>';
        $('body').append(html);
        $modal = $('#wvMediaPickerModal');
        $modal.on('click', '[data-wv-picker-type]', function(){
            $modal.find('[data-wv-picker-type]').removeClass('active btn-primary').addClass('btn-default');
            $(this).addClass('active btn-primary').removeClass('btn-default');
            activeType = $(this).attr('data-wv-picker-type') || '';
            loadMedia();
        });
        $modal.on('click', '#wvMediaPickerSearch', function(){ loadMedia(); });
        $modal.on('keypress', '#wvMediaPickerKeyword', function(e){
            if(e.which === 13){
                e.preventDefault();
                loadMedia();
            }
        });
        $modal.on('click', '.js-wv-use-media', function(e){
            e.stopPropagation();
            var item = $(this).closest('.wv-media-picker-card').data('item');
            if(item){
                useMediaItem(item);
            }
        });
        $modal.on('click', '.wv-media-picker-card', function(){
            var item = $(this).data('item');
            if(!item) return;
            var isMulti = activeInput && activeInput.prop('multiple');
            if(isMulti){
                $(this).toggleClass('is-selected');
                var idx = -1;
                for(var i=0; i<selectedMediaItems.length; i++){
                    if(selectedMediaItems[i].id === item.id){
                        idx = i;
                        break;
                    }
                }
                if($(this).hasClass('is-selected')){
                    if(idx === -1) selectedMediaItems.push(item);
                } else {
                    if(idx !== -1) selectedMediaItems.splice(idx, 1);
                }
                updateMultiSelectionUI();
            } else {
                useMediaItem(item);
            }
        });
        $modal.on('click', '#wvPickerAttachSelected', function(){
            if(selectedMediaItems.length > 0){
                attachMultipleMediaItems(selectedMediaItems);
            }
        });
        return $modal;
    }

    function updateMultiSelectionUI(){
        var count = selectedMediaItems.length;
        var $actions = $('#wvPickerMultiActions');
        var $count = $('#wvPickerSelectedCount');
        if(count > 0){
            $actions.show();
            $count.text(count + ' selected');
        } else {
            $actions.hide();
        }
    }

    function openPicker($input){
        if(!pickerUrl){
            alert(t('notReady','Media Gallery picker is not ready.'));
            return;
        }
        activeInput = $input;
        activeType = inferFileType($input);
        selectedMediaItems = [];
        var $modal = ensureModal();
        $('#wvMediaPickerKeyword').val('');
        $('#wvPickerMultiActions').hide();
        $modal.find('[data-wv-picker-type]').removeClass('active btn-primary').addClass('btn-default');
        $modal.find('[data-wv-picker-type="' + (activeType || '') + '"]').addClass('active btn-primary').removeClass('btn-default');
        $modal.modal('show');
        loadMedia();
    }

    function loadMedia(){
        if(loading){ return; }
        loading = true;
        var $grid = $('#wvMediaPickerGrid');
        $grid.html('<div class="wv-media-picker-empty"><i class="fa fa-spinner fa-spin"></i> '+t('loading','Loading media...')+'</div>');
        $.ajax({
            url: pickerUrl,
            dataType: 'json',
            data: {
                file_type: activeType,
                keyword: $('#wvMediaPickerKeyword').val() || '',
                limit: 60
            }
        }).done(function(resp){
            renderMedia(resp && resp.items ? resp.items : []);
        }).fail(function(){
            $grid.html('<div class="wv-media-picker-empty">'+t('loadFailed','Media could not be loaded.')+'</div>');
        }).always(function(){
            loading = false;
        });
    }

    function renderMedia(items){
        var $grid = $('#wvMediaPickerGrid').empty();
        items = $.grep(items || [], function(item){
            return itemAllowedForInput(item, activeInput);
        });
        if(!items.length){
            $grid.html('<div class="wv-media-picker-empty">'+t('empty','No media found.')+'</div>');
            return;
        }
        var isMulti = activeInput && activeInput.prop('multiple');
        $.each(items, function(_, item){
            var isImage = item.file_type === 'image' && item.preview_url;
            var isSelected = false;
            for(var i=0; i<selectedMediaItems.length; i++){
                if(selectedMediaItems[i].id === item.id){
                    isSelected = true;
                    break;
                }
            }
            var $card = $('<article class="wv-media-picker-card ' + (isSelected ? 'is-selected' : '') + '"></article>').data('item', item);
            var thumb = '<div class="wv-media-picker-thumb">';
            if(isMulti){
                thumb += '<div class="wv-media-picker-check"><i class="fa fa-check"></i></div>';
            }
            thumb += '<span class="wv-media-picker-type">'+(item.file_type || 'file')+'</span>';
            if(isImage){
                thumb += '<img src="'+escapeAttr(item.preview_url)+'" alt="">';
            }else{
                thumb += '<i class="'+fileIcon(item.file_name, item.file_type)+'"></i>';
            }
            thumb += '</div>';
            $card.append(thumb);
            $card.append('<div class="wv-media-picker-info"><h4>'+escapeHtml(item.title || item.file_name)+'</h4><p>'+escapeHtml((item.extension || '').toUpperCase())+' &middot; '+escapeHtml(item.size_label || '')+'</p><button type="button" class="btn btn-primary btn-sm js-wv-use-media"><i class="fa fa-check"></i> '+ (isMulti ? 'Select' : t('use','Use File')) +'</button></div>');
            $grid.append($card);
        });
    }

    function itemAllowedForInput(item, $input){
        if(!$input || !$input.length){
            return true;
        }
        // Always display items matching the currently selected tab or search query
        return true;
    }

    function logoMaxHeight($input){
        if(!$input || !$input.length){
            return 0;
        }
        var value = parseInt($input.attr('data-wv-logo-max-height') || $input.data('wvLogoMaxHeight') || 0, 10);
        return value > 0 ? value : 0;
    }

    function resizeImageBlobForLogo(blob, item, maxHeight){
        return new Promise(function(resolve){
            var mime = item.mime_type || blob.type || '';
            var originalExt = fileExtension(item.file_name || item.title);
            if(item.file_type === 'image' && String(mime).indexOf('image/') !== 0){
                mime = originalExt === 'png' ? 'image/png' : 'image/jpeg';
            }
            if(!maxHeight || (String(mime).indexOf('image/') !== 0 && item.file_type !== 'image') || !window.URL){
                resolve({blob:blob, mime:mime});
                return;
            }
            var canvasCheck = document.createElement('canvas');
            if(!canvasCheck.toBlob){
                resolve({blob:blob, mime:mime});
                return;
            }
            var img = new Image();
            var objectUrl = URL.createObjectURL(blob);
            img.onload = function(){
                URL.revokeObjectURL(objectUrl);
                if(!img.height || img.height <= maxHeight){
                    resolve({blob:blob, mime:mime});
                    return;
                }
                var ratio = maxHeight / img.height;
                var canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(img.width * ratio));
                canvas.height = maxHeight;
                var ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                var outputMime = originalExt === 'png' ? 'image/png' : 'image/jpeg';
                canvas.toBlob(function(resizedBlob){
                    resolve({blob:resizedBlob || blob, mime:resizedBlob ? outputMime : mime});
                }, outputMime, 0.92);
            };
            img.onerror = function(){
                URL.revokeObjectURL(objectUrl);
                resolve({blob:blob, mime:mime});
            };
            img.src = objectUrl;
        });
    }

    function escapeHtml(value){
        return String(value || '').replace(/[&<>"']/g, function(ch){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch];
        });
    }

    function escapeAttr(value){
        return escapeHtml(value).replace(/`/g, '&#096;');
    }

    function useMediaItem(item){
        if(!activeInput || !activeInput.length){
            return;
        }
        var itemUrl = item.url || item.fetch_url || item.file_path || '';
        if(itemUrl && itemUrl.indexOf('/') !== 0 && itemUrl.indexOf('http') !== 0){
            itemUrl = '/whitevillas/' + itemUrl;
        }

        if(activeInput.attr('type') === 'hidden' || activeInput.attr('type') === 'text'){
            activeInput.val(itemUrl).trigger('change');
            $('#wvMediaPickerModal').modal('hide');
            return;
        }

        attachMultipleMediaItems([item]);
    }

    function attachMultipleMediaItems(items){
        if(!activeInput || !activeInput.length || !items.length){
            return;
        }
        if(!window.DataTransfer || !window.File || !window.fetch){
            alert(t('browserUnsupported','Your browser cannot attach gallery files automatically. Please download and upload manually.'));
            return;
        }

        var isMulti = activeInput.prop('multiple');
        var dt = new DataTransfer();
        if(isMulti && activeInput.get(0).files && activeInput.get(0).files.length){
            $.each(activeInput.get(0).files, function(_, existingFile){
                dt.items.add(existingFile);
            });
        }

        var fetchPromises = items.map(function(item){
            return fetch(item.fetch_url, {credentials:'same-origin'})
                .then(function(res){ return res.blob(); })
                .then(function(blob){ return resizeImageBlobForLogo(blob, item, logoMaxHeight(activeInput)); })
                .then(function(prepared){
                    var mime = prepared.mime || prepared.blob.type || item.mime_type || 'application/octet-stream';
                    return new File([prepared.blob], cleanFileName(item.file_name || item.title), {type:mime, lastModified:Date.now()});
                });
        });

        Promise.all(fetchPromises)
            .then(function(files){
                files.forEach(function(file){
                    dt.items.add(file);
                });
                activeInput.get(0).files = dt.files;
                if(activeInput.attr('id') === 'WeblogoPhoto' && items[0]){
                    $('#WeblogoGalleryMediaId').val(items[0].id || '');
                }
                activeInput.trigger('change');
                renderInputPreview(activeInput);
                $('#wvMediaPickerModal').modal('hide');
            })
            .catch(function(){
                alert(t('selectFailed','Selected media could not be attached to this upload field.'));
            });
    }

    function renderInputPreview($input){
        var input = $input.get(0);
        var $preview = $input.siblings('.wv-gallery-selected-preview');
        if(!$preview.length){
            $preview = $('<div class="wv-gallery-selected-preview"></div>').insertAfter($input);
        }
        $preview.empty().removeClass('has-files');
        if(!input.files || !input.files.length){
            return;
        }
        $preview.addClass('has-files');
        $.each(input.files, function(index, file){
            var $chip = $('<div class="wv-gallery-preview-chip"></div>').data('file-index', index);
            if(file.type && file.type.indexOf('image/') === 0){
                var img = document.createElement('img');
                img.alt = '';
                $chip.append(img);
                var reader = new FileReader();
                reader.onload = function(e){ img.src = e.target.result; };
                reader.readAsDataURL(file);
            }else{
                $chip.append('<i class="'+fileIcon(file.name, '')+'"></i>');
            }
            $chip.append('<span title="'+escapeAttr(file.name)+'">'+escapeHtml(file.name)+'</span>');
            var $remove = $('<button type="button" class="wv-chip-remove" title="Remove file">&times;</button>');
            $remove.on('click', function(e){
                e.stopPropagation();
                removeFileFromInput($input, index);
            });
            $chip.append($remove);
            $preview.append($chip);
        });
    }

    function removeFileFromInput($input, removeIdx){
        var input = $input.get(0);
        if(!input.files || !window.DataTransfer){
            $input.val('');
            renderInputPreview($input);
            $input.trigger('change');
            return;
        }
        var dt = new DataTransfer();
        for(var i=0; i<input.files.length; i++){
            if(i !== removeIdx){
                dt.items.add(input.files[i]);
            }
        }
        input.files = dt.files;
        renderInputPreview($input);
        $input.trigger('change');
    }

    function enhanceUploads(scope){
        var $scope = scope ? $(scope) : $(document);
        $scope.find('input[type="file"]').each(function(){
            var $input = $(this);
            if(!shouldEnhance($input)){
                return;
            }
            $input.data('wvGalleryReady', true);
            var $tools = $('<div class="wv-gallery-upload-tools"></div>');
            var $select = $('<button type="button" class="btn btn-info btn-xs wv-btn-open-gallery"><i class="fa fa-folder-open-o"></i> '+t('select','Choose from Media Gallery')+'</button>');
            var $clear = $('<button type="button" class="btn btn-default btn-xs"><i class="fa fa-close"></i> '+t('clear','Clear')+'</button>');
            $tools.append($select).append($clear);
            $tools.insertAfter($input);
            $('<div class="wv-gallery-selected-preview"></div>').insertAfter($tools);
            $select.on('click', function(){ openPicker($input); });
            $clear.on('click', function(){
                $input.val('');
                renderInputPreview($input);
                $input.trigger('change');
            });
            $input.on('change.wvGalleryPreview', function(){ renderInputPreview($input); });
        });
    }

    $(function(){
        enhanceUploads(document);
        var observer = new MutationObserver(function(mutations){
            $.each(mutations, function(_, mutation){
                $.each(mutation.addedNodes || [], function(_, node){
                    if(node && node.nodeType === 1){
                        enhanceUploads(node);
                    }
                });
            });
        });
        observer.observe(document.body, {childList:true, subtree:true});
        $(document).on('shown.bs.modal', function(e){ enhanceUploads(e.target); });
    });
})(window, window.jQuery);
