/**
* Theme: Hyper - Responsive Bootstrap 5 Admin Dashboard
* Author: Coderthemes
* Module/App: Hightlight the syntax
*/


if (window['ClipboardJS']) {
    var clipboard = new ClipboardJS('.btn-copy-clipboard', {
        target: function (trigger) {
            var highlight = trigger.closest('.tab-pane.active');

            el = highlight.querySelector('pre.language-markup');

            return el;
        }
    });

    clipboard.on('success', function (e) {
        var originalLabel = e.trigger.innerHTML;
        e.trigger.innerHTML = "Copied";
        setTimeout(function () {
            e.trigger.innerHTML = originalLabel;
        }, 3000);
        e.clearSelection();
    });
}