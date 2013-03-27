(function (scope) {

    var CanvasFilter = {};
    CanvasFilter.filterImage = function (source, f) {
        var helper       = document.createElement('canvas'),
            imageData = source.getContext('2d').getImageData(0, 0, source.width, source.height),
            pixels    = imageData.data,
            r, g, b, i, tmp;

        helper.width  = source.width;
        helper.height = source.height;

        imageData.data = f(pixels);
        helper.getContext('2d').putImageData(imageData, 0, 0);

        return helper;
    };

    CanvasFilter.blackAndWhite = function (pixels) {
        var numPixels = pixels.length,
            i, average;
        for (i = 0; i < numPixels; i += 4) {
            average = pixels[i + 0] * 0.2126 + pixels[i + 1] * 0.7152 + pixels[i + 2] * 0.0722;

            pixels[i + 0] = average;
            pixels[i + 1] = average;
            pixels[i + 2] = average;
        }
        return pixels;
    };

    CanvasFilter.sepia = function (pixels) {
        var numPixels = pixels.length,
            i;
        for (i = 0; i < numPixels; i += 4) {
            pixels[i + 0] = pixels[i + 0] * 0.393 + pixels[i + 1] * 0.769 + pixels[i + 2] * 0.189;
            pixels[i + 1] = pixels[i + 0] * 0.349 + pixels[i + 1] * 0.686 + pixels[i + 2] * 0.168;
            pixels[i + 2] = pixels[i + 0] * 0.272 + pixels[i + 1] * 0.534 + pixels[i + 2] * 0.131;
        }
        return pixels;
    };

    CanvasFilter.brighten = function (pixels) {
        var numPixels = pixels.length,
            i;
        for (i = 0; i < numPixels; i += 4) {
            pixels[i + 0] += 16;
            pixels[i + 1] += 16;
            pixels[i + 2] += 16;
        }
        return pixels;
    }

    CanvasFilter.darken = function (pixels) {
        var numPixels = pixels.length,
            i;
        for (i = 0; i < numPixels; i += 4) {
            pixels[i + 0] -= 16;
            pixels[i + 1] -= 16;
            pixels[i + 2] -= 16;
        }
        return pixels;
    }

    CanvasFilter.invert = function (pixels) {
        var numPixels = pixels.length,
            i;
        for (i = 0; i < numPixels; i += 4) {
            pixels[i + 0] = 255 - pixels[i + 0];
            pixels[i + 1] = 255 - pixels[i + 1];
            pixels[i + 2] = 255 - pixels[i + 2];
        }
        return pixels;
    }

    scope.CanvasFilter = CanvasFilter;

}(window));
