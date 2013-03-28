(function (scope) {

    if (typeof scope.Camvas === 'undefined') {
        return;
    }
    var Camvas = scope.Camvas;

    // R/G/B filters, reduces input to the according channel
    Camvas.registerFilterMulMatrix('red',   [1, 0, 0,
                                             0, 0, 0,
                                             0, 0, 0]);
    Camvas.registerFilterMulMatrix('green', [0, 0, 0,
                                             0, 1, 0,
                                             0, 0, 0]);
    Camvas.registerFilterMulMatrix('blue',  [0, 0, 0,
                                             0, 0, 0,
                                             0, 0, 1]);

    // Luma filter, see http://en.wikipedia.org/wiki/Luminance_%28relative%29
    Camvas.registerFilterMulMatrix('luma', [0.2126, 0.7152, 0.0722,
                                            0.2126, 0.7152, 0.0722,
                                            0.2126, 0.7152, 0.0722]);

    // Sepia filter, matrix picked from different google result
    Camvas.registerFilterMulMatrix('sepia', [0.393, 0.769, 0.189,
                                             0.349, 0.686, 0.168,
                                             0.272, 0.534, 0.131]);

    // Brighten (& darken) filter, adds value to each channel
    Camvas.registerFilterAddMatrix('brighten', [16, 16, 16]);

    // Invert filter
    Camvas.registerFilter('invert', function (pixels) {
        var numPixels = pixels.length,
            i;
        for (i = 0; i < numPixels; i += 4) {
            pixels[i + 0] = ~pixels[i + 0] & 0xff;
            pixels[i + 1] = ~pixels[i + 1] & 0xff;
            pixels[i + 2] = ~pixels[i + 2] & 0xff;
        }
        return pixels;
    });

    // Reduce filter, reduces colors in input
    (function () {
        var threshold = 0x1f; // ~= 32
        Camvas.registerFilter('reduce', function (pixels) {
            var numPixels = pixels.length,
                i;
            for (i = 0; i < numPixels; i += 4) {
                pixels[i + 0] = pixels[i + 0] & ~threshold;
                pixels[i + 1] = pixels[i + 1] & ~threshold;
                pixels[i + 2] = pixels[i + 2] & ~threshold;
            }
            return pixels;
        }, {threshold: threshold});
    }());

    // Threshold filter
    (function () {
        var threshold = 63;
        Camvas.registerFilter('threshold', function (pixels) {
            var numPixels = pixels.length,
                i, w;
            for (i = 0; i < numPixels; i += 4) {
                w = (pixels[i + 0] + pixels[i + 1] + pixels[i + 2] > threshold * 3)
                  ? 255 : 0;
                pixels[i + 0] = w;
                pixels[i + 1] = w;
                pixels[i + 2] = w;
            }
            return pixels;
        }, {threshold: threshold});
    }());

    // Flip horizontal filter, flips input horizontally
    Camvas.registerFilter('flipHorizontal', function (pixels, width, height) {
        var temp    = document.createElement('canvas').getContext('2d').createImageData(width, height).data,
            offset0 = 0,
            x, y, offset1, i;

        for (i = 0; i < pixels.length; i += 1) {
            temp[i] = pixels[i];
        }

        for (y = 0; y < height; y += 1) {
            for (x = 0; x < width; x += 1) {
                offset1 = (y * width + width - x - 1) << 2;

                pixels[offset0 + 0] = temp[offset1 + 0];
                pixels[offset0 + 1] = temp[offset1 + 1];
                pixels[offset0 + 2] = temp[offset1 + 2];
                pixels[offset0 + 3] = temp[offset1 + 3];

                offset0 += 4;
            }
        }
        return pixels;
    });

    // Flip vertical filter, flips input vertically
    Camvas.registerFilter('flipVertical', function (pixels, width, height) {
        var canvas  = document.createElement('canvas'),
            offset0 = 0, 
            temp, x, y, offset1, i;
        canvas.width  = width;
        canvas.height = height;
        temp = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height).data;

        for (i = 0; i < pixels.length; i += 1) {
            temp[i] = pixels[i];
        }

        for (y = 0; y < height; y += 1) {
            for (x = 0; x < width; x += 1) {
                offset1 = ((height - y - 1) * width + x) * 4;

                pixels[offset0 + 0] = temp[offset1 + 0];
                pixels[offset0 + 1] = temp[offset1 + 1];
                pixels[offset0 + 2] = temp[offset1 + 2];
                pixels[offset0 + 3] = temp[offset1 + 3];

                offset0 += 4;
            }
        }
        return pixels;
    });

    // Mirror split filter, splits input image horizontally and mirrors the right side
    Camvas.registerFilter('mirrorsplit', function (pixels, width, height) {
        var temp   = document.createElement('canvas').getContext('2d').createImageData(width, height).data,
            x, y, offset0, offset1, i;

        for (i = 0; i < pixels.length; i += 1) {
            temp[i] = pixels[i];
        }

        for (y = 0; y < height; y += 1) {
            for (x = 0; x < width / 2; x += 1) {
                offset0 = (y * width + x) << 2;
                offset1 = (y * width + width - x - 1) << 2;

                pixels[offset0 + 0] = temp[offset1 + 0];
                pixels[offset0 + 1] = temp[offset1 + 1];
                pixels[offset0 + 2] = temp[offset1 + 2];
                pixels[offset0 + 3] = temp[offset1 + 3];
            }
        }
        return pixels;
    });

    // Pixelate filter, resizes pixels
    (function () {
        var pixelSize = 8;
        Camvas.registerFilter('pixelate', function (pixels, width, height) {
            var x, y, v, w, r, g, b, offset;

            for (y = 0; y < height; y += pixelSize) {
                for (x = 0; x < width; x += pixelSize) {
                    offset = (y * width + x) << 2;
                    r = g = b = 0;
                    for (w = 0; w < pixelSize; w += 1) {
                        for (v = 0; v < pixelSize; v += 1) {
                            r += pixels[offset + (w * width + v) * 4 + 0];
                            g += pixels[offset + (w * width + v) * 4 + 1];
                            b += pixels[offset + (w * width + v) * 4 + 2];
                        }
                    }
                    r /= pixelSize * pixelSize;
                    g /= pixelSize * pixelSize;
                    b /= pixelSize * pixelSize;

                    for (w = 0; w < pixelSize; w += 1) {
                        for (v = 0; v < pixelSize; v += 1) {
                            pixels[offset + (w * width + v) * 4 + 0] = r;
                            pixels[offset + (w * width + v) * 4 + 1] = g;
                            pixels[offset + (w * width + v) * 4 + 2] = b;
                        }
                    }
                }
            }
            return pixels;
        }, {size: pixelSize});
    }());

}(window));
