<?php
require_once("config.php");
require_once("functions.php");
$page_name = "Stocking Level";
$_SESSION['page'] = 'stocking_levels.php';
login_check();
if (!isset($_SESSION['stocking_action']))
    header('Location: stocking_select_action.php');
require_once("assets.php");
$shift_inf = get_current_shift();
$booked_in_out = get_booked_in_out('Stocking', $shift_inf['shift'], $shift_inf['date']);
?>
<style>
    #chart {
        max-width: 50%;
        height: 400px;
    }
</style>
<script src="plugins/apexcharts.js"></script>

<body class="hold-transition sidebar-collapse layout-top-nav" onload="startTime()">
    <div class="wrapper">
        <?php include("header.php"); ?>
        <?php include("menu.php"); ?>
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <b>L/P(Week)</b>
                                    <input type="week" value="<?= date("o") . "-W" . date("W") ?>" id="lp-week" />
                                </div>
                                <div class="card-body">
                                    <div id="lp-week-chart" style="width: 100%;"></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <b>L/P(Month)</b>
                                    <input type="month" value="<?= date('Y-m') ?>" id="lp-month">
                                </div>
                                <div class="card-body">
                                    <div id="lp-month-chart" style="width: 100%;"></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <b>L/P(Year)</b>
                                    <input type="number" value="<?= date('Y') ?>" id="lp-year" max="<?= date('Y') ?>">
                                </div>
                                <div class="card-body">
                                    <div id="lp-year-chart" style="width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <b>H/P(Week)</b>
                                    <input type="week" value="<?= date("o") . "-W" . date("W") ?>" id="hp-week" />
                                </div>
                                <div class="card-body">
                                    <div id="hp-week-chart" style="width: 100%;"></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <b>H/P(Month)</b>
                                    <input type="month" value="<?= date('Y-m') ?>" id="hp-month">
                                </div>
                                <div class="card-body">
                                    <div id="hp-month-chart" style="width: 100%;"></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <b>H/P(Year)</b>
                                    <input type="number" value="<?= date('Y') ?>" id="hp-year" max="<?= date('Y') ?>">
                                </div>
                                <div class="card-body">
                                    <div id="hp-year-chart" style="width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.content-wrapper -->
            <?php include("footer.php"); ?>
        </div>

        <script src="plugins/jquery/jquery.min.js"></script>
        <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/adminlte.min.js"></script>
        <script src="assets/js/custom.js"></script>
        <script>
            $(document).ready(function() {
                var options = {
                    chart: {
                        type: 'bar'
                    },
                    series: [],
                    xaxis: {},
                    title: {
                        align: "center",
                        style: {
                            fontSize: '20px',
                            fontWeight: "bold"
                        }
                    }
                }

                options.title.text = "L/P(Week)"
                window.lpWeekChart = new ApexCharts(document.querySelector("#lp-week-chart"), options);
                window.lpWeekChart.render()
                updateWeekGraph("lp")
                $("#lp-week").on('change', function() {
                    updateWeekGraph("lp")
                })

                options.title.text = "H/P(Week)"
                window.hpWeekChart = new ApexCharts(document.querySelector("#hp-week-chart"), options);
                window.hpWeekChart.render()
                updateWeekGraph("hp")
                $("#hp-week").on('change', function() {
                    updateWeekGraph("hp")
                })

                options.title.text = "L/P(Month)"
                window.lpMonthChart = new ApexCharts(document.querySelector("#lp-month-chart"), options);
                window.lpMonthChart.render()
                updateMonthGraph("lp")
                $("#lp-month").on('change', function() {
                    updateMonthGraph("lp")
                })

                options.title.text = "H/P(Month)"
                window.hpMonthChart = new ApexCharts(document.querySelector("#hp-month-chart"), options);
                window.hpMonthChart.render()
                updateMonthGraph("hp")
                $("#hp-month").on('change', function() {
                    updateMonthGraph("hp")
                })

                options.title.text = "L/P(Year)"
                window.lpYearChart = new ApexCharts(document.querySelector("#lp-year-chart"), options);
                window.lpYearChart.render()
                updateYearGraph("lp")
                $("#lp-year").on('change', function() {
                    updateYearGraph("lp")
                })


                options.title.text = "H/P(Year)"
                window.hpYearChart = new ApexCharts(document.querySelector("#hp-year-chart"), options);
                window.hpYearChart.render()
                updateYearGraph("hp")
                $("#hp-year").on('change', function() {
                    updateYearGraph("hp")
                })
            })

            function updateWeekGraph(type) {
                var weekVal = $(`#${type}-week`).val()
                $.ajax({
                    url: "actions.php",
                    method: "post",
                    data: {
                        'action': 'read_graph_week',
                        'part': type,
                        'week': weekVal
                    },
                    dataType: 'JSON'
                }).done(function(res) {
                    if (type == 'lp')
                        var k = window.lpWeekChart
                    else
                        var k = window.hpWeekChart
                    k.updateOptions({
                        xaxis: {
                            categories: res.xaxis
                        },
                        series: [{
                            name: "IN",
                            data: res.in_count_array
                        }, {
                            name: "OUT",
                            data: res.out_count_array
                        }]
                    }, true)
                })
            }

            function updateMonthGraph(type) {
                var monthVal = $(`#${type}-month`).val()
                $.ajax({
                    url: "actions.php",
                    method: "post",
                    data: {
                        'action': 'read_graph_month',
                        'part': type,
                        'month': monthVal
                    },
                    dataType: 'JSON'
                }).done(function(res) {
                    if (type == 'lp')
                        var k = window.lpMonthChart
                    else
                        var k = window.hpMonthChart
                    k.updateOptions({
                        xaxis: {
                            categories: res.xaxis
                        },
                        series: [{
                            name: "IN",
                            data: res.in_count_array
                        }, {
                            name: "OUT",
                            data: res.out_count_array
                        }]
                    }, true)
                })
            }

            function updateYearGraph(type) {
                var yearVal = $(`#${type}-year`).val()
                $.ajax({
                    url: "actions.php",
                    method: "post",
                    data: {
                        'action': 'read_graph_year',
                        'part': type,
                        'year': yearVal
                    },
                    dataType: 'JSON'
                }).done(function(res) {
                    if (type == 'lp')
                        var k = window.lpYearChart
                    else
                        var k = window.hpYearChart
                    k.updateOptions({
                        xaxis: {
                            categories: res.xaxis
                        },
                        series: [{
                            name: "IN",
                            data: res.in_count_array
                        }, {
                            name: "OUT",
                            data: res.out_count_array
                        }]
                    }, true)
                })
            }
        </script>
</body>

</html>