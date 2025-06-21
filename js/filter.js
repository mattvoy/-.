function initializeFilters() {
  const priceFromInput = document.getElementById("price-from");
  const priceToInput = document.getElementById("price-to");
  const cityInput = document.getElementById("city-input");
  const filterCheckboxes = document.querySelectorAll(".filter-checkbox");
  const applyFiltersLink = document.getElementById("apply-filters");
  const resetFiltersLink = document.getElementById("reset-filters");

  function filterAds() {
    const adItems = Array.from(document.querySelectorAll(".dec-item"));
    const priceFrom = priceFromInput.value
      ? parseFloat(priceFromInput.value)
      : null;
    const priceTo = priceToInput.value ? parseFloat(priceToInput.value) : null;
    const cityFilter = cityInput.value.toLowerCase();
    const selectedConditions = Array.from(
      document.querySelectorAll(
        '.filter-checkbox[data-filter="condition"]:checked'
      )
    ).map((checkbox) => checkbox.value);

    adItems.forEach((ad) => {
      const adPrice = parseFloat(ad.dataset.price);
      let adCity = "";
      if (ad.querySelector(".location")) {
        adCity = ad.querySelector(".location").textContent.toLowerCase();
      }
      const adCondition = ad.dataset.condition;

      let showAd = true;

      if (priceFrom !== null && adPrice < priceFrom) {
        showAd = false;
      }
      if (priceTo !== null && adPrice > priceTo) {
        showAd = false;
      }

      if (cityFilter && !adCity.includes(cityFilter)) {
        showAd = false;
      }

      if (
        selectedConditions.length > 0 &&
        !selectedConditions.includes(adCondition)
      ) {
        showAd = false;
      }

      if (showAd) {
        ad.style.display = "block";
      } else {
        ad.style.display = "none";
      }
    });
    updatePaginationVisibility();
  }

  function resetFilters() {
    priceFromInput.value = "";
    priceToInput.value = "";
    cityInput.value = "";
    filterCheckboxes.forEach((checkbox) => (checkbox.checked = false));
    const adItems = Array.from(document.querySelectorAll(".dec-item"));
    adItems.forEach((ad) => (ad.style.display = "block"));
    updatePaginationVisibility();
  }

  applyFiltersLink.addEventListener("click", function (event) {
    event.preventDefault();
    filterAds();
  });

  resetFiltersLink.addEventListener("click", function (event) {
    event.preventDefault();
    resetFilters();
  });
  updatePaginationVisibility();
}

document.addEventListener("DOMContentLoaded", initializeFilters);

document.addEventListener("DOMContentLoaded", function () {
  const filtersToggle = document.querySelector(".filters-toggle");
  const filtersContainer = document.querySelector(".filters-container");

  filtersToggle.addEventListener("click", function () {
    filtersContainer.classList.toggle("active");
  });
});
