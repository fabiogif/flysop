$(document).ready(function () {
    if (typeof $.fn.inputmask !== 'undefined') {
        $(".phone").inputmask({ mask: "(99) 99999-9999" });
    }
});
