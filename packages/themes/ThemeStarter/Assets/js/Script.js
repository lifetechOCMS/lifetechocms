const welcomeButton = document.getElementById('welcomeButton');
const welcomeMessage = document.getElementById('welcomeMessage');

if (welcomeButton && welcomeMessage) {
    welcomeButton.addEventListener('click', () => {
        welcomeMessage.textContent = 'Your LifeTechOCMS theme is working!';
    });
}
