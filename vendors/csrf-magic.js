jQuery(document).ready(function () {
    forms = document.getElementsByTagName('form');
    for (var i = 0; i < forms.length; i++) {
        form = forms[i];
        if (form.method.toUpperCase() !== 'POST') continue;
        if (form.elements[csrfMagicName]) continue;
        var input = document.createElement('input');
        input.setAttribute('name', csrfMagicName);
        input.setAttribute('value', csrfMagicToken);
        input.setAttribute('type', 'hidden');
        form.appendChild(input);
    }
})