import React from 'react';
import ReactDOMServer from 'react-dom/server';
import { useEffect } from '@storybook/client-api';

import hubTwig from './hub-page.twig';
import hubData from './hub-page.yml';

//Imports for header and footer
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

//Imports for Feature Content zone
import contentFeatureData from '../../02-molecules/content-feature/content-feature.yml';
import productFeatureData from '../../02-molecules/product-feature/product-feature.yml';
import recipeFeatureData from '../../02-molecules/recipe-feature-module/recipe-feature-module.yml';
import homeProductContentData from '../../02-molecules/product-content-pair-up/product-content-pair-up.yml';
import productCard from '../../02-molecules/card/product-card/product-card.twig';
import productCardData from '../../02-molecules/card/product-card/product-card.yml';
import productRatingData from '../../02-molecules/card/product-card/product-rating.yml';

//Imports for Driver content zone
import flexibleFramerData from '../../02-molecules/flexible-framer/flexible-framer.yml';
import flexibleDriverData from '../../02-molecules/flexible-driver/flexible-driver.yml';

// Import from Community Zone 
import feedbackData from '../../02-molecules/feedback-module/feedback.yml';
import homePollData from '../../02-molecules/polls/poll.yml';
import defaultLink from './../../01-atoms/links/defaultLink/defaultLink.twig'

export default {
  title: 'Pages/[PT 04] Hub Pages',
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

    feature_content_option: {
      name: 'Feature Content Layout',
      table: {
        category: 'Feature Content Layout',
      },
      control: {
        type: 'select',
        options: [
          'Product Feature Module',
          'Content Feature Module',
          'Recipe Feature Module',
          'Product Content Pair-Up',
        ],
      },
    },

    //Product Feature
    product_feature_eyebrow: {
      name: 'Eyebrow',
      defaultValue: { summary: 'Product ABC ' },
      table: {
        category: 'Product Feature',
      },
      description:
        'Eyebrow text for the product feature. <b>Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    product_feature_title: {
      name: 'Title text',
      table: {
        category: 'Product Feature',
      },
      defaultValue: { summary: 'ABC Chocolate' },
      description:
        'Title for the product feature.<b> Maximum character limit is 55.</b>',
      control: { type: 'text' },
    },
    product_feature_Background: {
      name: 'Background Color',
      table: {
        category: 'Product Feature',
      },
      defaultValue: { summary: '#fff' },
      description: 'Background color HEX value for the product feature',
      control: { type: 'color' },
    },
    product_feature_ProductImage: {
      name: 'Image Assets',
      table: {
        category: 'Product Feature',
      },
      description:
        'Product image for the product.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      control: { type: 'object' },
    },
    product_feature_ExploreCTA: {
      name: 'Button CTA',
      table: {
        category: 'Product Feature',
      },
      defaultValue: { summary: 'Explore' },
      description:
        'Button CTA text for the product feature. <b>Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },

    //Content Feature
    content_feature_Eyebrow: {
      name: 'Eyebrow',
      description:
        'Eyebrow of the content.<b> Maximum character limit is 15. </b>',
      defaultValue: { summary: 'INITIATIVE' },
      table: {
        category: 'Content Feature ',
      },
      control: { type: 'text' },
    },
    content_feature_Title: {
      name: 'Title',
      description:
        'Title of the content. <b>Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Title..' },
      table: {
        category: 'Content Feature ',
      },
      control: { type: 'text' },
    },
    content_feature_background_images: {
      name: 'Background Image',
      description:
        'Background Image of the content.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      table: {
        category: 'Content Feature ',
      },
      control: { type: 'object' },
    },
    content_feature_Description: {
      name: 'Feature Description',
      description:
        'Description of the content. <b>Maximum character limit is 300.</b>',
      defaultValue: { summary: 'lorem pisum..' },
      table: {
        category: 'Content Feature ',
      },
      control: { type: 'text' },
    },
    content_feature_ExploreCTA: {
      name: 'Button CTA',
      description: 'Button text. <b>Maximum character limit is 15.</b>',
      defaultValue: { summary: 'Submit' },
      table: {
        category: 'Content Feature ',
      },
      control: { type: 'text' },
    },

    //Recipe Feature
    recipe_feature_Eyebrow: {
      name: 'Eyebrow',
      defaultValue: { summary: 'Recipe' },
      table: {
        category: 'Recipe Feature',
      },
      description:
        'Eyebrow text for the recipe feature.<b> Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    recipe_feature_RecipeTitle: {
      name: 'Recipe title',
      defaultValue: { summary: 'Product ABC ' },
      table: {
        category: 'Recipe Feature',
      },
      description:
        'Recipe title for the recipe feature.<b> Maximum character limit is 60.</b>',
      control: { type: 'text' },
    },
    recipe_feature_cta: {
      name: 'Button CTA',
      defaultValue: { summary: 'SEE DETAILS ' },
      table: {
        category: 'Recipe Feature',
      },
      description:
        'Button CTA for the recipe feature button.<b> Maximum character limit is 15.</b>',
      control: { type: 'object' },
    },
    recipe_feature_recipe_media: {
      name: 'Recipe Image',
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      table: {
        category: 'Recipe Feature',
      },
      description:
        'Recipe image for the recipe.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      control: { type: 'object' },
    },

    //Product Content Pair up
    ContentTitle: {
      name: 'Title text',
      description:
        'Title of the layout.<b> Maximum character limit is 100.</b>',
      table: {
        category: 'Product Content Pair Up Component',
      },
      defaultValue: { summary: 'Lorem Ipsum..' },
      control: { type: 'text' },
    },
    ContentEyebrowText: {
      name: 'Eyebrow',
      description:
        'Eyebrow of the layout. <b>Maximum character limit is 100.</b>',
      table: {
        category: 'Product Content Pair Up Component',
      },
      defaultValue: { summary: 'Lorem' },
      control: { type: 'text' },
    },
    ContentBackground: {
      name: 'Background Image',
      description:
        'Background Image of the layout.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Product Content Pair Up Component',
      },
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      control: { type: 'object' },
    },
    content_card_eyebrow: {
      name: 'Card Eyebrow',
      table: {
        category: 'Product Content Pair Up Component',
      },
      control: {
        type: 'text',
      },
    },
    content_card_item: {
      name: 'Card Contents',
      table: {
        category: 'Product Content Pair Up Component',
      },
      control: {
        type: 'object',
      },
    },

    driver_content_option: {
      name: 'Driver Content Layout',
      table: {
        category: 'Driver Content Layout',
      },
      control: {
        type: 'select',
        options: ['Flexible Driver', 'Flexible Framer'],
      },
    },

    //Flexible Framer
    framer_Title: {
      description:
        'Change the title of the content.<b> Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Lorem' },
      table: { category: 'Flexible Framer' },
      control: { type: 'text' },
    },
    framer_items: {
      name: 'Stories',
      description:
        'Change the stories of the content.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b> Maximum character for the Item title and Item description and button CTA is 60, 255 and 15 respectively.</b>',
      table: { category: 'Flexible Framer' },
      control: { type: 'object' },
    },

    //flexible driver 
    driver_Title: {
      name: 'Title',
      description:
        'Title of the content.<b> Maximum character limit is 65.</b>',
      defaultValue: { summary: 'title' },
      table: {
        category: 'Flexible Driver',
      },
      control: { type: 'text' },
    },
    driver_Description: {
      name: 'Content description',
      description:
        'Description of the content. <b>Maximum character limit is 160.</b>',
      defaultValue: { summary: 'lorem pisum..' },
      table: {
        category: 'Flexible Driver',
      },
      control: { type: 'text' },
    },
    driver_ButtonCTA: {
      name: 'Button CTA',
      description:
        'Button CTA of the content.<b> Maximum character limit is 15.</b>',
      defaultValue: { summary: 'Explore' },
      table: {
        category: 'Flexible Driver',
      },
      control: { type: 'text' },
    },
    driver_LeftImage: {
      name: 'Left Image',
      defaultValue: { summary: 'https://picsum.photos/200' },
      description:
        'Left side image of the content( Web Images are only applicable). Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Flexible Driver',
      },
      control: { type: 'object' },
    },
    driver_RightImage: {
      name: 'Right Image',
      defaultValue: { summary: 'https://picsum.photos/200' },
      description:
        'Right side image of the content( Web Images are only applicable). Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Flexible Driver',
      },
      control: { type: 'object' },
    },

    community_option:{
      name:'Community Layout',
      table:{
        category:'Community Block'
      },
      control:{
        type:'select',
        options:[
          'Feedback Module',
          'Poll'
        ]
      }
    },

     //Polls 
     PollImage: {
      name: 'Image Asset',
      description: 'Changing the image for the layout',
      table: {
        category: 'Polls',
      },
      control: { type: 'object' },
    },
    PollHeading: {
      name: 'Heading text',
      description:
        'Changing the Heading for the layout.<b> Maximum character limti is 55.</b>',
      defaultValue: { summary: 'Lorem..' },
      table: {
        category: 'Polls',
      },
      control: { type: 'text' },
    },
    PollContent: {
      name: 'Content text',
      description:
        'Changing the Content for the layout.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul> <b>Maximum character limti is 255.</b>',
      defaultValue: { summary: 'Lorem Ipsum..' },
      table: {
        category: 'Polls',
      },
      control: { type: 'text' },
    },
    PollOptions: {
      name: 'Poll options',
      description: 'Change the poll options in the layout',
      defaultValue: { summary: '3 choices' },
      table: {
        category: 'Polls',
      },
      control: { type: 'radio', options: ['3', '4', '5'] },
    },

    //Feedback Module
    feedback_standardHeading: {
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
    feedback_standardChoices: {
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
  },
};

export const hubPageLayout = ({
  theme,
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

  feature_content_option,

  //product feature
  product_feature_eyebrow,
  product_feature_title,
  product_feature_Background,
  product_feature_ProductImage,
  product_feature_ExploreCTA,

  //Content feature
  content_feature_Eyebrow,
  content_feature_Title,
  content_feature_background_images,
  content_feature_Description,
  content_feature_ExploreCTA,

  //Recipe Feature
  recipe_feature_Eyebrow,
  recipe_feature_RecipeTitle,
  recipe_feature_cta,
  recipe_feature_recipe_media,

  //product content pair up
  ContentTitle,
  ContentEyebrowText,
  ContentBackground,
  ProductEyebrow,
  content_card_eyebrow,
  content_card_item,

  driver_content_option,

  //flexible framer
  framer_Title,
  framer_items,

  //flexible driver
  driver_Title,
  driver_Description,
  driver_ButtonCTA,
  driver_LeftImage,
  driver_RightImage,

  community_option,

  //polls
  PollImage,
  PollHeading,
  PollContent,
  PollOptions,

  //Feedback 
  brandShape,
  description,
  feedback_standardHeading,
  feedback_standardChoices

}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  homePollData.vote_button = defaultLink({default_link_content: 'Submit'});
  homeProductContentData.supporting_card_content = [
    ReactDOMServer.renderToStaticMarkup(
      <div
        dangerouslySetInnerHTML={{
          __html: productCard({
            ...productCardData,
            ...productRatingData,
            card__eyebrow: content_card_eyebrow,
            item: content_card_item,
          }),
        }}
      />,
    ),
  ];
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: hubTwig({
          ...footerSocial,
          ...footerMenu,
          ...secondaryMenuData,
          ...inlineSearchData,
          ...mainMenuData,
          ...legalLinksData,
          ...siteHeaderData,
          ...siteFooterData,
          ...hubData,

          ...parentPageHeaderData,

          ...contentFeatureData,
          ...productFeatureData,
          ...recipeFeatureData,
          ...homeProductContentData,
          ...productCardData,

          ...flexibleFramerData,
          ...flexibleDriverData,

          ...feedbackData,
          ...homePollData,

          theme_styles: theme,

          menu_items: headerMenu,
          alert_banner: headerAlertBanner,

          footer_menu_items: footerMenuItems,
          marketing_text: marketingMessage,
          social_menu_items: socialMenuItems,
          legal_links_menu_items: legaMenuItems,
          copyright_text: copyrighttext,
          corporate_tout_text: corporateText,

          feature_content_option: feature_content_option,

          driver_content_option: driver_content_option,

          //parent page header
          pp_header_eyebrow_text: Eyebrow,
          pp_header_heading: Title,
          pp_header_paragraph_content: Description,
          parent_page_media_type: BackgroundTheme,
          parent_page_media_entities: parent_page_media_entities,

          //Product feature
          eyebrow_text: product_feature_eyebrow,
          storybook_product_feature_heading: product_feature_title,
          storybook_product_feature_background_color: product_feature_Background,
          image_src: product_feature_ProductImage,
          default_link_content: product_feature_ExploreCTA,

          //Content Feature
          storybook_content_feature_eyebrow_text: content_feature_Eyebrow,
          storybook_content_feature_heading: content_feature_Title,
          paragraph_content: content_feature_Description,
          storybook_content_feature_default_link_content: content_feature_ExploreCTA,
          background_images: content_feature_background_images,

          //Recipe Feature
          eyebrow: recipe_feature_Eyebrow,
          title: recipe_feature_RecipeTitle,
          cta: recipe_feature_cta,
          recipe_media: recipe_feature_recipe_media,

          //Product Content Pair up
          lead_card_title: ContentTitle,
          lead_card_eyebrow: ContentEyebrowText,
          background: ContentBackground,
          card__eyebrow: content_card_eyebrow,
          item: content_card_item,

          //Flexible Framer
          grid_label: framer_Title,
          flexible_framer_items: framer_items,

          //flexible Driver
          flexible_driver_heading: driver_Title,
          flexible_driver_text: driver_Description,
          flexible_driver_button_text: driver_ButtonCTA,
          storybook_flexible_driver_asset_1: driver_LeftImage,
          storybook_flexible_driver_asset_2: driver_RightImage,

          community_option:community_option,

          //polls
          polling_png_asset: PollImage,
          polling_heading: PollHeading,
          polling_paragraph_content: PollContent,
          storybook_poll_options: PollOptions,

          //Feedback
          brand_shape: brandShape,
          feedback_paragraph_content:description,
          feedback_heading: feedback_standardHeading,
          choices: feedback_standardChoices,
        }),
      }}
    />
  );
};
hubPageLayout.args = {
  theme: hubData.theme_styles,
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

  feature_content_option: hubData.feature_content_option,

  //Product Feature
  product_feature_eyebrow: productFeatureData.eyebrow_text,
  product_feature_title: productFeatureData.storybook_product_feature_heading,
  product_feature_Background: productFeatureData.storybook_product_feature_background_color,
  product_feature_ProductImage: productFeatureData.image_src,
  product_feature_ExploreCTA: productFeatureData.default_link_content,

  //Content Feature
  content_feature_Eyebrow: contentFeatureData.storybook_content_feature_eyebrow_text,
  content_feature_Title: contentFeatureData.storybook_content_feature_heading,
  content_feature_Description: contentFeatureData.paragraph_content,
  content_feature_background_images: contentFeatureData.background_images,
  content_feature_ExploreCTA: contentFeatureData.storybook_content_feature_default_link_content,

  //Recipe Feature
  recipe_feature_Eyebrow: recipeFeatureData.eyebrow,
  recipe_feature_RecipeTitle: recipeFeatureData.title,
  recipe_feature_cta: recipeFeatureData.cta,
  recipe_feature_recipe_media: recipeFeatureData.recipe_media,

  /* Product Content pair up component */
  ContentTitle: homeProductContentData.lead_card_title,
  ContentEyebrowText: homeProductContentData.lead_card_eyebrow,
  ContentBackground: homeProductContentData.background,
  content_card_eyebrow: 'MADE WITH',
  content_card_item: productCardData.item,

  driver_content_option: hubData.driver_content_option,

  //Flexible Framer
  framer_Title: flexibleFramerData.grid_label,
  framer_items: flexibleFramerData.flexible_framer_items,

  //Flexible Driver
  driver_Title: flexibleDriverData.flexible_driver_heading,
  driver_Description: flexibleDriverData.flexible_driver_text,
  driver_ButtonCTA: flexibleDriverData.flexible_driver_button_text,
  driver_LeftImage: flexibleDriverData.storybook_flexible_driver_asset_1,
  driver_RightImage: flexibleDriverData.storybook_flexible_driver_asset_2,


  community_option:hubData.community_option,

  /* Poll component */ 
  PollImage: homePollData.polling_png_asset,
  PollHeading: homePollData.polling_heading,
  PollContent: homePollData.polling_paragraph_content,
  PollOptions: homePollData.storybook_poll_options,

  //feedback
  brandShape: feedbackData.brand_shape,
  description: feedbackData.feedback_paragraph_content,
  feedback_standardHeading: feedbackData.feedback_heading,
  feedback_standardChoices: feedbackData.choices,
};
