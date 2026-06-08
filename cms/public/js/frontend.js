document.addEventListener("DOMContentLoaded", () => {
    const backToTop = document.querySelector("[data-back-to-top]");

    if (!backToTop) {
        return;
    }

    const toggleBackToTop = () => {
        if (window.scrollY > 420) {
            backToTop.classList.add("show");
        } else {
            backToTop.classList.remove("show");
        }
    };

    backToTop.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    });

    window.addEventListener("scroll", toggleBackToTop, { passive: true });

    toggleBackToTop();
});
