document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.analytics-chart').forEach((chart) => {
        const tooltip = chart.querySelector('[data-analytics-chart-tooltip]');
        const label = chart.querySelector('[data-analytics-chart-tooltip-label]');
        const value = chart.querySelector('[data-analytics-chart-tooltip-value]');

        if (!tooltip || !label || !value) {
            return;
        }

        chart.querySelectorAll('[data-analytics-chart-point]').forEach((point) => {
            const showTooltip = () => {
                label.textContent = point.dataset.label;
                value.textContent = `${Number(point.dataset.views).toLocaleString()} page views`;

                tooltip.style.left = point.style.left;
                tooltip.style.top = point.style.top;
                tooltip.hidden = false;
            };

            const hideTooltip = () => {
                tooltip.hidden = true;
            };

            point.addEventListener('mouseenter', showTooltip);
            point.addEventListener('mouseleave', hideTooltip);
            point.addEventListener('focus', showTooltip);
            point.addEventListener('blur', hideTooltip);
        });
    });
});