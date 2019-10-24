function getUserImage(username, background = [255, 255, 255, 0], colored) {
    const hash = sha1(username);
    let col = [0, 0, 0, 255];

    if (colored) {
        for(let i = 0; i<3; i++) {
            let hashBuffer = hash.slice(i*12, (i*12)+12);
            let stringCode = "";
            for(let j = 0; j < hashBuffer.length; j++) {
                stringCode += hashBuffer.charCodeAt(j);
            }
            col[i] = Number(stringCode) % 255;
        }
    }

    const img = new Identicon( hash, {
        foreground: col,
        background: background,
        size: 420,
        format: 'png'
    });
    return `data:image/png;base64,${img}`;
}

$(document).ready(function() {
    // fix menu when passed
    $('.masthead').visibility({
        once: false,
        onBottomPassed: function() {
            $('.fixed.menu').transition('fade in');
        },
        onBottomPassedReverse: function() {
            $('.fixed.menu').transition('fade out');
        }
    });
    // create sidebar and attach to menu open
    $('.ui.sidebar').sidebar('attach events', '.toc.item');

    $('.ui.dropdown').dropdown({
        clearable: true
    });
    $('.ui.checkbox').checkbox();
    $('.date-time').calendar({ampm: false});
});

