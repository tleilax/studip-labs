// window.requestAnimationFrame polyfill
// http://paulirish.com/2011/requestanimationframe-for-smart-animating/
window.requestAnimationFrame = (function () {
    return  window.requestAnimationFrame       ||
            window.webkitRequestAnimationFrame ||
            window.mozRequestAnimationFrame    ||
            function (callback) {
                window.setTimeout(callback, 1000 / 60);
            };
})();

// window.URL polyfill
window.URL = (function () {
    return  window.URL       ||
            window.webkitURL ||
            {
                createObjectURL: function (stream) {
                    return stream;
                }
            };
}());

// navigator.getMedia polyfill
navigator.getMedia = (function () {
    return  navigator.getUserMedia       ||
            navigator.webkitGetUserMedia ||
            navigator.mozGetUserMedia    ||
            navigator.msGetUserMedia;
}());

(function (scope) {

    function Camvas (canvas, width, height, callback) {
        if (typeof canvas === 'string') {
            canvas = document.querySelector(canvas);
        }

        if (arguments.length === 2 && typeof width === 'function') {
            callback = width;
            width = height = null;
        }
        if (arguments.length === 3 && typeof height === 'function') {
            callback = height;
            height = width;
        }

        if (typeof navigator.getMedia === 'undefined') {
            throw new 'Streaming not supported';
        }

        var video = document.createElement('video'),
            self  = this;
        if (typeof video.play === 'undefined') {
            throw 'Video not supported';
        }

        this.canvas  = canvas;
        this.context = canvas.getContext('2d');
        this.video   = video;
        this.paused  = true;
        this.running = false;

        canvas.width  = video.width  = width  || canvas.clientWidth  || canvas.width;
        canvas.height = video.height = height || canvas.clientHeight || canvas.height;

        navigator.getMedia({video: true}, function (stream) {
            if (navigator.mozGetUserMedia) {
                video.mozSrcObject = stream;
            } else {
                video.src = window.URL.createObjectURL(stream);
            }
            video.play();
        }, function (error) {
            throw 'Error during stream initialization: ' + error;
        });

        video.addEventListener('playing', function handler () {
            video.removeEventListener('playing', handler);

            // adjust canvas height to video resolution
            canvas.height = video.height * (video.width / canvas.width);

            (callback || function () {})();

            self.play();
        });
    };
    Camvas.prototype.play = function () {
        if (this.paused) {
            this.paused = false;

        }
        if (!this.running) {
            this.loop();
            this.running = true;
        }
    };
    Camvas.prototype.loop = function () {
        var self = this;
        (function loop () {
            if (!self.paused && self.video.videoWidth) {
                self.flip();
            }

            if (self.onloop) {
                self.onloop();
            }

            window.requestAnimationFrame(loop);
        }());
    };
    Camvas.prototype.flip = function (input, context) {
        input   = input   || this.video;
        context = context || this.context;
        context.drawImage(input, 0, 0);
//                          0, 0, input.width, input.height,
//                          0, 0, context.canvas.width, context.canvas.height);
    };
    Camvas.prototype.pause = function () {
        this.paused = true;
    };
    Camvas.prototype.toDataURL = function (type, quality) {
        return this.canvas.toDataURL(type || 'image/jpeg', quality || 1);
    };

    // External fassade
    scope.Camvas = {
        definition: Camvas,
        create: function (canvas, width, height, callback) {
            return new Camvas(canvas, width, height, callback);
        }
    };

}(window));
