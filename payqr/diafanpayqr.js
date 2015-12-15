document.addEventListener("DOMContentLoaded", function(event) {
    if(typeof payQR  !== "undefined")
    {
        payQR.onPaid(function(data) {

            var message = "Ваш заказ #" + data.orderId + " успешно оплачен на сумму: " + data.amount + "! ";

            try{
                payqrUserData = $.parseJSON(data.userData);

                //прикручиваем скрипт очистки корзины
                $.get("payqr/payqr_config.php", function(data){
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

                if(typeof payqrUserData !== "undefined" && typeof payqrUserData.cart_id !== "undefined" && parseInt(payqrUserData.cart_id))
                {
                    redirectUrl += "/?id=" + payqrUserData.cart_id + "&shk_action=empty";
                }

                window.location.replace( redirectUrl );
            }
            catch(e)
            {
                alert("Возникли ошибки при обработке данных!");
            }

        });
    }
});