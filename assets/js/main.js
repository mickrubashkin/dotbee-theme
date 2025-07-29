document.addEventListener("DOMContentLoaded", () => {
  console.log("test autodeploy from github");
  const burger = document.querySelector(".burger");
  const mobileMenu = document.querySelector(".header__mobile-menu");

  burger.addEventListener("click", () => {
    burger.classList.toggle("burger--active");
    mobileMenu.classList.toggle("mobile-menu--open");
  });

  // Close on link click
  mobileMenu.addEventListener("click", (e) => {
    if (e.target.tagName === "A") {
      burger.classList.remove("burger--active");
      mobileMenu.classList.remove("mobile-menu--open");
    }
  });

  // Close on outside click
  document.addEventListener("click", (e) => {
    if (
      mobileMenu.classList.contains("mobile-menu--open") &&
      !mobileMenu.contains(e.target) &&
      !burger.contains(e.target)
    ) {
      burger.classList.remove("burger--active");
      mobileMenu.classList.remove("mobile-menu--open");
    }
  });

  // Change background color based on active section
  if (
    document.querySelector("section.privacy-policy") ||
    document.querySelector("section.press-release") ||
    document.querySelector("section.terms")
  ) {
    document.body.style.backgroundColor = "#fff";
  }

  const sections = document.querySelectorAll("section");

  function getActiveSection() {
    let current = null;
    let smallestOffset = Infinity;

    sections.forEach((section) => {
      const rect = section.getBoundingClientRect();
      const offset = Math.abs(rect.top);

      if (offset < smallestOffset && rect.top < window.innerHeight) {
        current = section;
        smallestOffset = offset;
      }
    });

    return current;
  }

  window.addEventListener("scroll", () => {
    const active = getActiveSection();
    if (active) {
      const color = active.dataset.bg;
      document.body.style.backgroundColor = color;
    }
  });

  // Form submission handling
  const form = document.getElementById("waitlist-form");
  if (!form) return;

  // Set initial form start time
  if (form) {
    const startInput = form.querySelector('[name="form_start"]');
    if (startInput) startInput.value = Math.floor(Date.now() / 1000);
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch(dotbee_ajax.ajax_url, {
      method: "POST",
      credentials: "same-origin",
      body: new URLSearchParams({
        action: "waitlist_form",
        ...Object.fromEntries(formData.entries()),
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        const msg = document.getElementById("waitlist-form-message");
        if (data.success) {
          form.reset();
          msg.innerHTML = `<div class="form-success">${data.data}</div>`;
        } else {
          console.log(data);
          msg.innerHTML = `<div class="form-error">${
            data?.data || "Unknown error"
          }</div>`;
        }
      })
      .catch(() => {
        const msg = document.getElementById("waitlist-form-message");
        msg.innerHTML = `<div class="form-error">Something went wrong. Please try again.</div>`;
      });
  });

  // Story toggler
  const textWrapper = document.querySelector(".story__text-wrapper");
  const imageBlock = document.querySelector(".story__image");
  const toggleBtn = document.querySelector(".story__toggle");
  console.log("i am here");

  if (!textWrapper || !imageBlock || !toggleBtn) {
    return;
  }

  const imageHeight = imageBlock.offsetHeight;
  textWrapper.style.maxHeight = `${imageHeight - 20}px`;

  toggleBtn.addEventListener("click", function () {
    const isExpanded = textWrapper.classList.toggle("expanded");

    if (isExpanded) {
      toggleBtn.textContent = "Read Less <-";
      textWrapper.style.maxHeight = "10000px";
    } else {
      textWrapper.style.maxHeight = `${imageBlock.offsetHeight - 20}px`;

      toggleBtn.textContent = "Read More";
    }
  });
});
