
let input = document.querySelector('[name="candidateAnswer"]')
let answers = document.querySelectorAll('.answers')
answers.forEach(element => {
    element.addEventListener('click', () => {
            answers.forEach(element => {
                element.style.backgroundColor = "white"
                element.style.color = "blue"
            });
            element.style.backgroundColor = "green";
            element.style.color = "black";
            input.value = element.id
        }
    )
});