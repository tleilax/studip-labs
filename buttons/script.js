(function ($) {
    var currentClassName = 'button-blue',
        buttons = $('div:last button');
    $('select').change(function () {
        var color     = $(this).val(),
            className = 'button-' + color;
        buttons.removeClass(currentClassName).addClass(className);
        currentClassName = className;
    }).change();

    $('.toggle').click(function () {
        buttons.closest('div').toggleClass('button-group');
    });
    $('.show-class').click(function () {
        buttons.find('span').toggle();
    });
    $('.show-css').change(function () {
        $('pre.less').hide();
        $('.show-less').attr('checked', false);
        $('pre.css').toggle(this.checked);
    }).filter(':checked').change();
    $('.show-less').change(function () {
        $('pre.css').hide();
        $('.show-css').attr('checked', false);
        $('pre.less').toggle(this.checked);
    }).filter(':checked').change();
}(jQuery));
