import Swal from 'sweetalert2';
import PopupWindow from "./PopupWindow";

export default class PopupWindowForm extends PopupWindow{
    constructor(title, icon, message, additionalParameters = false, customDefaults = false) {
        super(title,icon ,message ,additionalParameters ,customDefaults );
        this._defaults = {
            confirmButtonColor: '#007a33',
            showClass: {
                popup: 'animate__animated animate__fadeIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOut animate__faster'
            },
            customClass: {
                confirmButton: 'swal2-oun-style-button btn-h50-green',
                cancelButton: 'swal2-oun-style-button btn-h50-green-line'
            },
            showConfirmButton: true,
            showCancelButton: true,
            buttonsStyling: false,
            showCloseButton: true,
            reverseButtons: true,
        };
        if (typeof customDefaults === 'object') {
            this._defaults = {};
            Object.assign(this._defaults, customDefaults);
        }

        if (typeof additionalParameters === 'object') {
            this._parameters = {};
            Object.assign(this._parameters, additionalParameters);
        }
        Object.assign(this._parameters, this._defaults, {
            title: title,
            icon: this._checkIfIconPermited(icon),
            text: message
        });
        this._window = Swal.mixin(this._parameters);
    }
}