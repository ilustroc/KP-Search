//
const initializeSearchTabs = () => {
    const container = document.querySelector('[data-search-tabs]');

    if (!container) {
        return;
    }

    const tabs = Array.from(container.querySelectorAll('[data-module-tab]'));
    const panel = container.querySelector('#search-module-panel');
    const cache = new Map();
    let activeRequest = null;

    const renderState = (state) => {
        const template = container.querySelector(`[data-module-state-template="${state}"]`);

        if (template && panel) {
            panel.replaceChildren(template.content.cloneNode(true));
        }
    };

    const selectTab = async (tab) => {
        if (!panel) {
            return;
        }

        tabs.forEach((candidate) => {
            const isSelected = candidate === tab;
            candidate.setAttribute('aria-selected', String(isSelected));
            candidate.setAttribute('tabindex', isSelected ? '0' : '-1');
            candidate.classList.toggle('border-kp-green-dark', isSelected);
            candidate.classList.toggle('text-kp-green-dark', isSelected);
            candidate.classList.toggle('border-transparent', !isSelected);
            candidate.classList.toggle('text-slate-600', !isSelected);
        });

        const module = tab.dataset.module;

        if (cache.has(module)) {
            panel.innerHTML = cache.get(module);
            return;
        }

        activeRequest?.abort();
        activeRequest = new AbortController();
        renderState('loading');

        try {
            const response = await fetch(tab.dataset.url, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: activeRequest.signal,
            });

            if (response.status === 403) {
                renderState('forbidden');
                return;
            }

            if (!response.ok) {
                renderState('error');
                return;
            }

            const html = await response.text();
            cache.set(module, html);
            panel.innerHTML = html;
        } catch (error) {
            if (error.name !== 'AbortError') {
                renderState('error');
            }
        }
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(tab));
        tab.addEventListener('keydown', (event) => {
            const keys = ['ArrowLeft', 'ArrowRight', 'Home', 'End'];

            if (!keys.includes(event.key)) {
                return;
            }

            event.preventDefault();
            const targetIndex = event.key === 'Home'
                ? 0
                : event.key === 'End'
                    ? tabs.length - 1
                    : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;

            tabs[targetIndex].focus();
            selectTab(tabs[targetIndex]);
        });
    });

    if (tabs[0]) {
        selectTab(tabs[0]);
    }
};

document.addEventListener('DOMContentLoaded', initializeSearchTabs);
