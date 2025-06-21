document.addEventListener("DOMContentLoaded", function () {
  const sliderContainer = document.querySelector(".slide-container");
  const slides = document.querySelectorAll(".slide");
  const prevButton = document.querySelector(".prev-slide");
  const nextButton = document.querySelector(".next-slide");
  let currentIndex = 0;

  if (!sliderContainer || !slides.length || !prevButton || !nextButton) {
    return;
  }

  function updateSlider() {
    const slideWidth = slides[0].offsetWidth;
    sliderContainer.style.transform = `translateX(${
      -currentIndex * slideWidth
    }px)`;
  }

  prevButton.addEventListener("click", function () {
    currentIndex = Math.max(0, currentIndex - 1);
    updateSlider();
  });

  nextButton.addEventListener("click", function () {
    currentIndex = Math.min(slides.length - 1, currentIndex + 1);
    updateSlider();
  });

  updateSlider();
});
