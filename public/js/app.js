// Cookie manager ------------------------------------------------------------------------------------------------------
(function () {
    window.cookieSet = function (name, value, exDays) {
        const d = new Date();
        d.setTime(d.getTime() + (exDays * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }

    window.cookieGet = function (cname) {
        let name = cname + "=";
        let ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }

        return "";
    }

    window.cookieCheck = function () {
        let user = cookieGet("username");
        if (user !== "") {
            alert("Welcome again " + user);
        } else {
            user = prompt("Please enter your name:", "");
            if (user !== "" && user != null) {
                cookieSet("username", user, 365);
            }
        }
    }
})();

// Query Selectors -----------------------------------------------------------------------------------------------------
(function () {
    window.QA = (selector) => document.querySelectorAll(selector);
})();

// select2 loader ------------------------------------------------------------------------------------------------------
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

// Change lang watcher -------------------------------------------------------------------------------------------------
(function () {
    QA('.language-switch-link').forEach((link) => {
        link.addEventListener('click', (e) => {
            let newLang = e.target.getAttribute('data-lang');
            cookieSet('lang', newLang, 365);
            window.location.reload();
        });
    });
})();