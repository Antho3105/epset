let input = document.querySelector('[name="candidateAnswer"]')
let answers = document.querySelectorAll('.answers')
answers.forEach(element => {
    element.addEventListener('click', () => {
            answers.forEach(element => {
                element.style.backgroundColor = "white"
                element.style.color = "black"
            });
            element.style.backgroundColor = "green";
            element.style.color = "white";
            input.value = element.id
        }
    )
});

// TODO réactiver le timer !
// document.addEventListener('DOMContentLoaded', function () {
//     let timerDiv = document.querySelector('#timer');
//     let counter = timerDiv.innerHTML;
//
//     let intervalId = setInterval(function timer() {
//         counter -= 0.1
//         if (counter > 0) {
//             timerDiv.innerHTML = counter.toFixed(1);
//         }
//         else {
//             clearInterval(intervalId)
//             document.querySelector('#timer').innerHTML = "Terminé";
//             document.forms["formSurvey"].submit();
//         }
//             }, 100);
// });
