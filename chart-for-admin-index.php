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

    <canvas id='graphCanvas'>

    </canvas>

    <script>
    $(document).ready(function() {
        showGraph();
    });

    //Function to create bar graph with data from get-data-chart-admin-index.php
    function showGraph() {
        // Perform post request
        $.post("get-data-chart-admin-index.php", function(data) {
            // Store labels and totals into arrays
            var labels = [];
            var total = [];
            for (var i in data) {
                labels.push(data[i].label);
                total.push(data[i].total);
            }

            // Create bar color array
            var barColors = ["#f57f43", "#789b48", "#005677", "#cbc8c7", "#d5ca9e"];
            // Set chart parameters
            var chartData = {
                labels: labels,
                datasets: [{
                    label: 'Totals by Department',
                    backgroundColor: barColors,
                    borderColor: '#46d5f1',
                    hoverBackgroundColor: '#58c9e8',
                    data: total
                }]
            };
            // Get element to draw the graph on
            var graphTarget = $("#graphCanvas");
            // Create the chart
            var barGraph = new Chart(graphTarget, {
                type: 'bar',
                data: chartData,
            })

        });
    }
    </script>

</body>

</html>