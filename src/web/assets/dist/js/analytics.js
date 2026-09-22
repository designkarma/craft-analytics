const getChartPoint = (target) => {
    return target.closest('[data-analytics-chart-point]');
};

const showTooltip = (point) => {
    const chart = point.closest('.analytics-chart');

    if (!chart) {
        return;
    }

    const tooltip = chart.querySelector(
        '[data-analytics-chart-tooltip]'
    );

    const label = chart.querySelector(
        '[data-analytics-chart-tooltip-label]'
    );

    const value = chart.querySelector(
        '[data-analytics-chart-tooltip-value]'
    );

    if (!tooltip || !label || !value) {
        return;
    }

    label.textContent = point.dataset.label;

    value.textContent =
        `${Number(point.dataset.views).toLocaleString()} page views`;

    tooltip.style.left = point.style.left;
    tooltip.style.top = point.style.top;
    tooltip.hidden = false;
};

const hideTooltip = (point) => {
    const chart = point.closest('.analytics-chart');

    if (!chart) {
        return;
    }

    const tooltip = chart.querySelector(
        '[data-analytics-chart-tooltip]'
    );

    if (tooltip) {
        tooltip.hidden = true;
    }
};

document.addEventListener('mouseover', (event) => {
    const point = getChartPoint(event.target);

    if (point) {
        showTooltip(point);
    }
});

document.addEventListener('mouseout', (event) => {
    const point = getChartPoint(event.target);

    if (point) {
        hideTooltip(point);
    }
});

document.addEventListener('focusin', (event) => {
    const point = getChartPoint(event.target);

    if (point) {
        showTooltip(point);
    }
});

document.addEventListener('focusout', (event) => {
    const point = getChartPoint(event.target);

    if (point) {
        hideTooltip(point);
    }
});