window.PulseMediaPicker = (() => {
    const modal = document.getElementById("pulseMediaPicker");
    const grid = document.getElementById("pulseMediaPickerGrid");

    let currentCallback = null;

    async function loadMedia() {
        try {
            const response = await fetch(
                window.PULSE_MEDIA_LIBRARY_URL
            );

            const data = await response.json();

            renderMedia(
                data.media || []
            );

        } catch (error) {

            grid.innerHTML = `
                <div class="p-module-empty">
                    <span class="material-symbols-rounded">
                        error
                    </span>

                    <h3>
                        Failed to load media
                    </h3>
                </div>
            `;
        }
    }

    function renderMedia(items) {

        if (!items.length) {

            grid.innerHTML = `
                <div class="p-module-empty">
                    <span class="material-symbols-rounded">
                        perm_media
                    </span>

                    <h3>
                        No images found
                    </h3>
                </div>
            `;

            return;
        }

        grid.innerHTML = items.map(item => `
            <button
                type="button"
                class="p-module-media-picker-item"
                data-url="${item.url}"
            >
                <img
                    src="${item.url}"
                    alt="${item.name}"
                >

                <span>
                    ${item.name}
                </span>
            </button>
        `).join("");
    }

    function open(callback) {

        currentCallback = callback;

        modal.hidden = false;

        document.body.style.overflow = "hidden";

        loadMedia();
    }

    function close() {

        modal.hidden = true;

        document.body.style.overflow = "";

        currentCallback = null;
    }

    modal.addEventListener("click", (event) => {

        const closeTarget = event.target.closest(
            "[data-media-close]"
        );

        if (closeTarget) {
            close();
            return;
        }

        const mediaItem = event.target.closest(
            ".p-module-media-picker-item"
        );

        if (!mediaItem) {
            return;
        }

        const url = mediaItem.dataset.url;

        if (currentCallback) {
            currentCallback(url);
        }

        close();
    });

    return {
        open,
        close,
    };
})();