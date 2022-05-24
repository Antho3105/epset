tinymce.init({
    selector: '.tinymce',
    plugins: 'codesample wordcount',
    toolbar: 'codesample fontfamily fontsize forecolor backcolor',
    language: 'fr_FR',
    setup: function (editor) {
        let max = 20;
        editor.on('submit', function (event) {
            let numChars = tinymce.activeEditor.plugins.wordcount.body.getCharacterCount();
            if (numChars > max) {
                alert("Maximum " + max + " characters allowed.");
                event.preventDefault();
                return false;
            }
        });
    }
});