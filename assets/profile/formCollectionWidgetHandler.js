const $ = require('jquery');
import Inputmask from 'inputmask';
//add-new-item-to-collection

$(document).ready(function(){
    $('body').on('click','.add-new-item-to-collection', (event) => {
        let btn = event.currentTarget;

        let attr = btn.getAttribute('data-tpl');
        let template = document.querySelector(attr);
        let clone = template.content.cloneNode(true);
        let inputs = clone.querySelectorAll('input');
        let rowParent = btn.closest('.collection-field');
        let rowsCount = rowParent.querySelectorAll('.collection-row').length;
        inputs.forEach((element) => {
            element.id = element.id.replaceAll('#id#', rowsCount);
            element.setAttribute('name',element.getAttribute('name').replaceAll('#id#', rowsCount));
        });

        rowParent.querySelector('.collection-items').append(clone);
        let phoneMask;
        phoneMask = rowParent.querySelectorAll('.js-phone-mask');

        phoneMask.forEach((element)=>{
            Inputmask({'mask': '+38 (999) 999 99 99'}).mask(element);
        });
    });

    let phones = document.querySelectorAll('[type="tel"]');

    phones.forEach(function(element){
        Inputmask({'mask': '+38 (999) 999 99 99'}).mask($(element));
    });

    $('.role-submit').click(()=>{
        $('.role-form form').submit();
    });
});