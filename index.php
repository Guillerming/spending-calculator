<?php

    $session_timestamp = (new DateTime())->getTimestamp();


    // STOP CONFIG

    $HTML_PAST = '';
    $HTML_FUTURE = '';
    $HTML_BUDGET = '';


    $months = json_decode(file_get_contents(__DIR__ . '/db/months.json'));
    $budget = json_decode(file_get_contents(__DIR__ . '/db/budget.json'));

    $expenses = scandir(__DIR__ . '/expenses/');
    $futures = array();

    $year = null;
    $month = null;
    $day = null;

    function printYear() {
        global $HTML_PAST;
        global $year;
        $HTML_PAST .= '<p class="title year">' . $year . '</p>';
    }

    function printMonth() {
        global $HTML_PAST;
        global $months;
        global $month;
        $HTML_PAST .= '<p class="title month">' . $months[$month - 1] . '</p>';
    }

    function printDay() {
        global $HTML_PAST;
        global $day;
        $suffix = 'th';
        if ( $day == 1 ) { $suffix = 'st'; }
        if ( $day == 2 ) { $suffix = 'nd'; }
        if ( $day == 3 ) { $suffix = 'rd'; }
        $HTML_PAST .= '<p class="title day">'. $day . $suffix . '</p>';
    }

    function printPastExpense($data) {
        global $HTML_PAST;
        $amount = $data->expense->amount * -1;
        if ( $amount > 0 ) {
            $class = 'good';
            $amount = '+' . $amount;
        } else {
            $class = 'bad';
        }
        $HTML_PAST .= '<p class="expense"><span class="value ' . $class . '">' . $amount . '€</span> <span class="title">' . $data->expense->title . '</span></p>';
    }

    function printFutureExpense($data) {
        global $HTML_FUTURE;
        $amount = $data->expense->amount * -1;
        if ( $amount > 0 ) {
            $class = 'good';
            $amount = '+' + $amount;
        } else {
            $class = 'bad';
        }
        $HTML_FUTURE .= '<p class="expense"><span class="value ' . $class . '">' . $amount . '€</span>  <span class="title">' . $data->expense->title . '</span></p>';
    }

    function printMonthResult() {
        global $money_rest;
        global $HTML_PAST;
        global $months;
        global $month;
        if ( $money_rest > 1 ) {
            $class = 'good';
            $money_rest = '+' . $money_rest;
        } else {
            $class = 'bad';
        }
        $HTML_PAST .= '<p class="expense money-rest"><span class="value ' . $class . '">'. $money_rest .'€</span>' .  $months[$month - 1] . ' balance</p>';
    }

?>

<html>

    <head>
        <title>Monthly expense tracker</title>
        <meta charset="utf-8" />
        <link rel="preconnect" href="https://fonts.gstatic.com" />
        <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300;0,400;0,700;0,900&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous" />
        <link rel="stylesheet" type="text/css" href="/assets/css/styles.css" />
        <link rel="stylesheet" type="text/css" href="/assets/css/sidebar.css" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    </head>

    <body>
        <div class="container py-4">

            <h2>Add expenses or income:</h2>
            <form class="form">
                <div class="row mb-3">
                    <div class="col-12 col-sm-7 col-md-8 col-xl-6 mb-3">
                        <label for="expense-title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="expense-title" />
                    </div>
                    <div class="col-12 col-sm-5 col-md-4 col-xl-3 mb-3">
                        <label for="expense-amount" class="form-label">Amount</label>
                        <input type="number" step="any" class="form-control" id="expense-amount" />
                    </div>
                    <div class="col-12 col-xl-3 mb-3">
                        <label for="expense-date" class="form-label">Date</label>
                        <input type="text" class="form-control" id="expense-date" value="<?php echo (new DateTime())->format(DateTime::ATOM); ?>" />
                    </div>
                </div>
                <button type="submit" class="btn btn-primary submit-button">Submit</button>
                <p class="feedback small mt-3"></p>
            </form>

            <div class="row">
                <div class="col-12 col-md-4 sidebar order-2">
                    <p class="h4 mb-3">Monthly budget:</p>
                    <ul>
                        <?php

                            function printBudgetLine( $title, $amount ) {
                                global $HTML_BUDGET;
                                $HTML_BUDGET .= '<li><span>'.$title.'</span> <span>'.$amount.'€</span></li>';
                            }

                            $budget_income = 0;
                            $budget_expenses = 0;

                            for ( $n = 0; $n < count($budget->income); $n++ ) {
                                $budget_income = $budget_income + $budget->income[$n]->amount;
                                printBudgetLine( $budget->income[$n]->title, '+' . $budget->income[$n]->amount );
                            }
                            for ( $n = 0; $n < count($budget->expenses); $n++ ) {
                                $budget_expenses = $budget_expenses + $budget->expenses[$n]->amount;
                                printBudgetLine( $budget->expenses[$n]->title, ($budget->expenses[$n]->amount * -1) );
                            }

                            $month_budget = $budget_income - $budget_expenses;
                            $money_rest = $month_budget;

                            echo $HTML_BUDGET;

                        ?>
                        <li class="total">
                            <span><b>Total available</b></span>
                            <span><div class="value"><?php echo $month_budget; ?></div></span>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-md-8 order-1">
                    <div class="expenses pt-4">
                        <h2>Expenses out of budget:</h2>
                        <?php

                            /**
                             * Past expenses
                             */

                             for ($n = 0; $n < count($expenses); $n++) {
                                if ($expenses[$n] == '.' || $expenses[$n] == '..' || $expenses[$n] == '.DS_Store' || $expenses[$n] == 'thumbs.db' || $expenses[$n] == '.gitkeep' ) {
                                    continue;
                                }
                                $contents = json_decode(file_get_contents(__DIR__ . '/expenses/' . $expenses[$n]));

                                if ($contents->timestamp > $session_timestamp) {
                                    array_push($futures, $contents);
                                    continue;
                                }

                                if ($contents->date->year != $year) {
                                    $year = $contents->date->year;
                                    printYear();
                                }

                                if ($contents->date->month != $month) {
                                    if ( $month != '' ) {
                                        printMonthResult();
                                    }
                                    $month = $contents->date->month;
                                    $money_rest = $month_budget;
                                    printMonth();
                                }

                                if ($contents->date->day != $day) {
                                    $day = $contents->date->day;
                                    printDay();
                                }

                                $money_rest = $money_rest - $contents->expense->amount;
                                printPastExpense($contents);
                            }

                            printMonthResult();


                            $month++;
                            if ( $month >= 13 ) {
                                $month = 1;
                            }
                            printMonth();

                            echo '<div class="past-expenses">';
                            echo $HTML_PAST;
                            echo '</div>';

                            /**
                             * Future expenses
                             */

                            $year = null;
                            $month = null;
                            $day = null;


                            for ($n = 0; $n < count($futures); $n++) {

                                if ($futures[$n]->date->year != $year) {
                                    $year = $futures[$n]->date->year;
                                    printYear();
                                }

                                // if ($futures[$n]->date->month != $month) {
                                //     $month = $futures[$n]->date->month;
                                //     printMonth();
                                // }

                                if ($futures[$n]->date->day != $day) {
                                    $day = $futures[$n]->date->day;
                                    printDay();
                                }

                                printFutureExpense($futures[$n]);
                            }

                            if ( $HTML_FUTURE ) {
                                echo '<div class="future-expenses">';
                                echo $HTML_FUTURE;
                                echo '</div>';
                            }

                        ?>
                    </div>
                </div>
            </div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
        <script src="/assets/js/form.js"></script>
    </body>

</html>