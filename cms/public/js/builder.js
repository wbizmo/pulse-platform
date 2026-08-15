(() => {
    const input = document.querySelector('#pulseBuilderData');
    if (!input || !window.PULSE_BUILDER) return;
    const canvas = document.querySelector('#pulseBuilderCanvas');
    const templateInput = document.querySelector('#pulseBuilderTemplateData');
    const config = window.PULSE_BUILDER;
    const key = `pulse:builder:${config.page}:${config.version}`;
    const registry = new Map(config.registry.map(block => [block.type, block]));
    let documentValue;
    try { documentValue = JSON.parse(input.value); } catch { documentValue = {schema_version: 1, nodes: []}; }
    let dirty = false;

    const uuid = () => crypto.randomUUID();
    const freshNode = type => ({id: uuid(), type, props: structuredClone(registry.get(type).defaults), settings: {}, children: []});
    const cloneNode = node => ({...structuredClone(node), id: uuid(), children: node.children.map(cloneNode)});
    const sync = () => {
        input.value = JSON.stringify(documentValue, null, 2);
        if (templateInput) templateInput.value = input.value;
        if (dirty && input.value.length <= 131072) localStorage.setItem(key, input.value);
    };
    const change = fn => { fn(); dirty = true; sync(); render(); };
    const escape = value => String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const render = () => {
        canvas.innerHTML = '';
        const list = (nodes, parent) => nodes.forEach((node, index) => {
            const card = document.createElement('article');
            card.className = 'p-card'; card.dataset.nodeId = node.id; card.setAttribute('aria-label', `${registry.get(node.type)?.label || node.type} block`);
            card.innerHTML = `<strong>${escape(registry.get(node.type)?.label || node.type)}</strong><div class="p-actions"><button type="button" data-up aria-label="Move block up">↑</button><button type="button" data-down aria-label="Move block down">↓</button><button type="button" data-duplicate>Duplicate</button><button type="button" data-delete>Delete</button></div><label>Properties JSON<textarea data-props rows="4">${escape(JSON.stringify(node.props, null, 2))}</textarea></label><label>Alignment<select data-alignment><option value="">Default</option><option>left</option><option>center</option><option>right</option></select></label>${registry.get(node.type)?.container ? '<div class="p-actions"><label>Child block<select data-child-type></select></label><button type="button" data-add-child>Add nested block</button></div>' : ''}<div data-children></div>`;
            card.querySelector('[data-alignment]').value = node.settings.alignment || '';
            card.querySelector('[data-up]').onclick = () => index && change(() => nodes.splice(index - 1, 0, nodes.splice(index, 1)[0]));
            card.querySelector('[data-down]').onclick = () => index < nodes.length - 1 && change(() => nodes.splice(index + 1, 0, nodes.splice(index, 1)[0]));
            card.querySelector('[data-duplicate]').onclick = () => change(() => nodes.splice(index + 1, 0, cloneNode(node)));
            card.querySelector('[data-delete]').onclick = () => change(() => nodes.splice(index, 1));
            card.querySelector('[data-alignment]').onchange = event => change(() => event.target.value ? node.settings.alignment = event.target.value : delete node.settings.alignment);
            card.querySelector('[data-props]').onchange = event => { try { const props = JSON.parse(event.target.value); change(() => node.props = props); } catch { event.target.setCustomValidity('Properties must be valid JSON.'); event.target.reportValidity(); } };
            if (registry.get(node.type)?.container) {
                const selector = card.querySelector('[data-child-type]');
                config.registry.filter(block => block.type !== 'section').forEach(block => selector.add(new Option(block.label, block.type)));
                card.querySelector('[data-add-child]').onclick = () => change(() => node.children.push(freshNode(selector.value)));
            }
            parent.append(card);
            if (registry.get(node.type)?.container) list(node.children, card.querySelector('[data-children]'));
        });
        list(documentValue.nodes, canvas);
    };

    document.querySelectorAll('[data-builder-add]').forEach(button => button.onclick = () => change(() => documentValue.nodes.push(freshNode(button.dataset.builderAdd))));
    document.querySelectorAll('[data-builder-template-document]').forEach(button => button.onclick = () => change(() => { const source = JSON.parse(button.dataset.builderTemplateDocument); documentValue.nodes.push(...source.nodes.map(cloneNode)); }));
    document.querySelectorAll('[data-builder-viewport]').forEach(button => button.onclick = () => canvas.dataset.viewport = button.dataset.builderViewport);
    document.querySelector('.p-module-builder-form').addEventListener('submit', () => { dirty = false; localStorage.removeItem(key); sync(); });
    window.addEventListener('beforeunload', event => { if (dirty) event.preventDefault(); });
    const saved = localStorage.getItem(key); const recovery = document.querySelector('#pulseBuilderRecovery');
    if (saved && saved !== input.value) { recovery.hidden = false; recovery.querySelector('[data-builder-restore]').onclick = () => { try { documentValue = JSON.parse(saved); dirty = true; recovery.hidden = true; sync(); render(); } catch { localStorage.removeItem(key); } }; recovery.querySelector('[data-builder-discard-draft]').onclick = () => { localStorage.removeItem(key); recovery.hidden = true; }; }
    sync(); render();
})();
