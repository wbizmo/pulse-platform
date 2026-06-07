const builderData = document.getElementById("pulseBuilderData");
const builderCanvas = document.getElementById("pulseBuilderCanvas");
const builderInspector = document.getElementById("pulseBuilderInspector");
const addButtons = document.querySelectorAll("[data-builder-add]");

let blocks = [];
let selectedIndex = null;

const templates = {
    hero: {
        type: "hero",
        eyebrow: "Pulse CMS",
        title: "Build Better Websites",
        description: "Create beautiful experiences with Pulse CMS.",
        button_label: "Get Started",
        button_url: "#"
    },

    text: {
        type: "text",
        content: "Your content goes here."
    },

    image: {
        type: "image",
        url: "",
        alt: "Image description"
    },

    video: {
        type: "video",
        embed: ""
    },

    cta: {
        type: "cta",
        title: "Ready to start?",
        description: "Create your next project with Pulse.",
        button_label: "Contact Us",
        button_url: "#"
    },

    features: {
        type: "features",
        title: "Features",
        description: "Everything you need to build a modern website.",
        items: [
            {
                icon: "palette",
                title: "Themes",
                description: "Customize bundled themes."
            },
            {
                icon: "extension",
                title: "Plugins",
                description: "Activate built-in plugins."
            },
            {
                icon: "article",
                title: "Pages",
                description: "Create flexible pages."
            }
        ]
    },

    stats: {
        type: "stats",
        items: [
            {
                value: "5+",
                label: "Themes"
            },
            {
                value: "20+",
                label: "Plugins"
            },
            {
                value: "100%",
                label: "Blade"
            }
        ]
    },

    accordion: {
        type: "accordion",
        title: "Frequently Asked Questions",
        items: [
            {
                question: "Can Pulse run on shared hosting?",
                answer: "Yes. Pulse is designed with cPanel-style hosting in mind."
            }
        ]
    },

    testimonial: {
        type: "testimonial",
        quote: "Pulse makes it easier to build flexible websites.",
        name: "Pulse User",
        role: "Website Owner"
    },

    html: {
        type: "html",
        html: "<div><h2>Custom HTML</h2><p>Add your markup here.</p></div>"
    }
};

function safeParseJson(value) {
    try {
        const parsed = JSON.parse(value || "[]");
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function syncTextarea() {
    builderData.value = JSON.stringify(blocks, null, 2);
}

function escapeHtml(text) {
    return String(text || "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;");
}

function renderBuilder() {
    if (!blocks.length) {
        builderCanvas.innerHTML = `
            <div class="pulse-builder-empty">
                <span class="material-symbols-rounded">view_quilt</span>
                <h3>No blocks yet</h3>
                <p>Add a block to begin building this page.</p>
            </div>
        `;

        selectedIndex = null;
        renderInspector();
        syncTextarea();
        return;
    }

    builderCanvas.innerHTML = blocks.map((block, index) => {
        return `
            <article
                class="pulse-builder-item ${selectedIndex === index ? "pulse-builder-selected" : ""}"
                data-select-block="${index}"
            >
                <div class="pulse-builder-item-head">
                    <div>
                        <strong>${escapeHtml(block.type)}</strong>
                        <span>Block ${index + 1}</span>
                    </div>

                    <div class="pulse-builder-item-actions">
                        <button type="button" data-up="${index}">
                            <span class="material-symbols-rounded">keyboard_arrow_up</span>
                        </button>

                        <button type="button" data-down="${index}">
                            <span class="material-symbols-rounded">keyboard_arrow_down</span>
                        </button>

                        <button type="button" data-delete="${index}">
                            <span class="material-symbols-rounded">delete</span>
                        </button>
                    </div>
                </div>

                ${renderBlockPreview(block)}
            </article>
        `;
    }).join("");

    syncTextarea();
}

function renderBlockPreview(block) {
    switch (block.type) {
        case "hero":
            return `
                <div class="pulse-builder-preview pulse-builder-preview-hero">
                    <span>${escapeHtml(block.eyebrow)}</span>
                    <h2>${escapeHtml(block.title)}</h2>
                    <p>${escapeHtml(block.description)}</p>
                    ${block.button_label ? `<button type="button">${escapeHtml(block.button_label)}</button>` : ""}
                </div>
            `;

        case "text":
            return `
                <div class="pulse-builder-preview">
                    <p>${escapeHtml(block.content)}</p>
                </div>
            `;

        case "image":
            return `
                <div class="pulse-builder-preview">
                    ${
                        block.url
                            ? `<img class="pulse-builder-preview-image" src="${escapeHtml(block.url)}" alt="${escapeHtml(block.alt)}">`
                            : `<div class="pulse-builder-placeholder"><span class="material-symbols-rounded">image</span><p>No image selected</p></div>`
                    }
                </div>
            `;

        case "video":
            return `
                <div class="pulse-builder-preview">
                    <div class="pulse-builder-placeholder">
                        <span class="material-symbols-rounded">smart_display</span>
                        <p>${block.embed ? "Video embed added" : "No video embed added"}</p>
                    </div>
                </div>
            `;

        case "cta":
            return `
                <div class="pulse-builder-preview pulse-builder-preview-cta">
                    <h2>${escapeHtml(block.title)}</h2>
                    <p>${escapeHtml(block.description)}</p>
                    ${block.button_label ? `<button type="button">${escapeHtml(block.button_label)}</button>` : ""}
                </div>
            `;

        case "features":
            return `
                <div class="pulse-builder-preview">
                    <h3>${escapeHtml(block.title)}</h3>
                    <p>${escapeHtml(block.description)}</p>
                    <div class="pulse-builder-preview-grid">
                        ${(block.items || []).map(item => `
                            <div>
                                <strong>${escapeHtml(item.title)}</strong>
                                <span>${escapeHtml(item.description)}</span>
                            </div>
                        `).join("")}
                    </div>
                </div>
            `;

        case "stats":
            return `
                <div class="pulse-builder-preview">
                    <div class="pulse-builder-preview-grid">
                        ${(block.items || []).map(item => `
                            <div>
                                <strong>${escapeHtml(item.value)}</strong>
                                <span>${escapeHtml(item.label)}</span>
                            </div>
                        `).join("")}
                    </div>
                </div>
            `;

        case "accordion":
            return `
                <div class="pulse-builder-preview">
                    <h3>${escapeHtml(block.title)}</h3>
                    ${(block.items || []).map(item => `
                        <div class="pulse-builder-preview-row">
                            <strong>${escapeHtml(item.question)}</strong>
                            <span>${escapeHtml(item.answer)}</span>
                        </div>
                    `).join("")}
                </div>
            `;

        case "testimonial":
            return `
                <div class="pulse-builder-preview">
                    <p>“${escapeHtml(block.quote)}”</p>
                    <strong>${escapeHtml(block.name)}</strong>
                    <span>${escapeHtml(block.role)}</span>
                </div>
            `;

        case "html":
            return `
                <div class="pulse-builder-preview">
                    <pre>${escapeHtml(block.html)}</pre>
                </div>
            `;

        default:
            return `
                <div class="pulse-builder-preview">
                    <pre>${escapeHtml(JSON.stringify(block, null, 2))}</pre>
                </div>
            `;
    }
}

function renderInspector() {
    if (selectedIndex === null || !blocks[selectedIndex]) {
        builderInspector.innerHTML = `
            <div class="pulse-builder-inspector-empty">
                <span class="material-symbols-rounded">touch_app</span>
                <h4>No block selected</h4>
                <p>Click any builder block to edit its content.</p>
            </div>
        `;
        return;
    }

    const block = blocks[selectedIndex];

    builderInspector.innerHTML = `
        <div class="pulse-builder-inspector-active">
            <div class="pulse-builder-inspector-title">
                <strong>${escapeHtml(block.type)} block</strong>
                <span>Editing block ${selectedIndex + 1}</span>
            </div>

            ${renderFields(block)}
        </div>
    `;
}

function renderFields(block) {
    switch (block.type) {
        case "hero":
            return `
                ${inputField("eyebrow", "Eyebrow", block.eyebrow)}
                ${inputField("title", "Title", block.title)}
                ${textareaField("description", "Description", block.description)}
                ${inputField("button_label", "Button label", block.button_label)}
                ${inputField("button_url", "Button URL", block.button_url)}
            `;

        case "text":
            return textareaField("content", "Content", block.content, 8);

        case "image":
            return `
                ${inputField("url", "Image URL", block.url)}
                <button type="button" class="pulse-builder-inspector-btn" data-inspector-image>
                    <span class="material-symbols-rounded">perm_media</span>
                    Choose from media library
                </button>
                ${inputField("alt", "Alt text", block.alt)}
            `;

        case "video":
            return textareaField("embed", "Video embed code", block.embed, 8);

        case "cta":
            return `
                ${inputField("title", "Title", block.title)}
                ${textareaField("description", "Description", block.description)}
                ${inputField("button_label", "Button label", block.button_label)}
                ${inputField("button_url", "Button URL", block.button_url)}
            `;

        case "features":
            return `
                ${inputField("title", "Section title", block.title)}
                ${textareaField("description", "Section description", block.description)}
                ${jsonField("items", "Feature items JSON", block.items)}
            `;

        case "stats":
            return jsonField("items", "Stats JSON", block.items);

        case "accordion":
            return `
                ${inputField("title", "Section title", block.title)}
                ${jsonField("items", "Accordion items JSON", block.items)}
            `;

        case "testimonial":
            return `
                ${textareaField("quote", "Quote", block.quote)}
                ${inputField("name", "Name", block.name)}
                ${inputField("role", "Role", block.role)}
            `;

        case "html":
            return textareaField("html", "Custom HTML", block.html, 10);

        default:
            return jsonField("", "Block JSON", block);
    }
}

function inputField(key, label, value = "") {
    return `
        <div class="pulse-builder-form-group">
            <label>${label}</label>
            <input type="text" data-builder-field="${key}" value="${escapeAttribute(value)}">
        </div>
    `;
}

function textareaField(key, label, value = "", rows = 5) {
    return `
        <div class="pulse-builder-form-group">
            <label>${label}</label>
            <textarea rows="${rows}" data-builder-field="${key}">${escapeHtml(value)}</textarea>
        </div>
    `;
}

function jsonField(key, label, value) {
    return `
        <div class="pulse-builder-form-group">
            <label>${label}</label>
            <textarea rows="8" data-builder-json-field="${key}">${escapeHtml(JSON.stringify(value || [], null, 2))}</textarea>
        </div>
    `;
}

function escapeAttribute(value) {
    return escapeHtml(value).replaceAll('"', "&quot;");
}

function updateBlockField(key, value) {
    if (selectedIndex === null || !blocks[selectedIndex]) {
        return;
    }

    blocks[selectedIndex][key] = value;
    renderBuilder();
    renderInspector();
}

function updateBlockJsonField(key, value) {
    if (selectedIndex === null || !blocks[selectedIndex]) {
        return;
    }

    try {
        const parsed = JSON.parse(value || "[]");

        if (key) {
            blocks[selectedIndex][key] = parsed;
        } else {
            blocks[selectedIndex] = parsed;
        }

        renderBuilder();
        renderInspector();
    } catch {
        syncTextarea();
    }
}

addButtons.forEach(button => {
    button.addEventListener("click", () => {
        const type = button.dataset.builderAdd;

        if (!templates[type]) {
            return;
        }

        blocks.push(JSON.parse(JSON.stringify(templates[type])));
        selectedIndex = blocks.length - 1;

        renderBuilder();
        renderInspector();
    });
});

builderCanvas.addEventListener("click", event => {
    const remove = event.target.closest("[data-delete]");
    const up = event.target.closest("[data-up]");
    const down = event.target.closest("[data-down]");
    const select = event.target.closest("[data-select-block]");

    if (remove) {
        const index = Number(remove.dataset.delete);

        blocks.splice(index, 1);

        if (selectedIndex === index) {
            selectedIndex = null;
        } else if (selectedIndex !== null && selectedIndex > index) {
            selectedIndex--;
        }

        renderBuilder();
        renderInspector();
        return;
    }

    if (up) {
        const index = Number(up.dataset.up);

        if (index > 0) {
            [blocks[index], blocks[index - 1]] = [blocks[index - 1], blocks[index]];
            selectedIndex = index - 1;
            renderBuilder();
            renderInspector();
        }

        return;
    }

    if (down) {
        const index = Number(down.dataset.down);

        if (index < blocks.length - 1) {
            [blocks[index], blocks[index + 1]] = [blocks[index + 1], blocks[index]];
            selectedIndex = index + 1;
            renderBuilder();
            renderInspector();
        }

        return;
    }

    if (select) {
        selectedIndex = Number(select.dataset.selectBlock);
        renderBuilder();
        renderInspector();
    }
});

builderInspector.addEventListener("input", event => {
    const field = event.target.closest("[data-builder-field]");
    const jsonFieldTarget = event.target.closest("[data-builder-json-field]");

    if (field) {
        const key = field.dataset.builderField;
        blocks[selectedIndex][key] = field.value;
        syncTextarea();
        renderBuilder();
    }

    if (jsonFieldTarget) {
        const key = jsonFieldTarget.dataset.builderJsonField;
        updateBlockJsonField(key, jsonFieldTarget.value);
    }
});

builderInspector.addEventListener("click", event => {
    const imageButton = event.target.closest("[data-inspector-image]");

    if (!imageButton || selectedIndex === null) {
        return;
    }

    window.PulseMediaPicker.open(url => {
        blocks[selectedIndex].url = url;
        syncTextarea();
        renderBuilder();
        renderInspector();
    });
});

builderData.addEventListener("input", () => {
    blocks = safeParseJson(builderData.value);
    selectedIndex = null;
    renderBuilder();
    renderInspector();
});

blocks = safeParseJson(builderData.value);
renderBuilder();
renderInspector();
