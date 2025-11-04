document.addEventListener("DOMContentLoaded", function () {
    const institutionSelect = document.getElementById("institution");
    const signupMessage = document.getElementById("signupMessage");
    const signupBtn = document.getElementById("signupBtn");
  
    institutionSelect.addEventListener("change", function () {
      if (institutionSelect.value === "Other") {
        signupMessage.classList.remove("hidden");
      } else {
        signupMessage.classList.add("hidden");
      }
    });
  
    signupBtn.addEventListener("click", function () {
      alert("Redirecting to the sign-up page (not implemented).");
    });
  
    document.getElementById("institutionForm").addEventListener("submit", function (e) {
      e.preventDefault();
      if (institutionSelect.value && institutionSelect.value !== "Other") {
        alert("You selected: " + institutionSelect.value);
      } 
      else if (institutionSelect.value === "Other") {
        alert("Please sign up with your institution.");
      }
    });
  });