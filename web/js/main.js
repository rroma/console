CM = Array();
openedHist = Array();
vResizeDrag = false;
hResizeDrag = false;
pX = 0;
pY = 0;


$(function(){
    $('.v-resize').mousedown(function(e){
        vResizeDrag = true;
        pY = e.pageY;
    });
    $('.hr-resize').mousedown(function(e){
        hResizeDrag = true;
        pX = e.pageX;
    });
    $(document).mousemove(function(e){
        if(hResizeDrag){
            var dX = pX - e.pageX;
            var width = parseInt($('.saved').css('width'));
            width += dX;
            $('.saved').css('width', width + 'px');
            pX = e.pageX;
        }
        if(vResizeDrag){
            var dY = e.pageY - pY;
            var height = parseInt($('.CodeMirror').css('height'));
            height += dY;
            $('.CodeMirror').css('height', height + 'px');
            $('.saved ul').css('height', height + 'px');
            pY = e.pageY;
            for(i in CM)
                CM[i].refresh();
        }
    });
    $(document).mouseup(function(){
        vResizeDrag = false;
        hResizeDrag = false;
    });
    $.fn.serializeObject = function()
    {
       var o = {};
       var a = this.serializeArray();
       $.each(a, function() {
           if (o[this.name]) {
               if (!o[this.name].push) {
                   o[this.name] = [o[this.name]];
               }
               o[this.name].push(this.value || '');
           } else {
               o[this.name] = this.value || '';
           }
       });
       return o;
    };

    $('#tab-h-hold .wrap').dblclick(function(e){
        var data = { 
            code: '<?php\n\n\n?>',
            name: getNewName(),
            key: null
        };
        addTab(data);
        selectTab(scriptIdx);
        scriptIdx++;
    });

    $('.wrap').on('dblclick', '.tab-head', function(e){
        e.stopPropagation(); 
    });

    $('.wrap').on('click', '.close-tab-btn', function(e){
        if($('.tab-head').length < 2)
            return;

        if($(this).parent().hasClass('selected'))
            selectTab(openedHist.pop());
        closeTab($(this).parent().attr('tabnum'));
        e.stopPropogation();
        
    });
    
    $('.wrap').on('click', '.tab-head', function(e){
        selectTab($(this).attr('tabnum'));
    });
    
    $('.tab-text').dblclick(function(){
        makeEditable(this);
    });
    
    $('div.saved').on('click', 'span', function(){
        key = $(this).data('key');
        keyInp = $('.db-key[value=' + key + ']');
        if(keyInp.length > 0){
            tabnum = keyInp.parent().attr('tabnum');
            selectTab(tabnum);
        } else {
            $.ajax({
                url: '/script/' + key + '/json',
                method: 'get',
                success: function(data){ 
                    addTab(data);
                    selectTab(scriptIdx);
                    scriptIdx++;
                }
            });
        }
    });

    $('.tab-text').blur(function(){
        makeNonEditable(this);
        
        var key = $('.tab-body.selected .code-edit');
        if(key){
            var selectedTab = $('.tab-head.selected');
            var name = selectedTab.find('.tab-text').text();
            selectedTab.find('input[type=hidden]').val(name);
            subformIdx = selectedTab.attr('tabnum');

            var formData = $('form').serializeObject();
            var filtered = {};
            var regEx = new RegExp('^\\w+\\[\\w+\\]\\['+ subformIdx +'\\]');

            for (var i in formData) {
                if(i.match(regEx)) {
                    filtered[i] = formData[i];
                }
            }

            $.ajax({
                url: '/script/rename',
                dataType: 'json',
                method: 'post',
                data: filtered,
                success: function(data){ 
                    console.log(data);

                }
            });
        }
    });

    $('.code-editor').each(function(){
        var editor = createCM(this);     
        CM.push(editor);
    });

    $('#exec-btn').click(function(){
        var btn = $(this);
        var action = btn.parents('form').attr('action');

        btn.attr('disabled', 'disabled');

        for(var i in CM){
            CM[i].save();
        }
        var code = $('.tab-body.selected .code-editor');
        var name = $('.tab-head.selected .tab-text');
        $.ajax({
            url: action,
            dataType: 'json',
            method: 'post',
            data: { 
                code: code.val(),
                name: name.text()
            },
            success: function(data){ 
                btn.removeAttr('disabled');
                $('#output').empty().append(data.output);
                $('#time').text(data.execParams.time);
                $('#mem').text(data.execParams.mem);
                $('#result').removeClass('hidden');
            }
        });
    });

    $('#save-btn').click(function(){
        var btn = $(this);
        var form = btn.parents('form');
        
        var selectedTab = $('.tab-head.selected');
        var name = selectedTab.find('.tab-text').text();
        selectedTab.find('input[type=hidden]').val(name);
        subformIdx = $('.tab-head.selected').attr('tabnum');
        for (i in CM) {
            CM[i].save();
        }

        var formData = {};
        formData = form.serializeObject();
        var filtered = {};
        var regEx = new RegExp('^\\w+\\[\\w+\\]\\['+ subformIdx +'\\]');
        
        for (var i in formData) {
            if(i.match(regEx)) {
                filtered[i] = formData[i];
            }
        }
        
        btn.attr('disabled', 'disabled');
        $.ajax({
            url: '/script/save',
            dataType: 'json',
            method: 'post',
            data: filtered,
            success: function(data){ 
                for (i in data) {
                    setKey(i, data[i].dbkey);
                }
                btn.removeAttr('disabled');
                $.ajax({
                    url: '/script/saved-list',
                    method: 'get',
                    success: function(data){ 
                        var names = $(data).find('li');
                        if(names.length > 0){
                            $('.saved ul').empty().append(names);
                        }
                    }
                });
            }
        });
    });
});

function setKey(idx, key){
    id = keyIdProto.replace('__idx__', idx);
    $('#' + id).val(key);
}

function setCode(idx, code){
    id = codeIdProto.replace('__idx__', idx);
    $('#' + id).val(code);
}

function setName(idx, name){
    id = nameIdProto.replace('__idx__', idx);
    $('#' + id).val(name);
}

function isScriptOpened(key){
    var result = false;
    $('.db-key').each(function(){
        if(key == $(this).val()){
            result = true;
        }
    });

    return result;
}

function getOwnerFormId(elementName) {
    var reg = new RegExp('');
    return elementName.match(/^\w+\[\w+\]\[(\d+)\]\[\w+\]/)[1];
}

function createCM(elem){
    return CodeMirror.fromTextArea(elem, {
        lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        indentUnit: 4,
        indentWithTabs: true,
        enterMode: "keep",
        tabMode: "shift",
        theme: "ambiance"
    });
}
    
function addTab(data){
    var newHead = tabHeadProto.replace(/__idx__/g, scriptIdx);
    newHead = newHead.replace(/__text__/g, data.name);
    newHead = $(newHead);
    $('#tab-h-hold .wrap').append(newHead);
    setName(scriptIdx, data.name);
    newHead.attr('tabnum', scriptIdx);
    newHead.find('.tab-text').dblclick(function(){
            makeEditable(this);
    });
    newHead.find('.tab-text').blur(function(){
        makeNonEditable(this);
    });

    var newBody = tabBodyProto.replace(/__idx__/g, scriptIdx);
    newBody = $(newBody);
    $('.cm-hold').append(newBody);
    newBody.attr('tabnum', scriptIdx);


    newBody.find('.code-editor').val(data.code);
    setKey(scriptIdx, data.key);
    var editor = createCM(newBody.find('.code-editor').addClass('selected')[0]);
    CM.push(editor);
}

function selectTab(tabnum){
    var prev = parseInt($('.tab-head.selected').attr('tabnum'));
    i = openedHist.indexOf(prev);
    if(i >= 0)
        openedHist.splice(i, 1);
    openedHist.push(prev);

    $('.tab-head').removeClass('selected');
    $('.tab-body').removeClass('selected');
    $('.tab-head[tabnum='+tabnum+']').addClass('selected');
    $('.tab-body[tabnum='+tabnum+']').addClass('selected');
    for(i in CM){
        CM[i].refresh();
    }
}

function closeTab(tabnum){
    $('.tab-head[tabnum='+tabnum+']').remove();
    $('.tab-body[tabnum='+tabnum+']').remove();
    var prev = parseInt($('.tab-head.selected').attr('tabnum'));
    i = openedHist.indexOf(parseInt(tabnum));
    if(i >= 0)
        openedHist.splice(i, 1);
}

function makeEditable(el){
    $(el).attr('contenteditable', 'true')
    .addClass('edit')
    .focus();
}

function makeNonEditable(el){
    $(el)
    .attr('contenteditable', 'false')
    .removeClass('edit');
}

function getNewName(){
    var base = 'New script';
    var name = base;
    var max = 0;
    var names = [];
    $('.saved span').each(function(){
        names.push($(this).text());
    });
    $('.tab-text').each(function(){
        names.push($(this).text());
    });
    var i = 1;
    while(names.indexOf(name) != -1){
        name = base + ' ' + i;
        i++;
    }

    return name;
}

/*function changeHeight(value)
{
    for(i = 0; i < CM.length; i++)
    {
        CM[i].setSize(null, value);
    }
}*/

