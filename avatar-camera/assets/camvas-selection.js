(function (scope, $) {
    
    // Camvas addon: selection
    // Uses http://odyniec.net/projects/imgareaselect/

    if (!scope.Camvas || !$) {
        return false;
    }

    scope.Camvas.definition.prototype.enableSelection = function (options) {
        options = options || {};
        if (typeof options.handles === 'undefined') {
            options.handles = true;
        }
        options.instance = true;

        if (options.persistent) {
            options.x1   = options.x1 || 0;
            options.y1   = options.y1 || 0;
            options.x2   = options.x2 || this.canvas.width;
            options.y2   = options.y2 || this.canvas.height;
            options.show = true
        }

        this.selection = $(this.canvas).imgAreaSelect(options);
    };

    scope.Camvas.definition.prototype.toDataURL = function (type, quality) {
        var selected = this.selection.getSelection(),
            temp;
        if (!selected.width || !selected.height) {
            return this.canvas.toDataURL(type, quality);
        }

        temp = document.createElement('canvas');
        temp.width  = selected.width;
        temp.height = selected.height;
        temp.getContext('2d').drawImage(this.canvas,
                                        selected.x1, selected.y1, selected.width, selected.height,
                                        0, 0, temp.width, temp.height);
        return temp.toDataURL(type, quality);
    };

}(window, jQuery));