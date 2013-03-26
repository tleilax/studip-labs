(function () {
    var streaming = false,
        video     = document.getElementById('video'),
        canvas    = document.getElementById('canvas'),
        helper    = document.getElementById('helper'),
        trigger   = document.getElementById('take-picture'),
        result    = document.getElementById('result'),
        width     = 640,
        height    = 0;
    
    navigator.getMedia = (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia);
    if (typeof navigator.getMedia === 'undefined') {
        console.log(navigator.getMedia);
        return;
    }
    
    navigator.getMedia({
        video: true,
        audio: false
    }, function (stream) {
        var src;
        if (navigator.mozGetUserMedia) {
            video.mozSrcObject = stream;
        } else if (window.URL || window.webkitURL) {
            src = (window.URL || window.webkitURL).createObjectURL(stream);
            video.src = src;
        } else {
            video.src = stream;
        }
        video.play();
    }, function (error) {
        console.log('An error occured: ' + error);
    });
    
    video.addEventListener('canplay', function (event) {
        if (!streaming) {
            height = video.videoHeight / (video.videoWidth / width);
            video.width = width;
            video.height = height;
            streaming = true;
        }
    }, false);

    function copyVideo(video, canvas) {
        canvas.width  = video.width;
        canvas.height = video.height;
        canvas.getContext('2d').drawImage(video, 0, 0, video.width, video.height);
    }

    function copyCanvas(canvas, img) {
        img.src = canvas.toDataURL('image/png');
    }

    trigger.addEventListener('click', function (event) {
        result.parentNode.style.display = 'block';
        video.parentNode.style.display = 'none';

        copyVideo(video, canvas);
        copyCanvas(canvas, result);

        event.preventDefault();
    }, false);
    
    document.getElementById('redo').addEventListener('click', function (event) {
        result.parentNode.style.display = 'none';
        video.parentNode.style.display = 'block';

        event.preventDefault();
    });

    document.getElementById('normal').addEventListener('click', function (event) {
        copyCanvas(canvas, result);
        
        event.preventDefault();
    });

    function applyFunction (source, destination, f) {
        var imageData = source.getContext('2d').getImageData(0, 0, source.width, source.height),
            pixels    = imageData.data,
            numPixels = pixels.length,
            r, g, b, i, tmp;
        
        destination.width  = source.width;
        destination.height = source.height;
        destination.getContext('2d').clearRect(0, 0, destination.width, destination.height);
        
        for (i = 0; i < numPixels * 4; i+= 4) {
            r = pixels[i + 0];
            g = pixels[i + 1];
            b = pixels[i + 2];
            
            tmp = f(r, g, b);
            pixels[i + 0] = tmp.r;
            pixels[i + 1] = tmp.g;
            pixels[i + 2] = tmp.b;
        }
        destination.getContext('2d').putImageData(imageData, 0, 0);
    }


    document.getElementById('bw').addEventListener('click', function (event) {
        applyFunction(canvas, helper, function (r, g, b) {
            var average = ~~(r * 0.299 + g * 0.587 + b * 0.114);
            return {
                r: average,
                g: average,
                b: average
            };
        });
        copyCanvas(helper, result);

        event.preventDefault();
    });

    document.getElementById('sepia').addEventListener('click', function (event) {
        applyFunction(canvas, helper, function (r, g, b) {
            return {
                r: ~~(r * 0.393 + g * 0.769 + b * 0.189),
                g: ~~(r * 0.349 + g * 0.686 + b * 0.168),
                b: ~~(r * 0.272 + g * 0.534 + b * 0.131)
            };
        });
        copyCanvas(helper, result);

        event.preventDefault();
    });
}());
