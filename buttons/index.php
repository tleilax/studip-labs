<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Stud.IP - DirectionButtons test</title>
    <link href="http://studip.tleilax.de/trunk/assets/stylesheets/style.css" rel="stylesheet" type="text/css">
    <link href="style.min.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div>
        <select>
            <option>black</option>
            <option selected>blue</option>
            <option>grey</option>
            <option>green</option>
            <option>red</option>
            <option>white</option>
            <option>yellow</option>
        </select>
        <label>
            <input type="checkbox" class="toggle">
            toggle button group
        </label>
        <label>
            <input type="checkbox" class="show-class">
            show classes
        </label>
        <label>
            <input type="checkbox" class="show-less">
            show less
        </label>
        <label>
            <input type="checkbox" class="show-css">
            show css
            (<?= number_format(filesize('buttons.css'), 0, ',', '.') ?> bytes
            / <?= number_format(filesize('buttons.min.css'), 0, ',', '.') ?> compressed)
        </label>
    </div>

    <div>
        <button class="button button-arrow-begin">
            <span>Anfang</span>
            <span>.button-arrow-begin</span>
        </button>
        <button class="button button-arrow-back">
            <span>Zurück</span>
            <span>.button-arrow-back</span>
        </button>
        <button class="button">FooBar</button>
        <button class="button button-arrow-forward">
            <span>Weiter</span>
            <span>.button-arrow-forward</span>
        </button>
        <button class="button button-arrow-end">
            <span>Ende</span>
            <span>.button-arrow-end</span>
        </button>
    </div>
    
    <pre class="css" style="display: none;"><code class="language-css"><?= file_get_contents('buttons.css') ?></code></pre>
    <pre class="less" style="display: none;"><code class="language-css"><?= file_get_contents('buttons.less') ?></code></pre>

    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script src="script.min.js"></script>
</body>
</html>

