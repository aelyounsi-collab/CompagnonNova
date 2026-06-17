/* CompagnonNova — Chart.js helpers */
const CN = {
  gold:    '#C9A84C',
  navy:    '#141928',
  border:  '#1E2A42',
  muted:   '#6B7A99',
  white:   '#FFFFFF',
  green:   '#2D6A4F',
  red:     '#C0392B',
  yt:      '#FF5555',
  tk:      '#69C9D0',
  ig:      '#E1306C',
  fb:      '#1877F2',
};

const defaultTooltip = {
  backgroundColor: '#0D1220',
  titleColor: CN.gold,
  bodyColor: CN.white,
  borderColor: CN.border,
  borderWidth: 1,
  padding: 10,
  cornerRadius: 8,
};

const defaultGrid = {
  color: CN.border,
  drawBorder: false,
};

const defaultTicks = {
  color: CN.muted,
  font: { size: 11 },
};

// ── Line Chart ────────────────────────────────────
function lineChart(canvasId, labels, datasets, options = {}) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  const ds = datasets.map(d => ({
    tension: 0.4,
    fill: d.fill ?? false,
    pointRadius: 3,
    pointHoverRadius: 6,
    borderWidth: 2,
    ...d,
  }));
  return new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: ds },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: datasets.length > 1, labels: { color: CN.muted, font: { size: 11 } } },
        tooltip: defaultTooltip,
      },
      scales: {
        x: { grid: defaultGrid, ticks: defaultTicks },
        y: { grid: defaultGrid, ticks: defaultTicks, beginAtZero: true },
      },
      ...options,
    },
  });
}

// ── Doughnut Chart ────────────────────────────────
function doughnutChart(canvasId, labels, data, colors) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  return new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{ data, backgroundColor: colors, borderColor: CN.navy, borderWidth: 3, hoverOffset: 6 }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: { position: 'bottom', labels: { color: CN.muted, font: { size: 11 }, padding: 12 } },
        tooltip: defaultTooltip,
      },
    },
  });
}

// ── Bar Chart ─────────────────────────────────────
function barChart(canvasId, labels, datasets, options = {}) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  const ds = datasets.map(d => ({ borderRadius: 4, borderSkipped: false, ...d }));
  return new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: ds },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: datasets.length > 1, labels: { color: CN.muted, font: { size: 11 } } },
        tooltip: defaultTooltip,
      },
      scales: {
        x: { grid: { display: false }, ticks: defaultTicks },
        y: { grid: defaultGrid, ticks: defaultTicks, beginAtZero: true },
      },
      ...options,
    },
  });
}

// ── Horizontal Bar ────────────────────────────────
function hBarChart(canvasId, labels, data, color) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{ data, backgroundColor: color || CN.gold, borderRadius: 4, borderSkipped: false }],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: defaultTooltip },
      scales: {
        x: { grid: defaultGrid, ticks: defaultTicks, beginAtZero: true },
        y: { grid: { display: false }, ticks: defaultTicks },
      },
    },
  });
}
