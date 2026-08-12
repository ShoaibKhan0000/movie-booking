(() => {
    const initializeBookingTrendChart = () => {
        const chartElement = document.getElementById('booking-trends-chart');
        if (!chartElement || typeof Chart === 'undefined') {
            return;
        }

        const weeklyTrend = window.cinePassState?.weeklyTrend || [98, 114, 132, 149, 171, 164, 195];

        new Chart(chartElement, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Bookings',
                        data: weeklyTrend,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56, 189, 248, 0.2)',
                        pointBackgroundColor: '#e11d48',
                        pointBorderColor: '#e2e8f0',
                        borderWidth: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#cbd5e1',
                            font: {
                                family: 'Poppins',
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        borderColor: 'rgba(56, 189, 248, 0.55)',
                        borderWidth: 1,
                        titleColor: '#e2e8f0',
                        bodyColor: '#f8fafc',
                        padding: 12,
                        callbacks: {
                            label(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.12)'
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    },
                    y: {
                        beginAtZero: false,
                        suggestedMin: 80,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.12)'
                        },
                        ticks: {
                            color: '#94a3b8',
                            stepSize: 20
                        }
                    }
                }
            }
        });
    };

    document.addEventListener('DOMContentLoaded', initializeBookingTrendChart);
})();
