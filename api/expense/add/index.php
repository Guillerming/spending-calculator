<?php

    $expense = array(
        'title' => $_REQUEST['expense_title'],
        'amount' => $_REQUEST['expense_amount'],
        'date' => $_REQUEST['expense_date']
    );


    $timestamp = (new DateTime($expense['date']))->getTimestamp();
    $original_date = explode('T', $expense['date']);
    $original_date[0] = explode('-', $original_date[0]);
    $original_date[1] = explode('+', $original_date[1]);
    $original_date[1][0] = explode(':', $original_date[1][0]);
    $output = array(
        'timestamp' => $timestamp,
        'date' => array(
            'year' => $original_date[0][0],
            'month' => $original_date[0][1],
            'day' => $original_date[0][2],
            'hour' => $original_date[1][0][0],
            'minute' => $original_date[1][0][1],
            'second' => $original_date[1][0][2]
        ),
        'expense' => array(
            'title' => $expense['title'],
            'amount' => $expense['amount']
        )
    );


    if ( file_put_contents( '../../../expenses/'.$timestamp.'.json', json_encode($output, JSON_PRETTY_PRINT) ) ) {
        http_response_code(200);
        echo 'OK';
        return;
    }

    http_response_code(400);
    echo 'Something failed';
    return;