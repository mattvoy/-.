document.addEventListener("DOMContentLoaded", () => {
  const deleteButtons = document.querySelectorAll(".delete-ad-button");

  deleteButtons.forEach((button) => {
    button.addEventListener("click", async (event) => {
      const adId = button.dataset.adId;

      if (confirm("Вы уверены, что хотите удалить это объявление?")) {
        try {
          const response = await fetch("php/delete_ad.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `ad_id=${adId}`,
          });

          if (!response.ok) {
            const errorData = await response.json();
            alert(
              errorData.message || `HTTP error! Status: ${response.status}`
            );
            return;
          }

          const data = await response.json();
        } catch (error) {
          console.error("Error:", error);
          alert("Произошла ошибка при удалении объявления.");
        }
      }
    });
  });
});
