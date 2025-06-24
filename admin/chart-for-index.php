<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src=" https://code.jquery.com/jquery-3.6.1.js"></script>
</head>

<body>

    <canvas id='chart-line' height="300">

    </canvas>


    <script>
        $(document).ready(function() {
            showGraph();
        });

        function showGraph() {
            {
                $.post("get-data-chart-index.php",
                    function(data) {
                        console.log(data);
                        var labels = [];
                        var total = [];

                        for (var i in data) {
                            labels.push(data[i].label);
                            total.push(data[i].total);
                        }

                        var ctx1 = document.getElementById("chart-line").getContext("2d");

                        var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

                        gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
                        gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
                        gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');
                        new Chart(ctx1, {
                            type: "line",
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: "Department Spending",
                                    tension: 0.4,
                                    borderWidth: 0,
                                    pointRadius: 0,
                                    borderColor: "#5e72e4",
                                    backgroundColor: gradientStroke1,
                                    borderWidth: 3,
                                    fill: true,
                                    data: total,
                                    maxBarThickness: 6

                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false,
                                    }
                                },
                                interaction: {
                                    intersect: false,
                                    mode: 'index',
                                },
                                scales: {
                                    y: {
                                        grid: {
                                            drawBorder: false,
                                            display: true,
                                            drawOnChartArea: true,
                                            drawTicks: false,
                                            borderDash: [5, 5]
                                        },
                                        ticks: {
                                            display: true,
                                            padding: 10,
                                            color: '#fbfbfb',
                                            font: {
                                                size: 11,
                                                family: "Open Sans",
                                                style: 'normal',
                                                lineHeight: 2
                                            },
                                        }
                                    },
                                    x: {
                                        grid: {
                                            drawBorder: false,
                                            display: false,
                                            drawOnChartArea: false,
                                            drawTicks: false,
                                            borderDash: [5, 5]
                                        },
                                        ticks: {
                                            display: true,
                                            color: '#ccc',
                                            padding: 20,
                                            font: {
                                                size: 11,
                                                family: "Open Sans",
                                                style: 'normal',
                                                lineHeight: 2
                                            },
                                        }
                                    },
                                },
                            },
                        });
                    }

                )
            }
        }
    </script>

</body>

</html>