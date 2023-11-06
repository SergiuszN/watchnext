$(document).ready(function () {
    let selects = document.getElementsByClassName('select-2');
    let selectLength = selects.length;
    for (let i = 0; i < selectLength; i++) {
        let item = selects.item(i);
        let options = {
            theme: 'bootstrap-5'
        };

        if (item.classList.contains('s2-tags')) {
            options.tags = true;
        }

        $(selects.item(i)).select2(options);
    }
});