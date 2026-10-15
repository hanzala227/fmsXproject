
document.addEventListener('DOMContentLoaded', () => {
    // Mobile sidebar toggle
    const toggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    // Donut chart
    document.querySelectorAll('.donut').forEach(el => {
        const vals = (el.dataset.values || '')
            .split(',')
            .map(Number);

        const cols = (el.dataset.colors || '')
            .split(',');

        const total = vals.reduce((sum, value) => sum + value, 0);

        if (total <= 0 || vals.length === 0) {
            el.style.background = '#e5e7eb';
            return;
        }

        let acc = 0;

        const stops = vals.map((value, i) => {
            const from = (acc / total) * 100;
            acc += value;

            const to = (acc / total) * 100;
            const color = cols[i] || '#e5e7eb';

            return `${color} ${from}% ${to}%`;
        });

        el.style.background = `conic-gradient(${stops.join(', ')})`;
    });
});
