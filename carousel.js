const carousel = document.getElementById('imageCarousel');
const inner = carousel.querySelector('.carousel-inner');
const items = carousel.querySelectorAll('.carousel-item');
const totalItems = items.length;
let currentIndex = 0;

document.getElementById('nextBtn').addEventListener('click', () => {
    if (currentIndex < totalItems - 1) {
        currentIndex++;
    } else {
        currentIndex = 0;
    }
    updateCarousel();
});

document.getElementById('prevBtn').addEventListener('click', () => {
    if (currentIndex > 0) {
        currentIndex--;
    } else {
        currentIndex = totalItems - 1;
    }
    updateCarousel();
});

function updateCarousel() {
    const newTransformValue = -currentIndex * 100 + '%';
    inner.style.transform = 'translateX(' + newTransformValue + ')';
}
