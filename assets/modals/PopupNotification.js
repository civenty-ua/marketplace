import Swal from 'sweetalert2';
import PopupWindow from "./PopupWindow";

export default class PopupNotification extends PopupWindow{
    constructor(title, icon, message, additionalParameters = false, customDefaults = false) {
        super(title,icon ,message ,additionalParameters ,customDefaults );

        this._defaults = {
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', this._window.stopTimer)
                toast.addEventListener('mouseleave', this._window.resumeTimer)
            }
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