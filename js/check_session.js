const loginLink = document.getElementById("login-link");
const profileLink = document.getElementById("profile-link");

async function checkSession() {
  try {
    const response = await fetch("php/check_session.php");
    const data = await response.json();

    if (data.loggedIn) {
      loginLink.style.display = "none";
      profileLink.style.display = "flex";
    } else {
      loginLink.style.display = "flex";
      profileLink.style.display = "none";
    }
  } catch (error) {
    console.error("Error checking session:", error);
  }
}

document.addEventListener("DOMContentLoaded", checkSession);
