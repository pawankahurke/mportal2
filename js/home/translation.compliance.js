var translation = i18nextify.init({
    backend: {
        "loadPath": '../locale/Compliance/{{ns}}.{{lng}}.json'
    },
    // defaults that are set
    autorun: false,
    ignoreClasses: ['ignore'],
    ignoreTags: ['SCRIPT', 'STYLE', 'LINK'],
    debug: false
});
translation.start();