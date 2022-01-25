(function($, Drupal){
    Drupal.behaviors.entryGateForm = {
      attach(context) {
        var dateSelect;
        $(context).find('.entry-gate-form').once('entryGateForm').each(function(){
            var element = context.querySelector('.entry-gate-form')
            var element2 = context.querySelector('.entry-gate-form__fieldset')
            var element1 = context.getElementsByClassName('entry-gate-form__fieldset');
            var format = element1[0].getAttribute("data-date-format");
            if(format == 'mm_yyyy') {
                element.classList.add("newEntryFormat")
                element2.classList.add("newSubmitBtn")
            }
            
        });
      },
    };
  })(jQuery, Drupal);
