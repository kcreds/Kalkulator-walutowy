$(document).ready(function () {
    $('#CalculatorForm').submit(function (event) {
        event.preventDefault();

        var formData = $(this).serialize();
        var amount = $('input[name="amount"]').val();
        var firstCode = $('select[name="firstCurrency"] option:selected').data('code');
        var secondCode = $('select[name="secondCurrency"] option:selected').data('code');

        $.ajax({
            url: 'calculator.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response === "Wprowadź poprawną kwotę") {
                    $('#result').html(response);
                }
                else if (response === "Wybierz poprawną walutę źródłową") {
                    $('#result').html(response);
                }
                else if (response === "Wybierz poprawną walutę docelową") {
                    $('#result').html(response);
                }
                else if (response === "Błąd zapisu danych") {
                    console.error('$errorMessage');
                }
                else {
                    $('#result').html(amount + " " + firstCode + " = " + response + " " + secondCode);
                }
            }
        });
    });
});
