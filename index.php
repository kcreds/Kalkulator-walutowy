<?php include('head_layout.php'); ?>


<body>
    <div class="container">
        <div class='row'>
            <h1 class="mt-5">Kalkulator walutowy</h1>
            <hr>
            <form method="POST" action="#" id="CalculatorForm" class="">
                <div class="form-group">
                    <label for="amount">Kwota:</label>
                    <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="firstCurrency">Waluta źródłowa:</label>
                    <select name="firstCurrency" class="form-control" required>
                        <?php foreach ($NBPApiData as $rate) { ?>
                            <option value="<?php echo $rate['mid']; ?>" data-code="<?php echo $rate['code']; ?>">
                                <?php echo $rate['code']; ?> - <?php echo $rate['currency']; ?>
                            </option>
                        <?php } ?>
                    </select>

                </div>

                <div class="form-group">
                    <label for="secondCurrency">Waluta docelowa:</label>
                    <select name="secondCurrency" class="form-control" required>
                        <?php foreach ($NBPApiData as $rate) { ?>
                            <option value="<?php echo $rate['mid']; ?>" data-code="<?php echo $rate['code']; ?>">
                                <?php echo $rate['code']; ?> - <?php echo $rate['currency']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary mt-3" id="submit">Przelicz</button>
            </form>
        </div>

        <div class='row position-static'>
            <p id="result" class="mt-3 mb-2 h3 text-center font-weight-bold">&nbsp;</p>
        </div>

        <div class='row'>
            <p class="mt-5 h2 text-start">Historia ostatnich wyników z kalkulatora</p>
            <hr>
            <?php echo $calcTable; ?>
            </dvi>

            <div class='row'>
                <p class="mt-5 h2 text-start">Tabela aktualnych kursów z NBP</p>
                <hr>
                <?php echo $ratesTable; ?>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="script.js"></script>
</body>

</html>