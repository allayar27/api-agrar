$('#phone').formatter({
    'pattern': '+998 ({{99}}) {{999}} {{99}} {{99}}',
    'persistent': true
});
$('#passport').formatter({
    'pattern': '{{aa}} {{9999999}}',
    'persistent': true
});
$('#pinfl').formatter({
    'pattern': '{{99999999999999}}',
    'persistent': true
});
$('#quantity').formatter({
    'pattern': '{{99999999999999}}',
    'persistent': true
});
function customTime(input) {
    IMask(input, {
        overwrite: true,
        autofix: true,
        mask: 'HH:MM:SS',
        blocks: {
            HH: {
                mask: IMask.MaskedRange,
                placeholderChar: 'HH',
                from: 0,
                to: 23,
                maxLength: 2
            },
            MM: {
                mask: IMask.MaskedRange,
                placeholderChar: 'MM',
                from: 0,
                to: 59,
                maxLength: 2
            },
            SS: {
                mask: IMask.MaskedRange,
                placeholderChar: 'SS',
                from: 0,
                to: 59,
                maxLength: 2
            }
        }
    });
}

