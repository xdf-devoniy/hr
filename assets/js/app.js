(function () {
    const importInput = document.getElementById('transactionImport');
    if (importInput) {
        importInput.addEventListener('change', function () {
            const fileName = this.files?.[0]?.name || 'Choose CSV file';
            const label = document.querySelector('[for="transactionImport"]');
            if (label) {
                label.textContent = fileName;
            }
        });
    }

    const chartConfig = window.reportChartsData;
    if (chartConfig && typeof Chart !== 'undefined') {
        const currency = String(chartConfig.currency || 'USD').toUpperCase();
        const symbolMap = {
            USD: '$',
            EUR: '€',
            GBP: '£',
            CAD: 'C$',
            AUD: 'A$',
            JPY: '¥',
            INR: '₹',
            SUM: "so'm",
        };
        const suffixCurrencies = new Set(['INR', 'SUM']);
        const zeroDecimalCurrencies = new Set(['JPY']);

        const formatCurrency = (value) => {
            const decimals = zeroDecimalCurrencies.has(currency) ? 0 : 2;
            const absolute = Math.abs(value || 0);
            const formatted = absolute.toLocaleString(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            });
            const symbol = symbolMap[currency] || `${currency} `;
            const result = suffixCurrencies.has(currency)
                ? `${formatted} ${symbol}`
                : `${symbol}${formatted}`;
            return value < 0 ? `-${result}` : result;
        };

        const categoryConfig = chartConfig.category || {};
        if (Array.isArray(categoryConfig.labels) && categoryConfig.labels.length) {
            const ctx = document.getElementById('categoryChart');
            if (ctx) {
                const categoryChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: categoryConfig.labels,
                        datasets: [
                            {
                                data: categoryConfig.data || [],
                                backgroundColor: categoryConfig.colors || [],
                                hoverOffset: 4,
                                borderWidth: 0,
                            },
                        ],
                    },
                    options: {
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    padding: 16,
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const dataset = context.chart.data.datasets[context.datasetIndex];
                                        const total = Array.isArray(dataset.data)
                                            ? dataset.data.reduce((sum, value) => sum + (Number(value) || 0), 0)
                                            : 0;
                                        const rawValue = Number(context.parsed) || 0;
                                        const share = total ? ((rawValue / total) * 100).toFixed(1) : '0.0';
                                        return `${context.label}: ${formatCurrency(rawValue)} (${share}%)`;
                                    },
                                },
                            },
                        },
                    },
                });
                ctx.categoryChart = categoryChart;
            }
        }

        const trendConfig = chartConfig.trends || {};
        if (Array.isArray(trendConfig.labels) && trendConfig.labels.length) {
            const trendCanvas = document.getElementById('trendChart');
            if (trendCanvas) {
                const trendChart = new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: trendConfig.labels,
                        datasets: [
                            {
                                label: 'Income',
                                data: trendConfig.income || [],
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                                tension: 0.4,
                                fill: false,
                                pointRadius: 4,
                                pointBackgroundColor: '#22c55e',
                            },
                            {
                                label: 'Expenses',
                                data: trendConfig.expense || [],
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.18)',
                                tension: 0.4,
                                fill: false,
                                pointRadius: 4,
                                pointBackgroundColor: '#ef4444',
                            },
                            {
                                type: 'bar',
                                label: 'Net',
                                data: trendConfig.net || [],
                                backgroundColor: 'rgba(79, 70, 229, 0.35)',
                                borderRadius: 6,
                                borderSkipped: false,
                                order: 0,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.25)',
                                },
                                ticks: {
                                    callback: (value) => formatCurrency(value),
                                },
                            },
                            x: {
                                grid: {
                                    display: false,
                                },
                            },
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: (context) => {
                                        const rawValue = Number(context.parsed.y ?? context.parsed) || 0;
                                        return `${context.dataset.label}: ${formatCurrency(rawValue)}`;
                                    },
                                },
                            },
                            legend: {
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 12,
                                    boxHeight: 12,
                                },
                            },
                        },
                    },
                });
                trendCanvas.trendChart = trendChart;
            }
        }
    }
})();
