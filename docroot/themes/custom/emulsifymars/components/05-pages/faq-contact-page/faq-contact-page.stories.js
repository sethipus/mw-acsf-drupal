import React from 'react';

import faqContactTwig from './faq-contact-page.twig';
import faqContactData from './faq-contact-page.yml';

//Import for footer and header
import footerSocial from '../../02-molecules/menus/social/social-menu.yml';
import footerMenu from '../../02-molecules/menus/footer/footer-menu.yml';
import secondaryMenuData from '../../02-molecules/menus/inline/header-inline-menu/header-inline-menu.yml';
import inlineSearchData from '../../02-molecules/search/inline-search/inline-search.yml';
import mainMenuData from '../../02-molecules/menus/main-menu/main-menu.yml';
import legalLinksData from '../../02-molecules/menus/legal-links/legal-links-menu.yml';
import siteHeaderData from '../../03-organisms/site/site-header/site-header.yml';
import siteFooterData from '../../03-organisms/site/site-footer/site-footer.yml';
import '../../02-molecules/menus/main-menu/main-menu';
import '../../02-molecules/dropdown/dropdown';

//Imports for Parent Page Header Zone
import parentPageHeaderData from '../../02-molecules/parent-page-header/parent-page-header.yml';

//Import for faq list
import faqListData from '../../02-molecules/faq-list/faq-list.yml';

//Import for Contact Module
import contactHelpBannerData from '../../02-molecules/contact-module/contact-module.yml';

//Import for feedback module
import feedbackData from '../../02-molecules/feedback-module/feedback.yml';

import { useEffect } from '@storybook/client-api';

export default {
  title: 'Pages/[PT 09] FAQ & Contact',
  argTypes: {
    theme: {
      name: 'Theme',
      description: 'Theme of the page.',
      table: {
        category: 'Page Layout',
      },
      control: {
        type: 'select',
        options: ['twix', 'dove', 'mars', 'galaxy'],
      },
    },

    //Header
    headerMenu: {
      name: 'Menu List',
      description: 'Menu options in the header',
      table: {
        category: 'Header Component',
      },
      control: {
        type: 'object',
      },
    },
    headerAlertBanner: {
      name: 'Alert Banner',
      description: 'Alert Banner for the header',
      table: {
        category: 'Header Component',
      },
      control: {
        type: 'text',
      },
    },

    //Footer
    footerMenuItems: {
      name: 'Menu Items',
      descritpion:
        'Menu Items for the footer section. <b> Contact & Help, About, Where to Buy - Maintains Max CC: 25 </b>',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'object',
      },
    },
    marketingMessage: {
      name: 'Marketing & Copyright Message',
      description: ' Message for the marketing and copyright',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'object',
      },
    },
    socialMenuItems: {
      name: 'Social Follow',
      description: 'Content for the social menu icons',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'object',
      },
    },
    legaMenuItems: {
      name: 'Legal Menu',
      description:
        'Legal menu content.<b>9 links, however editors can add up to 3 more (a fourth row) </b>',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'object',
      },
    },
    copyrighttext: {
      name: 'Copyright Text',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'text',
      },
    },
    corporateText: {
      name: 'Corporate Text',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'text',
      },
    },

    //Parent page header controls
    Eyebrow: {
      name: 'Eyebrow text',
      description: 'Eyebrow of the page.<b> Maximum character limit is 30.</b>',
      defaultValue: { summary: 'LOREM' },
      table: {
        category: 'Parent Page Header Component',
      },
      control: { type: 'text' },
    },
    Title: {
      name: 'Title',
      description: 'Title of the page.<b> Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Title' },
      table: {
        category: 'Parent Page Header Component',
      },
      control: { type: 'text' },
    },
    Description: {
      name: 'Description text',
      description:
        'Description of the page. <b>Maximum character limit is 255.</b>',
      defaultValue: { summary: 'lorem ipsum..' },
      table: {
        category: 'Parent Page Header Component',
      },
      control: { type: 'text' },
    },
    BackgroundTheme: {
      name: 'Background Theme',
      description: 'Background - Color/Image/Video',
      table: {
        category: 'Parent Page Header Component',
      },
      control: { type: 'radio', options: ['color', 'image', 'video'] },
    },
    parent_page_media_entities: {
      name: 'Background Image/Video',
      description:
        'Background Image and Video URL. Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      defaultValue: {
        summary:
          'VIDEO - https://lhcdn.mars.com/adaptivemedia/rendition/id_f76bfd3c55ff05adc19f33a69e3bc665045e6a4f/name_f76bfd3c55ff05adc19f33a69e3bc665045e6a4f.jpg ,IMAGE - http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      table: {
        category: 'Parent Page Header Component',
      },
      control: { type: 'object' },
    },

    //Faq List
    faq_title: {
      name: 'FAQ Heading',
      description: '<b> Maximum character limit is 55 </b>',
      table: {
        category: 'FAQ Component',
      },
      control: {
        type: 'text',
      },
    },
    faq_searchLinks: {
      name: 'Common Topic Filters',
      description:
        'Editors have the ability to create, add, reorder and delete common topic filters. Max filters, <b>10. Max CC for each filter</b>',
      table: {
        category: 'FAQ Component',
      },
      control: {
        type: 'object',
      },
    },
    faq_faqLists: {
      name: 'QA Blurbs',
      description: `Editors author can add or remove the Q/A.`,
      table: {
        category: 'FAQ Component',
      },
      control: {
        type: 'object',
      },
    },

    //Contact & help Banner
    contact_Title: {
      name: 'Title',
      description: 'Title for the contact module',
      defaultValue: {
        summary: 'Lorem ipsum..',
      },
      table: {
        category: 'Contact & Help Banner',
      },
      control: {
        type: 'text',
      },
    },
    contact_Description: {
      name: 'Description',
      description: 'Description for the contact module',
      defaultValue: {
        summary: 'Lorem ipsum..',
      },
      table: {
        category: 'Contact & Help Banner',
      },
      control: {
        type: 'text',
      },
    },
    contact_callCTA: {
      name: 'Call CTA',
      description: 'Call number for the contact module',
      defaultValue: {
        summary: 'Lorem ipsum..',
      },
      table: {
        category: 'Contact & Help Banner',
      },
      control: {
        type: 'text',
      },
    },
    contact_emailCTA: {
      name: 'Email CTA',
      description: 'Email for the contact module',
      defaultValue: {
        summary: 'Lorem ipsum..',
      },
      table: {
        category: 'Contact & Help Banner',
      },
      control: {
        type: 'text',
      },
    },
    contact_social_heading:{
      name:'Social Heading',
      description:'Heading for the social links.',
      table:{
        category:'Contact & Help Banner'
      },
      control:{
        type:'text'
      }
    },

    //Feedback Module
    brandShape: {
      name: 'Brand Shape',
      description: 'SVG for the respective brand can be added.',
      table: {
        category: 'Feedback Module Component',
      },
      control: {
        type: 'text',
      },
    },
    standardHeading: {
      name: 'Standard Heading',
      description:
        'Only applicable to ✅Standard ❌Positive Feedback ❌Negative Feedback. <b>Maximum CC is 25.</b>',
      table: {
        category: 'Feedback Module Component',
      },
      control: {
        type: 'text',
      },
    },
    standardChoices: {
      name: 'Choose Option CTA',
      description: 'Options can be changed or removed as per the requirement',
      table: {
        category: 'Feedback Module Component',
      },
      control: {
        type: 'object',
      },
    },
    description:{
      name:'Description',
      description:'Text content for the feedback module',
      table:{
        category: 'Feedback Module Component',
      },
      control:{
        type:'text'
      }
    },
  },
};

export const recipeDetailPageLayout = ({
  theme,

  //header
  headerMenu,
  headerAlertBanner,
  footerMenuItems,
  marketingMessage,
  socialMenuItems,
  legaMenuItems,
  copyrighttext,
  corporateText,

  //parent page header
  Eyebrow,
  Title,
  Description,
  BackgroundTheme,
  parent_page_media_entities,

  //Faq
  faq_title,
  faq_searchLinks,
  faq_faqLists,

  //Contact Help Banner
  contact_Title,
  contact_Description,
  contact_callCTA,
  contact_emailCTA,
  contact_social_heading,

  //Feedback Module
  brandShape,
  description,
  standardHeading,
  standardChoices,


}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: faqContactTwig({
          ...footerSocial,
          ...footerMenu,
          ...secondaryMenuData,
          ...inlineSearchData,
          ...mainMenuData,
          ...legalLinksData,
          ...siteHeaderData,
          ...siteFooterData,
          ...faqContactData,

          ...parentPageHeaderData,
          ...faqListData,
          ...contactHelpBannerData,
          ...feedbackData,

          theme_styles: theme,

          menu_items: headerMenu,
          alert_banner: headerAlertBanner,

          footer_menu_items: footerMenuItems,
          marketing_text: marketingMessage,
          social_menu_items: socialMenuItems,
          legal_links_menu_items: legaMenuItems,
          copyright_text: copyrighttext,
          corporate_tout_text: corporateText,

          //parent page header
          pp_header_eyebrow_text: Eyebrow,
          pp_header_heading: Title,
          pp_header_paragraph_content: Description,
          parent_page_media_type: BackgroundTheme,
          parent_page_media_entities: parent_page_media_entities,

          //Faq List
          title: faq_title,
          facetLinks: faq_searchLinks,
          faq_items: faq_faqLists,


          //Contact Help Banner
          contact_module_heading: contact_Title,
          contact_module_paragraph_content: contact_Description,
          contact_module_contact_phone: contact_callCTA,
          contact_module_contact_email_text: contact_emailCTA,
          contact_module_social_heading:contact_social_heading,


          //Feedback Module
          brand_shape: brandShape,
          feedback_paragraph_content:description,
          feedback_heading: standardHeading,
          choices: standardChoices,
        }),
      }}
    />
  );
};
recipeDetailPageLayout.args = {
  theme: faqContactData.theme_styles,

  //For Header
  headerMenu: mainMenuData.menu_items,
  headerAlertBanner: siteHeaderData.alert_banner,

  //For Footer
  footerMenuItems: footerMenu.footer_menu_items,
  marketingMessage: siteFooterData.marketing_text,
  socialMenuItems: footerSocial.social_menu_items,
  legaMenuItems: legalLinksData.legal_links_menu_items,
  copyrighttext: siteFooterData.copyright_text,
  corporateText: siteFooterData.corporate_tout_text,

  // For Parent page header
  Eyebrow: parentPageHeaderData.pp_header_eyebrow_text,
  Title: parentPageHeaderData.pp_header_heading,
  Description: parentPageHeaderData.pp_header_paragraph_content,
  BackgroundTheme: parentPageHeaderData.parent_page_media_type,
  parent_page_media_entities: parentPageHeaderData.parent_page_media_entities,

  //For Faq List
  faq_title: faqListData.title,
  faq_searchLinks: faqListData.facetLinks,
  faq_faqLists: faqListData.faq_items,

  //Contact Help Banner
  contact_Title: contactHelpBannerData.contact_module_heading,
  contact_Description: contactHelpBannerData.contact_module_paragraph_content,
  contact_callCTA: contactHelpBannerData.contact_module_contact_phone,
  contact_emailCTA: contactHelpBannerData.contact_module_contact_email_text,
  contact_social_heading:contactModuleData.contact_module_social_heading,


  /* Feedback Module */
  brandShape: feedbackData.brand_shape,
  standardHeading: feedbackData.feedback_heading,
  description: feedbackData.feedback_paragraph_content,
  standardChoices: feedbackData.choices,
};
