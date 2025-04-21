<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script src="https://widget.cloudpayments.ru/bundles/cloudpayments.js"></script>
</head>
<body>
<input class="btn" id="payButton" value="Оплатить" type="button">
<script>
    var payments = new cp.CloudPayments({
        language: "ru-RU",
        email: "",
        applePaySupport: true,
        googlePaySupport: true,
        yandexPaySupport: true,
        tinkoffPaySupport: true,
        tinkoffInstallmentSupport: true,
        sbpSupport: true
    })

    payments.pay("charge", {
        publicId: "test_api_00000000000000000000002",
        description: "Тестовая оплата",
        amount: 100,
        currency: "RUB",
        invoiceId: "123",
        accountId: "123",
        email: "",
        skin: "mini",
        requireEmail: false,
    }).then(function(widgetResult) {
        console.log('result', widgetResult);
    }).catch(function(error) {
        console.log('error', error);
    });
</script>

</body>

</html>
