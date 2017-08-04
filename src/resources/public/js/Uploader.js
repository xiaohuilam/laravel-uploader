Utils = {};
Utils.printf = function() {
    var num = arguments.length;
    var oStr = arguments[0];
    for (var i = 1; i < num; i++) {
        var pattern = "\\{" + (i-1) + "\\}";
        var re = new RegExp(pattern, "g");
        oStr = oStr.replace(re, arguments[i]);
    }
    return oStr;
};
Utils.randomStr = function (length) {
    var str = '';
    while (str.length < length){
        str += Math.random().toString(36).substr(2)
    }
    return str.substr(0, length);
};
Utils.startsWith = function (needle, str) {
    return str.indexOf(needle) === 0;
};

function Uploader(selector) {
    this.selector = selector;

    this.uploader = null;

    this.init = function () {
        this.create();
        this.bind();
    };

    this.create = function () {
        var _this = this;

        this.uploadUrl = this.getAttr('data-url');
        this.token = this.getAttr('data-token');
        this.name = this.getAttr('data-name');
        this.max = this.getAttr('data-max');
        this.extensions = this.getAttr('data-extensions');

        this.uploader = WebUploader.create({
            server: _this.uploadUrl,
            auto : true,
            pick: {
                id : this.selector.find('.picker'),
                label : ' ',
                multiple : true
            },
            accept : {
                extensions: this.extensions
            },
            resize: false
        });
    };

    this.bind = function () {
        var _this = this;
        this.uploader.on( 'uploadBeforeSend', function( block, data ) {
            var file = block.file;

            if (_this.token !== undefined){
                data.token = _this.token;
                data.key = Utils.randomStr(32) + '.' + file.ext;
            }
        });
        this.uploader.on('beforeFileQueued', function () {
            if (_this.uploader.getFiles().length === parseInt(_this.max)){
                return false;
            }
        });
        this.uploader.on('fileQueued', function(file) {
            console.log(file);
            if (Utils.startsWith('image', file.type)){
                var _li = '<div id="{0}" class="img-item"><img class="img" src="{1}"><div class="wrapper">0%</div></div>';
                _this.uploader.makeThumb(file, function(error, src) {
                    if (error) {
                        return;
                    }
                    _this.selector.find('.picker').before(Utils.printf(_li, file.id, src));
                }, 75, 75);
            }else{
                var _li = '<div id="{0}" class="img-item"><p>{1}</p><div class="wrapper">0%</div></div>';
                _this.selector.find('.picker').before(Utils.printf(_li, file.id, file.ext.toUpperCase()));
            }

            if (_this.uploader.getFiles().length === parseInt(_this.max)){
                _this.selector.find('.picker').hide();
            }
        });

        this.uploader.on('uploadProgress', function(file, percentage) {
            var _percent = $('#'+file.id).find('.wrapper');
            _percent.text( parseInt(percentage * 100) + '%' );
        });

        this.uploader.on('uploadSuccess', function(file, response) {
            var _input = '<input type="hidden" name="{0}[]" value="{1}" />';
            if (parseInt(_this.max) === 1){
                _input = _input.replace('[]', '');
            }
            _this.selector.append(Utils.printf(_input, name, response.key));
            _this.selector.find('#'+file.id).find('.wrapper').hide();
        });

        this.uploader.on('uploadError', function(file) {
            _this.selector.find('#'+file.id).find('.wrapper').addClass('error').text('error');
        });
    };

    this.getAttr = function (name) {
        return this.selector.attr(name);
    }
}

var ups = $("div[id^='uploader_']");
for (var i = 0; i < ups.length; i++){
    var uploader = new Uploader($('#'+ups[i].id));
    uploader.init();
}

