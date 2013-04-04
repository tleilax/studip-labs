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
        flip    = scope.Camvas.definition.prototype.flip,
        temp    = null,
        ctx;
    scope.Camvas.definition.prototype.flip = function (input) {
        input = input || this.video;
        var filters = this.getFilters(),
            pixels, filter, args, imageData;
        

        // Apply filters to video
        if (!isEmpty(filters)) {
            if (temp === null) {
                temp = document.createElement('canvas'),
                temp.width  = this.canvas.width;
                temp.height = this.canvas.height;
                ctx = temp.getContext('2d');
            }

            ctx.drawImage(input,
                          0, 0, input.width, input.height,
                          0, 0, this.canvas.width, this.canvas.height);
            imageData = ctx.getImageData(0, 0, temp.width, temp.height);
            pixels    = imageData.data;

            for (filter in filters) {
                pixels = Filters[filter].func(pixels, temp.width, temp.height);
            }

            imageData.data = pixels;
            ctx.putImageData(imageData, 0, 0);

            input = temp;
        }

        // Copy (filtered) video to canvas through stored original function
        flip.apply(this, [input]);
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
    scope.Camvas.registerFilterMatrix = function (name, matrix, factor) {
        factor = factor || 1;

        var filter = {matrix: matrix},
            size   = Math.floor(Math.sqrt(matrix.length)),
            offset = Math.floor(size / 2),
            ccc    = 0;
        filter.func = function (pixels, width, height) {
            if (typeof filter.helper === 'undefined') {
                filter.helper = document.createElement('canvas').getContext('2d').createImageData(width, height).data;
            }

            var numPixels = pixels.length,
                i,
                x, y,
                sx, sy,
                r, g, b,
                weight,
                srcOffset, destOffset, matrixOffset;

            for (i = 0; i < numPixels; i += 1) {
                filter.helper[i] = pixels[i];
            }

            destOffset = 0;
            for (y = 0; y < height; y += 1) {
                for (x = 0; x < width; x += 1) {
                    r = g = b = matrixOffset = 0;
                    for (sy = y - offset; sy <= y + offset; sy += 1) {
                        srcOffset = (sy * width + x - offset) << 2;
                        for (sx = x - offset; sx <= x + offset; sx += 1) {
                            if (sx >= 0 && sx < width && sy >= 0 && sy < height) {
                                weight = filter.matrix[matrixOffset];
                                r += filter.helper[srcOffset + 0] * weight;
                                g += filter.helper[srcOffset + 1] * weight;
                                b += filter.helper[srcOffset + 2] * weight;
                            }
                            srcOffset    += 4;
                            matrixOffset += 1;
                        }
                    }

                    pixels[destOffset + 0] = r / factor;
                    pixels[destOffset + 1] = g / factor;
                    pixels[destOffset + 2] = b / factor;

                    destOffset += 4;
                }
            }
            return pixels;
        };
        Filters[name] = filter;
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
