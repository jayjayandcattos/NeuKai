function showTab(tabName) {
  document.querySelectorAll(".tab-content").forEach(function (tab) {
    tab.classList.remove("active");
    const existingImg = tab.querySelector(".tab-image");
    if (existingImg) existingImg.remove();
  });

  document.querySelectorAll(".tab-button").forEach(function (button) {
    button.classList.remove("active");
  });

  const selectedTab = document.getElementById(tabName);
  selectedTab.classList.add("active");

  if (event?.currentTarget) {
    event.currentTarget.classList.add("active");
  }

  const pTag = selectedTab.querySelector("p");
  if (pTag) {
    const msg = pTag.textContent.trim().toLowerCase();

    const emptyMessages = [
      "no cancelled donations found.",
      "no pending donations found.",
      "no completed donations found.",
    ];

    if (emptyMessages.includes(msg)) {
      const img = document.createElement("img");
      img.src = "../images/NEUKAIOUTLINE.svg";
      img.alt = "NEUKAI Logo";
      img.className = "tab-image";
      img.style.display = "block";
      img.style.margin = "100px auto 20px";
      img.style.width = "100%";
      img.style.maxWidth = "600px";
      img.style.height = "auto";

      selectedTab.insertBefore(img, pTag);
    }
  }
}
