tinymce.init({
    selector: '.tinymceXL',
    menubar: false,
    height: 350,
    plugins: 'wordcount',
    toolbar: 'fontfamily fontsize bold italic forecolor backcolor outdent indent',
    language: 'fr_FR',
});
tinymce.init({
    selector: '.tinymceL',
    menubar: false,
    width: 550,
    height: 300,
    plugins: 'codesample wordcount',
    toolbar: 'codesample fontfamily fontsize bold italic forecolor backcolor outdent indent',
    language: 'fr_FR',
});
tinymce.init({
    selector: '.tinymceXS',
    menubar: false,
    height: 150,
    plugins: 'codesample wordcount',
    toolbar: 'codesample fontfamily fontsize bold italic forecolor backcolor outdent indent',
    language: 'fr_FR',
});
