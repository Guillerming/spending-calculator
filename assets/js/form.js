(function Form() {

    var $form = $('.form'),
        $feedback = $form.find('.feedback'),
        $inputTitle = $form.find('#expense-title'),
        $inputAmount = $form.find('#expense-amount'),
        $inputDate = $form.find('#expense-date'),
        $submitButton = $form.find('.submit-button');

    function submit() {
        $.ajax({
            url: '/api/expense/add/',
            method: 'POST',
            data: {
                expense_title: $inputTitle.val(),
                expense_amount: $inputAmount.val(),
                expense_date: $inputDate.val()
            }
        }).done(function( response ) {
            window.location.reload();
        }).fail(function( err ) {
            $feedback.html('Failure: ' + err);
        }).always(function() {});
    }


    $submitButton.on('click', function(e) {
        e.preventDefault();
        submit();
    });

})();