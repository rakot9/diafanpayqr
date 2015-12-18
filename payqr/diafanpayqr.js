document.addEventListener("DOMContentLoaded", function(event) {
    if(typeof payQR  !== "undefined")
    {
        payQR.onPaid(function(data) {

            var message = "Ваш заказ #" + data.orderId + " успешно оплачен на сумму: " + data.amount + "! ";

            try{
                payqrUserData = $.parseJSON(data.userData);

                //прикручиваем скрипт очистки корзины
                $.get('http://' + window.location.hostname + "/payqr/payqr_ajax.php?action=clear_cart", function(data){
                    console.log('Отправили информацию об очистке корзины');
                });
                //

                if(typeof payqrUserData !== "undefined" && typeof payqrUserData.new_account !== "undefined" &&
                    (payqrUserData.new_account == true || payqrUserData.new_account == "true"))
                {
                    message += " Администратор сайта свяжется с вами в самое ближайшее время!";
                }

                alert(message);

                redirectUrl = window.location.origin;

                window.location.replace( redirectUrl );
            }
            catch(e)
            {
                alert("Возникли ошибки при обработке данных!");
            }

        });
    }

    $('span[class*="js_cart_count_"]').click(function(){
        //
        $.get('http://' + window.location.hostname + "/payqr/payqr_ajax.php?action=get_cart_button").done(
            function(data){console.log("Done get_cart_button");}
        );
        //
    });
});