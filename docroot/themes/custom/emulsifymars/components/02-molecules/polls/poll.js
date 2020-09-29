(function($){
  $(document).ajaxComplete((event, xhr, settings) => {
    requestAnimationFrame(()=>Drupal.attachBehaviors());
  });
})(jQuery);
