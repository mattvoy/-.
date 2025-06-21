const showPhoneButton = document.getElementById("show-phone-button");
const phoneModal = document.getElementById("phone-modal");
const closeModalButton = document.getElementById("close-modal-button");
const confirmShowPhoneButton = document.getElementById("confirm-show-phone");
const phoneNumberSpan = document.getElementById("phone-number");

const actualPhoneNumber = "987-654-3210";

function openModal() {
  phoneModal.style.display = "block";
}

function closeModal() {
  phoneModal.style.display = "none";
}

showPhoneButton.addEventListener("click", openModal);

closeModalButton.addEventListener("click", closeModal);

confirmShowPhoneButton.addEventListener("click", function () {
  phoneNumberSpan.textContent = actualPhoneNumber;
});

window.addEventListener("click", function (event) {
  if (event.target == phoneModal) {
    closeModal();
  }
});
