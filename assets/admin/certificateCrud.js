import window from "inputmask/lib/global/window";

const $ = require('jquery');

import PopupWindowAdmin from "../modals/PopupWindowAdmin";

$(document).ready(function(){
    $('[data-action-name="deleteCertificate"]').click(function (event){
        event.stopPropagation();
        event.preventDefault();

        let additionalParameters = {
            input: 'textarea',
            inputLabel: 'Текст повідомлення користувачеві',
            inputPlaceholder: 'Залиште поле пустим якщо не бажаєте вказати додаткову інформацію користувачу',
            inputAttributes: {
                maxlength: 1000,
                autocapitalize: 'off',
                autocorrect: 'off'
            }
        }
        let btnInstance = $(this);
        let Popup = new PopupWindowAdmin('Відхилення сертифікату', null, '', additionalParameters);
        Popup.on('onConfirm', (result)=>{
            window.location.href = btnInstance.attr('href') + '&message=' + result.value;
        });
        Popup.show();
        return false;
    });
});