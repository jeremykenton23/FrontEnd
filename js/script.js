document.addEventListener("DOMContentLoaded", function() {
    const sections = document.querySelectorAll(".content-section");

    function checkVisibility() {
        const viewportHeight = window.innerHeight;

        sections.forEach(section => {
            const rect = section.getBoundingClientRect();
            if (rect.top <= viewportHeight && rect.bottom >= 0) {
                section.querySelectorAll(".hidden").forEach(el => {
                    el.classList.add("visible");
                    el.classList.remove("hidden");
                });
            }
        });
    }

    // Initial check
    checkVisibility();

    // Check visibility on scroll
    window.addEventListener("scroll", checkVisibility);
});
