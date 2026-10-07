document.addEventListener('DOMContentLoaded', () => {
    // Mobile sidebar toggle
    const toggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));

    // Donut chart: <div class="donut" data-values="8,5,9,2" data-colors="#a,#b,...">
    document.querySelectorAll('.donut').forEach(el => {
        const vals = el.dataset.values.split(',').map(Number);
        const cols = el.dataset.colors.split(',');
        const total = vals.reduce((a, b) => a + b, 0);
        let acc = 0;
        const stops = vals.map((v, i) => {
            const from = (acc / total) * 100;
            acc += v;
            return `${cols[i]} ${from}% ${(acc / total) * 100}%`;
        });
        el.style.background = `conic-gradient(${stops.join(',')})`;
    });
});