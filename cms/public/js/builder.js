const builderData =
    document.getElementById(
        "pulseBuilderData"
    );

const builderCanvas =
    document.getElementById(
        "pulseBuilderCanvas"
    );

const addButtons =
    document.querySelectorAll(
        "[data-builder-add]"
    );

let blocks = [];

const templates = {

    hero: {
        type: "hero",
        eyebrow: "Pulse CMS",
        title: "Build Better Websites",
        description:
            "Create beautiful experiences with Pulse CMS.",
        button_label: "Get Started",
        button_url: "#"
    },

    text: {
        type: "text",
        content:
            "Your content goes here."
    },

    image: {
        type: "image",
        url: "",
        alt: ""
    },

    video: {
        type: "video",
        embed: ""
    },

    cta: {
        type: "cta",
        title: "Ready to start?",
        description:
            "Create your next project with Pulse.",
        button_label: "Contact Us",
        button_url: "#"
    },

    features: {
        type: "features",
        title: "Features",
        description: "",
        items: []
    },

    stats: {
        type: "stats",
        items: []
    },

    accordion: {
        type: "accordion",
        title: "FAQ",
        items: []
    },

    testimonial: {
        type: "testimonial",
        quote: "",
        name: "",
        role: ""
    },

    html: {
        type: "html",
        html: ""
    }
};

function safeParseJson(value) {

    try {

        const parsed =
            JSON.parse(value || "[]");

        return Array.isArray(parsed)
            ? parsed
            : [];

    } catch {

        return [];
    }
}

function syncTextarea() {

    builderData.value =
        JSON.stringify(
            blocks,
            null,
            2
        );
}

function renderBuilder() {

    if (!blocks.length) {

        builderCanvas.innerHTML = `
            <div class="pulse-builder-empty">

                <span class="material-symbols-rounded">
                    view_quilt
                </span>

                <h3>
                    No blocks yet
                </h3>

                <p>
                    Add a block to begin.
                </p>

            </div>
        `;

        syncTextarea();

        return;
    }

    builderCanvas.innerHTML = blocks
        .map((block, index) => {

            const imageControls =
                block.type === "image"
                    ? `
                        <div class="pulse-builder-inline-actions">

                            <button
                                type="button"
                                data-image-picker="${index}"
                            >
                                Select Image
                            </button>

                        </div>
                    `
                    : "";

            return `
                <article
                    class="pulse-builder-item"
                >

                    <div
                        class="pulse-builder-item-head"
                    >

                        <div>

                            <strong>
                                ${block.type}
                            </strong>

                            <span>
                                Block ${index + 1}
                            </span>

                        </div>

                        <div
                            class="pulse-builder-item-actions"
                        >

                            <button
                                type="button"
                                data-up="${index}"
                            >
                                ↑
                            </button>

                            <button
                                type="button"
                                data-down="${index}"
                            >
                                ↓
                            </button>

                            <button
                                type="button"
                                data-delete="${index}"
                            >
                                ×
                            </button>

                        </div>

                    </div>

                    ${imageControls}

                    <pre>
${escapeHtml(
    JSON.stringify(
        block,
        null,
        2
    )
)}
                    </pre>

                </article>
            `;
        })
        .join("");

    syncTextarea();
}

function escapeHtml(text) {

    return text
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;");
}

addButtons.forEach(button => {

    button.addEventListener(
        "click",
        () => {

            const type =
                button.dataset.builderAdd;

            blocks.push(
                JSON.parse(
                    JSON.stringify(
                        templates[type]
                    )
                )
            );

            renderBuilder();
        }
    );
});

builderCanvas.addEventListener(
    "click",
    (event) => {

        const remove =
            event.target.closest(
                "[data-delete]"
            );

        const up =
            event.target.closest(
                "[data-up]"
            );

        const down =
            event.target.closest(
                "[data-down]"
            );

        const picker =
            event.target.closest(
                "[data-image-picker]"
            );

        if (remove) {

            blocks.splice(
                remove.dataset.delete,
                1
            );

            renderBuilder();

            return;
        }

        if (up) {

            const index =
                Number(
                    up.dataset.up
                );

            if (index > 0) {

                [
                    blocks[index],
                    blocks[index - 1]
                ] = [
                    blocks[index - 1],
                    blocks[index]
                ];

                renderBuilder();
            }

            return;
        }

        if (down) {

            const index =
                Number(
                    down.dataset.down
                );

            if (
                index <
                blocks.length - 1
            ) {

                [
                    blocks[index],
                    blocks[index + 1]
                ] = [
                    blocks[index + 1],
                    blocks[index]
                ];

                renderBuilder();
            }

            return;
        }

        if (picker) {

            const index =
                Number(
                    picker.dataset.imagePicker
                );

            PulseMediaPicker.open(
                (url) => {

                    blocks[index].url =
                        url;

                    renderBuilder();
                }
            );
        }
    }
);

blocks =
    safeParseJson(
        builderData.value
    );

renderBuilder();