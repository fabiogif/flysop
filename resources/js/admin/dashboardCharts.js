/**
 * Gráficos do dashboard (Fase 4). Lê os dados de window.dashboardChartsData, definido
 * inline em resources/views/admin/pages/home/index.blade.php via @json($charts).
 * Só inicializa se os elementos <canvas> existirem na página (ilha JS, mesmo padrão de
 * resources/js/admin/occurrenceMap.js).
 */
const Chart = require('chart.js/auto').default || require('chart.js/auto');

document.addEventListener('DOMContentLoaded', function () {
    const data = window.dashboardChartsData;
    if (!data) return;

    const byDayEl = document.getElementById('chart-occurrences-by-day');
    if (byDayEl && data.byDay) {
        new Chart(byDayEl, {
            type: 'line',
            data: {
                labels: Object.keys(data.byDay),
                datasets: [{
                    label: 'Ocorrências',
                    data: Object.values(data.byDay),
                    borderColor: '#2e86de',
                    backgroundColor: 'rgba(46, 134, 222, 0.15)',
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: { responsive: true, plugins: { legend: { display: false } } },
        });
    }

    const byStatusEl = document.getElementById('chart-occurrences-by-status');
    if (byStatusEl && data.byStatus) {
        new Chart(byStatusEl, {
            type: 'bar',
            data: {
                labels: Object.keys(data.byStatus),
                datasets: [{
                    label: 'Ocorrências',
                    data: Object.values(data.byStatus),
                    backgroundColor: '#1f3b6b',
                }],
            },
            options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } } },
        });
    }

    const byPriorityEl = document.getElementById('chart-occurrences-by-priority');
    if (byPriorityEl && data.byPriority && data.byPriority.length) {
        new Chart(byPriorityEl, {
            type: 'doughnut',
            data: {
                labels: data.byPriority.map(function (p) { return p.name; }),
                datasets: [{
                    data: data.byPriority.map(function (p) { return p.total; }),
                    backgroundColor: data.byPriority.map(function (p) { return p.color || '#6c757d'; }),
                }],
            },
            options: { responsive: true },
        });
    }
});
