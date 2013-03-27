(function ($) {
    $(document).ready(function () {
        var camvas = new Camvas('#video');
    });
    var MIN_WIDTH  = 250,
        MIN_HEIGHT = 250,
        video      = $('#video')[0],
        result     = $('#result')[0],
        selection  = $('#result').imgAreaSelect({
                         aspectRatio: '1:1',
                         handles: 'corners',
                         instance: true,
                         minHeight: MIN_WIDTH,
                         minWidth: MIN_HEIGHT,
                         persistent: true
                     }),
        snapshot   = document.createElement('canvas'),
        snap_sfx   = document.createElement('audio'),
        width      = 640,
        height     = 480;
    snap_sfx.src     = 'assets/snap.wav';

    navigator.getMedia = (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia);
    if (typeof navigator.getMedia === 'undefined') {
        console.log('foo');
        return;
    }

    navigator.getMedia({
        video: true,
        audio: false
    }, function (stream) {
        if (navigator.mozGetUserMedia) {
            video.mozSrcObject = stream;
        } else if (window.URL || window.webkitURL) {
            video.src = (window.URL || window.webkitURL).createObjectURL(stream);
        } else {
            video.src = stream;
        }
        video.play();
    }, function (error) {
        console.log('An error occured: ' + error);
    });

    $(video).bind('canplay', function () {
        height = video.videoHeight / (video.videoWidth / width);

        video.width   = snapshot.width  = result.width  = width;
        video.height  = snapshot.height = result.height = height;

        $('.video').show();

        $(this).unbind('canplay');
    });

    function copyCanvas(source, destination) {
        destination.getContext('2d').drawImage(source, 0, 0, source.width, source.height);
    }

    $('#take-picture').click(function () {
        snap_sfx.play();

        $('.result, .video').toggle();

        copyCanvas(video, snapshot);
        copyCanvas(snapshot, result);

        var w = Math.min(snapshot.width, snapshot.height),
            x0 = (snapshot.width - w) / 2,
            y0 = (snapshot.height - w) / 2;
        selection.setSelection(x0, y0, x0 + w, y0 + w);
        selection.setOptions({show: true});
    });

    $('#redo').click(function () {
        selection.cancelSelection();
        $('.video, .result').toggle();
    });

    $('#normal').click(function () {
        copyCanvas(snapshot, result);
    });

    $('[data-filter]').click(function () {
        var filter = $(this).data('filter'),
            filtered = CanvasFilter.filterImage(snapshot, CanvasFilter[filter]);
        copyCanvas(filtered, result);
    });

    $('#upload').click(function () {
        var selected = selection.getSelection(),
            cropped  = document.createElement('canvas'),
            data     = {};
        cropped.width  = selected.width;
        cropped.height = selected.height;
        cropped.getContext('2d').drawImage(result,
                                           selected.x1, selected.y1, selected.width, selected.height,
                                           0, 0, selected.width, selected.height);
        data.contents = cropped.toDataURL('image/jpeg', 0.95).replace(/^data:image\/jpeg;base64,/, '');

        $.post('upload.php', data, function () {
            alert('success');
        });
    });
}(jQuery));
