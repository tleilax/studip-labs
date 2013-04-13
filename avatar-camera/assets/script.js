(function ($) {
    var sfx = {
        snap: document.createElement('audio')
    };
    sfx.snap.src = 'assets/snap.wav';

    var camvas = Camvas.create('#avatar', 640, 480, function () {
        $('.hidden').removeClass('hidden');

        camvas.debug('#debug');
        camvas.enableSelection({
            aspectRatio: '1:1',
            handles: 'corners',
            minHeight: 250,
            minWidth: 250,
            persistent: true,
            x1: 80, // = (640 - 480) / 2
            y1: 0,
            x2: 560, // = 640 - x1
            y2: 480
        });
    });

    $('#pause').click(function () { camvas.pause(); });
    $('#play').click(function () { camvas.play(); });

    $('[data-togglefilter]').change(function () {
        var filters = $(this).data('togglefilter').split(','),
            value   = $(this).val(),
            active  = $(this).val(),
            i;
        for (i = 0; i < filters.length; i++) {
            camvas.removeFilter(filters[i]);
        }
        if ($(this).is(':checkbox')) {
            active = this.checked;
        }

        if (active) {
            camvas.addFilter(value);
        }
    });
    
    $('[data-togglebrightness]').change(function () {
        var value = parseInt($(this).val(), 10),
            matrix;
        if (value) {
            filter = camvas.addFilter('brighten');
            filter.matrix = [value, value, value];
        } else {
            camvas.removeFilter('brighten');
        }
    });

    $('#save').click(function () {
        sfx.snap.play();

        var data = {
            contents: camvas.toDataURL('image/jpeg', 0.95).replace(/^data:image\/jpeg;base64,/, '')
        };
        $.post('upload.php', data, function () {
            alert('success');
        });
    });
}(jQuery));
