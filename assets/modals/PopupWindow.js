import Swal from 'sweetalert2';

/**
 * @function show() - Serve for showing modal
 * @function hide() - Hide form immediately
 * @funtion getWindow() - Get current modal instance
 * @param title - Set title of modal
 * @param icon - Set icon of modal
 * @param message - Set message of modal
 * @param customDefaults - Serve for rewrite default values of modal
 */
export default class PopupWindow {
    _parameters = {};
    _defaults = {};


    _onConfirm = () => {};
    _onDismiss = () => {};
    _onHide = () => {};

    _window = null;
    _permittedIcons = ["success", "error", "warning", "info", "question"];

    constructor(title, icon, message, additionalParameters = false, customDefaults = false) {

        this._defaults = {
            confirmButtonText: 'OK',
            buttonsStyling: false,
            confirmButtonColor: '#007a33',
            customClass: {
                confirmButton: 'swal2-oun-style-button'
            },
            showClass: {
                popup: 'animate__animated animate__fadeIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOut animate__faster'
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

    /**
     * @event onConfirm - fires when clicked ok btn
     * @event onDismiss - fires when cancel btn clicked
     * @event onHide - fires when form hides
     */
    on(event, callback = (result) => {}){
        switch (event) {
            case 'onConfirm':
                this._onConfirm = callback;
                break;
            case 'onDismiss':
                this._onDismiss = callback;
                break;
            case 'onHide':
                this._onHide = callback;
                break;
        }
        return this;
    }
    _checkIfIconPermited(icon){
        if (!this._permittedIcons.includes(icon)) {
            icon = '';
            console.info(`${icon} icon is not allowed!`);
        }
        return icon;
    }

    show() {
        if (this._window === null) return;

        let instance = this;
        this._window.fire().then
        (function (result){
            if(result.isConfirmed)
            {
                instance._onConfirm(result);
            }else if(result.isDismissed){
                instance._onDismiss(result);
            }
            instance._onHide(result);
        });
    }

    getWindow() {
        return this._window;
    }

    hide() {
        if (this._window === null) return;
        this._onHide();
        this._window.close();
    }
}