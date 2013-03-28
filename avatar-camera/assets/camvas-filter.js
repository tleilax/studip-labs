(function (scope) {
    
    // Camvas addon: filters

    if (!scope.Camvas) {
        return false;
    }

    function isEmpty(obj) {
        for (var prop in obj) {
            if (obj.hasOwnProperty(prop)) {
                return false;
            }
        }
        return true;
    }

    var Filters = {},
        flip    = scope.Camvas.definition.prototype.flip;
    scope.Camvas.definition.prototype.flip = function () {

        var src     = this.video,
            filters = this.getFilters(),
            temp, ctx,
            pixels, filter, args, imageData;

        // Apply filters to video
        if (!isEmpty(filters)) {
            temp = document.createElement('canvas'),
            temp.width  = this.canvas.width;
            temp.height = this.canvas.height;

            ctx = temp.getContext('2d');
            ctx.drawImage(src,
                          0, 0, src.width, src.height,
                          0, 0, this.canvas.width, this.canvas.height);
            imageData = ctx.getImageData(0, 0, temp.width, temp.height);
            pixels    = imageData.data;

            for (filter in filters) {
                args = filters[filter];
                pixels = Filters[filter].func(pixels, temp.width, temp.height);
            }

            imageData.data = pixels;
            ctx.putImageData(imageData, 0, 0);

            src = temp;
        }

        // Copy (filtered) video to canvas through stored original function
        flip.apply(this, [src]);
    };

    scope.Camvas.definition.prototype.getFilters = function () {
        this.filters = this.filters || {};
        return this.filters;
    }
    scope.Camvas.definition.prototype.addFilter = function (filter) {
        if (typeof Filters[filter] === 'undefined') {
            return false;
        }
        filters = this.getFilters();
        filters[filter] = Array.prototype.slice.call(arguments, 1);;
        return Filters[filter];
    };
    scope.Camvas.definition.prototype.removeFilter = function (filter) {
        filters = this.getFilters();
        delete filters[filter];
    };
    scope.Camvas.definition.prototype.resetFilters = function () {
        this.filters = {};
    };

    // Extend external Fassade
    scope.Camvas.filters = Filters;
    scope.Camvas.registerFilter = function (name, filterFunc, filterArgs) {
        Filters[name] = filterArgs || {};
        Filters[name].func = filterFunc;
    };
    scope.Camvas.registerFilterAddMatrix = function (name, matrix) {
        Filters[name] = {matrix: matrix};
        Filters[name].func = function (pixels) {
            var numPixels = pixels.length,
                matrix    = Filters[name].matrix,
                i;
            for (i = 0; i < numPixels; i += 4) {
                pixels[i + 0] += matrix[0];
                pixels[i + 1] += matrix[1];
                pixels[i + 2] += matrix[2];
            }
            return pixels;
        };
    };
    scope.Camvas.registerFilterMulMatrix = function (name, matrix) {
        Filters[name] = {matrix: matrix};
        Filters[name].func = function (pixels) {
            var numPixels = pixels.length,
                matrix    = Filters[name].matrix,
                i;
            for (i = 0; i < numPixels; i += 4) {
                pixels[i + 0] = pixels[i + 0] * matrix[0] + pixels[i + 1] * matrix[1] + pixels[i + 2] * matrix[2];
                pixels[i + 1] = pixels[i + 0] * matrix[3] + pixels[i + 1] * matrix[4] + pixels[i + 2] * matrix[5];
                pixels[i + 2] = pixels[i + 0] * matrix[6] + pixels[i + 1] * matrix[7] + pixels[i + 2] * matrix[8];
            }
            return pixels;
        };
    };

}(window));
