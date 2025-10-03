(function() {
    var d = "text/javascript",
        e = "text/css",
        f = "stylesheet",
        g = "script",
        h = "link",
        k = "head",
        l = "complete",
        m = "UTF-8",
        n = ".";

    function p(b) {
        var a = document.getElementsByTagName(k)[0];
        a || (a = document.body.parentNode.appendChild(document.createElement(k)));
        a.appendChild(b);
    }

    function _loadJs(b) {
        var a = document.createElement(g);
        a.type = d;
        a.charset = m;
        a.src = b;
        p(a);
    }

    function _loadCss(b) {
        var a = document.createElement(h);
        a.type = e;
        a.rel = f;
        a.charset = m;
        a.href = b;
        p(a);
    }

    function _isNS(b) {
        b = b.split(n);
        for (var a = window, c = 0; c < b.length; ++c)
            if (!(a = a[b[c]]))
                return !1;
        return !0;
    }

    function _setupNS(b) {
        b = b.split(n);
        for (var a = window, c = 0; c < b.length; ++c)
            a.hasOwnProperty ? a.hasOwnProperty(b[c]) ? a = a[b[c]] : a = a[b[c]] = {} : a = a[b[c]] || (a[b[c]] = {});
        return a;
    }

    if (_isNS('google.translate.Element')) {
        return;
    }

    (function() {
        var c = _setupNS('google.translate._const');
        c._cl = 'ru';
        c._cuc = 'googleTranslateElementInit';
        c._cac = '';
        c._cam = '';
        c._ctkk = eval('((function(){var a=71640675;var b=-12312877;return 406476+\'.\'+(a+b)})())');
        var h = 'translate.googleapis.com';
        var s = (true ? 'https' : window.location.protocol == 'https:' ? 'https' : 'http') + '://';
        var b = s + h;
        c._pah = h;
        c._pas = s;
        c._pbi = b + '/translate_static/img/te_bk.gif';
        c._pci = b + '/translate_static/img/te_ctrl3.gif';
        c._pli = b + '/translate_static/img/loading.gif';
        c._plla = h + '/translate_a/l';
        c._pmi = b + '/translate_static/img/mini_google.png';
        c._ps = b + '/translate_static/css/translateelement.css';
        c._puh = 'translate.google.com';
        _loadCss(c._ps);
        _loadJs(b + '/translate_static/js/element/main_ru.js');
    })();
})();

function get_cookie(cookie_name) {
    var results = document.cookie.match('(^|;) ?' + cookie_name + '=([^;]*)(;|$)');
    if (results) return (unescape(results[2]));
    else return null;
}

function doGTranslate(lang_pair) {
    var lang = lang_pair.split('|')[1];
    var teCombo;
    var sel = document.getElementsByTagName('select');
    for (var i = 0; i < sel.length; i++)
        if (sel[i].className == 'goog-te-combo')
            teCombo = sel[i];

    if (document.getElementById('google_translate_element') == null || document.getElementById('google_translate_element').innerHTML.length == 0 || teCombo.length == 0 || teCombo.innerHTML.length == 0) {
        setTimeout(function() {
            doGTranslate(lang_pair);
        }, 500);
    } else {
        teCombo.value = lang;
        GTranslateFireEvent(teCombo, 'change');
    }
}

function GTranslateFireEvent(element, event) {
    try {
        if (document.createEventObject) {
            var evt = document.createEventObject();
            element.fireEvent('on' + event, evt);
        } else {
            var evt = document.createEvent('HTMLEvents');
            evt.initEvent(event, true, true);
            element.dispatchEvent(evt);
        }
    } catch (e) {}
}

function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'ru'
    }, 'google_translate_element');
}
