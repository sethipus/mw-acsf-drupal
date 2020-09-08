Drupal.behaviors.ajaxcardgrid = {
  attach(context) {
    const seeMoreBtn = document.querySelector('.ajax-card-grid__more-link .default-link');
    seeMoreBtn.addEventListener('click', (event) => {
      event.preventDefault();
      console.log('ajax request triggered');

      jQuery('.js-pager__items.pager li a.button').trigger('click');
    });

  },
};


// function loadMoreBACKUP(idx, column) {
//   // Dig out the node id from the header link.
//   // var link = $(column).find('header a');
//   // console.log('link', link);
//   // var href = $(link).attr('href');
//   // console.log('href', href);
//   // var matches = /node\/(\d*)/.exec(href);
//   // console.log('matches', matches);
//   // var nid = matches[1];
//   // console.log('nid', nid);
//   var nid = 1;
//
//   // Everything we need to specify about the view.
//   var view_info = {
//     view_name: 'grid_card',
//     view_display_id: 'block_product',
//     view_args: nid,
//     view_dom_id: 'ajax-demo',
//     page: 1
//   };
//   console.log('view_info', view_info);
//
//   // Details of the ajax action.
//   var ajax_settings = {
//     submit: view_info,
//     url: '/views/ajax',
//     element: column,
//     event: 'click'
//   };
//   console.log('ajax_settings', ajax_settings);
//
//   // "grid_card"
//   // view_display_id	"block_product"
//   // view_args	""
//   // view_path	"/node/2"
//   // view_base_path	""
//   // view_dom_id	"9afbe1fda7da2662f040bdcf45c8de85e1e3fa6bc679025aa6f8005434bb23d2"
//   // pager_element	"0"
//   // page	"1"
//   // _drupal_ajax	"1"
//
//   jQuery.ajax({
//     type: 'POST',
//     url: '/views/ajax',
//     data: view_info,
//     dataType: 'json',
//     async: true,
//     complete: function(data) {
//       console.log(data);
//       // $(".contact-loader").hide();
//       // $('.contactblock').empty().html(data.responseText);
//     }
//   });
//
//   // Drupal.ajax(ajax_settings);
// }


// (function ($) {
//   Drupal.behaviors.ajaxViewDemo = {
//     attach: function (context, settings) {
//       // Attach ajax action click event of each view column.
//       $('.view-articles .views-col').once('attach-links').each(this.attachLink);
//     },
//
//     attachLink: function (idx, column) {
//
//       // Dig out the node id from the header link.
//       var link = $(column).find('header a');
//       var href = $(link).attr('href');
//       var matches = /node\/(\d*)/.exec(href);
//       var nid = matches[1];
//
//       // Everything we need to specify about the view.
//       var view_info = {
//         view_name: 'articles',
//         view_display_id: 'embed_1',
//         view_args: nid,
//         view_dom_id: 'ajax-demo'
//       };
//
//       // Details of the ajax action.
//       var ajax_settings = {
//         submit: view_info,
//         url: '/views/ajax',
//         element: column,
//         event: 'click'
//       };
//
//       Drupal.ajax(ajax_settings);
//     }
//   };
// })(jQuery);
