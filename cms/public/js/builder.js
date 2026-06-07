const builderData = document.getElementById("pulseBuilderData");
const builderCanvas = document.getElementById("pulseBuilderCanvas");
const addButtons = document.querySelectorAll("[data-builder-add]");

let blocks = [];

const templates = {
    hero: {
        type: "hero",
        eyebrow: "Pulse CMS",
        title: "Build better pages with Pulse",
        description: "Create beautiful sections, layouts, and content blocks from the page builder.",
        button_label: "Get started",
        button_url: "/contact"
    },
    text: {
        type: "text",
        content: "Write your text content here. This block supports paragraphs and clean formatted copy."
    },
    image: {
        type: "image",
        url: "https://images.unsplash.com/photo-1497366754035-f200968a6e72",
        alt: "Workspace image"
    },
    video: {
        type: "video",
        embed: "<iframe src=\"https://www.youtube.com/embed/dQw4w9WgXcQ\" title=\"Video\" allowfullscreen></iframe>"
    },
    cta: {
        type: "cta",
        title: "Ready to build with Pulse?",
        description: "Launch flexible pages, themes, menus, and plugin-powered websites.",
        button_label: "Contact us",
        button_url: "/contact"
    },
    features: {
        type: "features",
        title: "Powerful CMS Features",
        description: "Everything you need to manage a modern website.",
        items: [
            {
                icon: "palette",
                title: "Themes",
                description: "Switch and customize bundled themes."
            },
            {
                icon: "extension",
                title: "Plugins",
                description: "Activate built-in modules and settings."
            },
            {
                icon: "article",
                title: "Pages",
                description: "Create SEO-ready pages."
            }
        ]
    },
    stats: {
        type: "stats",
        items: [
            {
                value: "5+",
                label: "Bundled themes"
            },
            {
                value: "20+",
                label: "Built-in plugins"
            },
            {
                value: "100%",
                label: "Blade powered"
            }
        ]
    },
    accordion: {
        type: "accordion",
        title: "Common Questions",
        items: [
            {
                question: "Can Pulse work on shared hosting?",
                answer: "Yes. Pulse is being designed for cPanel-style shared hosting."
            },
            {
                question: "Does Pulse support plugins?",
                answer: "Yes. Plugins are bundled and configurable from the admin panel."
            }
        ]
    },
    testimonial: {
        type: "testimonial",
        quote: "Pulse makes it simple to manage flexible content without heavy frontend tooling.",
        name: "Pulse User",
        role: "Website Owner"
    },
    html: {
        type: "html",
        html: "<div><h2>Custom HTML Block</h2><p>Add custom markup here.</p></div>"
    }
};

function safeParseJson(value) {
    try {
        const parsed = JSON.parse(value || "[]");
        return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
        return [];
    }
}

function syncTextarea() {
    builderData.value = JSON.stringify(blocks, null, 2);
}

function renderBuilder() {
    builderCanvas.innerHTML = "";

    if (!blocks.length) {
        builderCanvas.innerHTML = `
            <div class="pulse-builder-empty">
                <span class="material-symbols-rounded">view_quilt</span>
                <h3>No blocks yet</h3>
                <p>Add a hero, image, video, CTA, accordion, stats, or custom section to begin.</p>
            </div>
        `;
        syncTextarea();
        return;
    }

    blocks.forEach((block, index) => {
        const card = document.createElement("article");
        card.className = "pulse-builder-item";

        card.innerHTML = `
            <div class="pulse-builder-item-head">
                <div>
                    <strong>${block.type || "block"}</strong>
                    <span>Section ${index + 1}</span>
                </div>

                <div class="pulse-builder-item-actions">
                    <button type="button" data-move-up="${index}">
                        <span class="material-symbols-rounded">keyboard_arrow_up</span>
                    </button>

                    <button type="button" data-move-down="${index}">
                        <span class="material-symbols-rounded">keyboard_arrow_down</span>
                    </button>

                    <button type="button" data-remove="${index}">
                        <span class="material-symbols-rounded">delete</span>
                    </button>
                </div>
            </div>

            <pre>${escapeHtml(JSON.stringify(block, null, 2))}</pre>
        `;

        builderCanvas.appendChild(card);
    });

    syncTextarea();
}

function escapeHtml(value) {
    return value
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;");
}

addButtons.forEach((button) => {
    button.addEventListener("click", () => {
        const type = button.dataset.builderAdd;

        if (!templates[type]) {
            return;
        }

        blocks.push(JSON.parse(JSON.stringify(templates[type])));
        renderBuilder();
    });
});

builderCanvas.addEventListener("click", (event) => {
    const removeButton = event.target.closest("[data-remove]");
    const moveUpButton = event.target.closest("[data-move-up]");
    const moveDownButton = event.target.closest("[data-move-down]");

    if (removeButton) {
        const index = Number(removeButton.dataset.remove);
        blocks.splice(index, 1);
        renderBuilder();
    }

    if (moveUpButton) {
        const index = Number(moveUpButton.dataset.moveUp);

        if (index > 0) {
            [blocks[index - 1], blocks[index]] = [blocks[index], blocks[index - 1]];
            renderBuilder();
        }
    }

    if (moveDownButton) {
        const index = Number(moveDownButton.dataset.moveDown);

        if (index < blocks.length - 1) {
            [blocks[index + 1], blocks[index]] = [blocks[index], blocks[index + 1]];
            renderBuilder();
        }
    }
});

builderData.addEventListener("input", () => {
    blocks = safeParseJson(builderData.value);
    renderBuilder();
});

blocks = safeParseJson(builderData.value);
renderBuilder();
