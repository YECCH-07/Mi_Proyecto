
document.addEventListener('DOMContentLoaded', () => {
    const slider = document.querySelector('.bg-slider');
    const images = [
        '../images/foto principal 2.jpg',
        '../images/foto principal 3.jpg',
        '../images/foto principal 4.jpg',
        '../images/foto principal.jpg',
        '../images/Imagen de  portada 2.jpg',
        '../images/Imagen de  portada.jpg',
        '../images/seguridad ciudadana.jpg'
    ];

    let currentImageIndex = 0;

    function changeBackgroundImage() {
        currentImageIndex = (currentImageIndex + 1) % images.length;
        slider.style.backgroundImage = `url('${images[currentImageIndex]}')`;
    }

    slider.style.backgroundImage = `url('${images[currentImageIndex]}')`;
    setInterval(changeBackgroundImage, 5000); // Change image every 5 seconds
});
