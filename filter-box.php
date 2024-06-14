<?php

$sql = "SELECT pf.product, gf.filter as gender, tf.filter as type, sf.filter as size, af.filter as sleeve, p.name, p.producttype
FROM products_filters pf
JOIN products p ON pf.product = p.product_id
JOIN filters_gender gf ON pf.gender_filter = gf.id
JOIN filters_type tf ON pf.type_filter = tf.id
JOIN filters_size sf ON pf.size_filter = sf.id
JOIN filters_sleeve af ON pf.sleeve_filter = af.id
WHERE p.producttype = $producttype";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$g_filters = array();
$t_filters = array();
$s_filters = array();
$a_filters = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // echo "<pre>";
        // echo print_r($row);
        // echo "</pre>";
        foreach ($row as $filter) {
            if (!in_array($row['gender'], $g_filters)) {
                array_push($g_filters, $row['gender']);
            };
            if (!in_array($row['type'], $t_filters)) {
                array_push($t_filters, $row['type']);
            };
            if (!in_array($row['size'], $s_filters)) {
                array_push($s_filters, $row['size']);
            }
            if (!in_array($row['sleeve'], $a_filters)) {
                array_push($a_filters, $row['sleeve']);
            }
        }
    }
}

?>

<div class="container">
    <form id="filter-form">
        <div class="product-filters">
            <div class="type-selection">
                <hr>
                <h4 class="filter-headers">Type</h4>
                <div class="typeSection" data-group="type">
                    <?php
                    foreach ($t_filters as $t) {

                        echo "<input type='checkbox' id='" . $t . "' name='type' value='" . $t . "'><label for='" . $t . "'>" . $t . "</label>";
                    }

                    ?>
                </div>
                <hr>
                <h4 class="filter-headers">Sleeves</h4>
                <div class="typeSection" data-group="sleeve">
                    <?php
                    foreach ($a_filters as $a) {

                        echo "<input type='checkbox' id='" . $a . "' name='sleeve' value='" . $a . "'><label for='" . $a . "'>" . $a . "</label>";
                    }
                    ?>
                </div>
                <hr>
                <h4 class="filter-headers">Size</h4>
                <div class="typeSection" data-group="size">
                    <?php
                    foreach ($s_filters as $s) {
                        echo "<input type='checkbox' id='" . $s . "' name='size' value='" . $s . "'><label for='" . $s . "'>" . $s . "</label>";
                    }
                    ?>
                </div>
                <hr>
                <h4 class="filter-headers">Gender</h4>
                <div class="typeSection" data-group="gender">
                    <?php
                    foreach ($g_filters as $g) {
                        echo "<input type='checkbox' id='" . $g . "' name='gender' value='" . $g . "'><label for='" . $g . "'>" . $g . "</label>";
                    }
                    ?>
                </div>
                <div>
                    <button id="button" type="button" id="filter-go-button" class="btn btn-primary">Filter</button>
                    <button id="reset-restore" type="button" form="filter-form" class="btn btn-warning">Reset
                        Filters</button>

                </div>
            </div>
        </div>
    </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
$(function() {
    $('#button').click(function() {
        // Create an map of category name to selected filters
        selectedFilters = $('.product-filters input:checked').get().reduce(function(a, c) {
            a[c.name] = (a[c.name] || []);
            a[c.name].push(c.value);
            return a
        }, {})
        $('#close-filters-box').trigger('click');
        // filter the list of products displayed
        match = 0
        unmatchedProd = $('.home-product-info').filter(function() {
            for (const category in selectedFilters) {
                // must match at least one in each category
                if (!selectedFilters[category].includes(this.dataset[category])) {
                    console.log(
                        `"${$(this).text().trim()}" does not match ${category} is in [${selectedFilters[category]}] (${category} is ${this.dataset[category]})`
                    )
                    return true
                }
                match++
            }
        });
        // do something with unmatchedProducts

        unmatchedProd.parent().hide()
        if (!match) {
            // console.log("Nothing matches the selected filters")
            alert("Nothing matches the selected filters");
            $('.home-product-info').show()
            $('input:checkbox').prop('checked', false)
        }
    })
    $('#restore').click(function() {
        console.log('Reset clicked');
        $('.home-product-info').parent().show()
        $('input:checkbox').prop('checked', false)
    })


});
$(function() {
    $('#reset-restore').click(function() {
        $('#restore').trigger('click');
    })
})
</script>
<style>
.type-section {
    position: relative;
    display: flex;
}


.typeSection label {
    color: black;
    border-radius: 10%;
    cursor: pointer;
    padding-left: .25em;
    padding-right: 1em;
}

.typeSection input[type="checkbox"] {
    width: 15px;
    height: 15px;
}

.typeSection input[type="checkbox"]:checked+label {
    background-color: #66bb6a;
    border-color: #66bb6a;
}

.typeSection input[type="checkbox"]:checked+label:after {
    opacity: 1;
}

.filter-headers {
    color: black;
}
</style>