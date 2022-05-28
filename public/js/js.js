function toggleImgInput() {
    let inputSelect = document.querySelector('.addImg')
    inputSelect.classList.toggle('show')
}

let input = document.querySelector('#question_answer_imgFileName')
if (input)
    input.addEventListener('change', previewImg)

function previewImg(event) {
    let file = event.path[0].files[0];
    let thumbnail = document.querySelector('.preview');
    if (file) {
        thumbnail.src = URL.createObjectURL(file);
    } else
        thumbnail.src = "{{ asset('./surveyQuestion_img/no_image.jpg') }}"
}
