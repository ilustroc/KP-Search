const initializeResultTabs = () => {
    const container = document.querySelector('[data-result-tabs]');

    if (!container) {
        return;
    }

    const tabs = Array.from(container.querySelectorAll('[data-result-tab]'));
    const panels = Array.from(container.querySelectorAll('[data-result-panel]'));

    const selectTab = (tab) => {
        const selectedPanel = tab.dataset.resultTab;

        tabs.forEach((candidate) => {
            const isSelected = candidate === tab;
            candidate.setAttribute('aria-selected', String(isSelected));
            candidate.setAttribute('tabindex', isSelected ? '0' : '-1');
            candidate.classList.toggle('bg-kp-green-dark', isSelected);
            candidate.classList.toggle('text-white', isSelected);
            candidate.classList.toggle('shadow-sm', isSelected);
            candidate.classList.toggle('bg-transparent', !isSelected);
            candidate.classList.toggle('text-slate-600', !isSelected);
            candidate.classList.toggle('hover:bg-slate-50', !isSelected);
            candidate.classList.toggle('hover:text-slate-900', !isSelected);
        });

        panels.forEach((panel) => {
            panel.hidden = panel.dataset.resultPanel !== selectedPanel;
        });
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

            selectTab(tabs[targetIndex]);
            tabs[targetIndex].focus();
        });
    });
};

const initializeExpandableTables = () => {
    document.querySelectorAll('[data-table-toggle]').forEach((button) => {
        const section = button.closest('section');
        const rows = section?.querySelectorAll('[data-expandable-row]') ?? [];

        button.addEventListener('click', () => {
            const isExpanded = button.getAttribute('aria-expanded') === 'true';

            rows.forEach((row) => {
                row.hidden = isExpanded;
            });
            button.setAttribute('aria-expanded', String(!isExpanded));
            button.textContent = isExpanded ? 'Ver más' : 'Ver menos';
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initializeResultTabs();
    initializeExpandableTables();
});
