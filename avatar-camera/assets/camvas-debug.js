(function (scope) {

    if (!scope.Camvas) {
        return false;
    }


    scope.Camvas.definition.prototype.debug = function (selector) {
        var frames    = 0,
            start     = (new Date()).getTime(),
            last      = start,
            threshold = 5,
            element   = document.querySelector(selector),
            fps       = [];

        this.onloop = function onloop () {
            frames += 1;

            if ((frames % threshold) === 0) {
                var now      = (new Date()).getTime(),
                    duration = now - last,
                    curFps   = 0,
                    i;

                fps.push(threshold * 1000 / duration);
                for (i = 0; i < fps.length; i += 1) {
                    curFps += fps[i];
                }
                curFps /= fps.length;
                fps = fps.slice(-10);

                element.innerHTML = curFps.toFixed(2) + ' fps';

                last = now;
            }
        };
    };

}(window));