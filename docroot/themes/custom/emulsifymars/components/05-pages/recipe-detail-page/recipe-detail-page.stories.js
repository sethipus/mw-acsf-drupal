import React from 'react';
import { useEffect } from '@storybook/client-api';

import recipeDetailTwig from './recipe-detail-page.twig';
import recipeDetailData from './recipe-detail-page.yml';

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

//Import for Recipe Hero Module
import recipeHeroModuleVideoData from '../../02-molecules/recipe-hero-module/recipe-hero-module-video.yml';

//Import for Recipe Body Module
import recipeBodyData from '../../03-organisms/recipe-body/recipe-body.yml';

//Import for List module
import articleListData from '../../02-molecules/article-media/list/article-list.yml';

//Imports for Storytelling zone
import fullWidthMediaData from '../../02-molecules/article-media/full-width/full-width-media.yml';
import flexibleFramerData from '../../02-molecules/flexible-framer/flexible-framer.yml';
import flexibleDriverData from '../../02-molecules/flexible-driver/flexible-driver.yml';
import contactHelpBannerData from '../../02-molecules/contact-module/contact-module.yml';
import WYSIWYGData from '../../02-molecules/article-media/wysiwyg/article-wysiwyg.yml';
import storyHighlight from '../../02-molecules/story-highlight/story_highlight.yml';
import homePollData from '../../02-molecules/polls/poll.yml';
import defaultLink from './../../01-atoms/links/defaultLink/defaultLink.twig';
import iframeData from '../../01-atoms/iframe/iframe.yml';

//Imports for User Feedback zone
import feedbackData from '../../02-molecules/feedback-module/feedback.yml';

export default {
  title: 'Pages/[PT 07] Recipe Detail',
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

    //Recipe Hero Module
    recipe_hero_LabelContent: {
      name: 'Label text',
      table: {
        category: 'Recipe Hero Module',
      },
      defaultValue: { summary: 'Lorem Ipsum..' },
      description:
        'Change the label text of the recipe.<b>Maximum character limit is 60.</b>',
      control: 'text',
    },
    recipe_hero_backgroundColorEnable: {
      name: 'Enable Background Color',
      table: {
        category: 'Recipe Hero Module',
      },
      description: 'Enable indicator for background color.',
      control: { type: 'boolean' },
    },
    recipe_hero_backgroundColor: {
      name: 'Background Color',
      table: {
        category: 'Recipe Hero Module',
      },
      description: 'Change the background color of the recipe',
      control: { type: 'color' },
    },
    recipe_hero_RecipeDescription: {
      name: 'Recipe Description text',
      table: {
        category: 'Recipe Hero Module',
      },
      defaultValue: { summary: 'Lorem Ipsum...' },
      description: 'Change the description of the recipe',
      control: 'text',
    },
    recipe_hero_CookingTime: {
      name: 'Cooking time',
      table: {
        category: 'Recipe Hero Module',
      },
      defaultValue: { summary: '23mins' },
      description: 'Change the cooking time of the recipe.',
      control: 'text',
    },
    recipe_hero_NumberOfIngridents: {
      name: 'Ingridents required',
      table: {
        category: 'Recipe Hero Module',
      },
      defaultValue: { summary: '12' },
      description: 'Number of ingridents required for the recipe.',
      control: 'text',
    },
    recipe_hero_NumberOfServings: {
      name: 'No. of Servings',
      table: {
        category: 'Recipe Hero Module',
      },
      defaultValue: { summary: '10' },
      description: 'Number of serving plates possible for the recipe.',
      control: 'text',
    },
    recipe_hero_video: {
      name: 'Video Enable Indicator',
      table: {
        category: 'Recipe Hero Module',
      },
      description: 'Enable the video instead of image',
      control: {
        type: 'boolean',
      },
    },
    recipe_hero_images: {
      name: 'Background Image',
      table: {
        category: 'Recipe Hero Module',
      },
      description:
        'Change the background Image of the recipe module.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      control: { type: 'object' },
    },

    //Recipe Body Module
    recipe_body_ingridents:{
      name:'Ingrident list',
      description:'Ingrident list for the desired recipe.',
      table:{
        category:'Recipe Body Module'
      },
      control:{
        type:'object'
      }
    },

    //List 
    list_title: {
      name: 'Title',
      description: 'Title',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'List Module' },
      control: { type: 'text' },
    },
    list_Content: {
      name: 'Content',
      description: 'Maximum number of point that can be added is <b> 9 </b> . List image should be of ratio <b> 16X9 </b>',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'List Module' },
      control: { type: 'object' },
    },

    StoryTellingOption: {
      name: 'Storytelling Block',
      table: {
        category: 'Storytelling Layout',
      },
      control: {
        type: 'select',
        options: [
          'Full Width Media',
          'Flexible Framer',
          'Flexible Driver',
          'Contact & Help Banner',
          'WYSIWYG',
          'Story Highlight',
          'Polls',
          'Iframe',
        ],
      },
    },

    //Full Width Controls
    heading: {
      name: ' Title',
      description: 'Title for the media. <b> Maximum character limit is 55</b>',
      table: {
        category: 'Full Width Media Component',
      },
      control: {
        type: 'text',
      },
    },
    media: {
      name: ' Media',
      description: `<ul><li> For video media, the video flag needs to be set as ,<i>true</i>
         with the src flag as the source path of the video.</li> <li>For parallax image media,
         the parallax_image flag needs to be set as ,<i>true</i>
         with the src flag as the image link.(When parallax image is set as false the heading
         will be visible)</li><li>For image media, the image flag needs to be set as ,<i>true</i>
         with the src flag as the source path of the image.</li></ul>`,
      table: {
        category: 'Full Width Media Component',
      },
      control: {
        type: 'object',
      },
    },
    content: {
      name: ' Content',
      description: `<b>Content description maximum character limit is 300</b>`,
      table: {
        category: 'Full Width Media Component',
      },
      control: {
        type: 'text',
      },
    },

    //Flexible Framer
    FramerTitle: {
      description:
        'Change the title of the content.<b> Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Lorem' },
      table: { category: 'Flexible Framer' },
      control: { type: 'text' },
    },
    Frameritems: {
      name: 'Stories',
      description:
        'Change the stories of the content.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b> Maximum character for the Item title and Item description and button CTA is 60, 255 and 15 respectively.</b>',
      table: { category: 'Flexible Framer' },
      control: { type: 'object' },
    },

    //Flexible Driver controls
    DriverTitle: {
      name: 'Title',
      description:
        'Title of the content.<b> Maximum character limit is 65.</b>',
      defaultValue: { summary: 'title' },
      table: {
        category: 'Flexible Driver Component',
      },
      control: { type: 'text' },
    },
    DriverDescription: {
      name: 'Content description',
      description:
        'Description of the content. <b>Maximum character limit is 160.</b>',
      defaultValue: { summary: 'lorem pisum..' },
      table: {
        category: 'Flexible Driver Component',
      },
      control: { type: 'text' },
    },
    DriverButtonCTA: {
      name: 'Button CTA',
      description:
        'Button CTA of the content.<b> Maximum character limit is 15.</b>',
      defaultValue: { summary: 'Explore' },
      table: {
        category: 'Flexible Driver Component',
      },
      control: { type: 'text' },
    },
    DriverLeftImage: {
      name: 'Left Image',
      defaultValue: { summary: 'https://picsum.photos/200' },
      description:
        'Left side image of the content( Web Images are only applicable). Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Flexible Driver Component',
      },
      control: { type: 'object' },
    },
    DriverRightImage: {
      name: 'Right Image',
      defaultValue: { summary: 'https://picsum.photos/200' },
      description:
        'Right side image of the content( Web Images are only applicable). Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Flexible Driver Component',
      },
      control: { type: 'object' },
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
    //WYSIWYG
    WYSIWYG_Header: {
      name: 'Header',
      description: 'Header text',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'WYSIWYG Component' },
      control: { type: 'text' },
    },
    WYSIWYG_body: {
      name: 'Body Text',
      description: 'Header text',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'WYSIWYG Component' },
      control: { type: 'text' },
    },

    //StoryHighlight controls
    StoryHighlightTitle: {
      name: 'Title text',
      description: 'Title of the story. <b>Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Dog Foods' },
      table: { category: 'Story Highlight' },
      control: { type: 'text' },
    },
    StoryHighlightParagraphContent: {
      name: 'Paragraph text',
      description:
        'Paragraph of the story.<b> Maximum character limit is 255.</b>',
      defaultValue: { summary: 'lorem ipsum...' },
      table: { category: 'Story Highlight' },
      control: { type: 'text' },
    },
    StoryHighlightButtonCTA: {
      name: 'Button CTA',
      description:
        'Button CTA of the button.<b> Maximum character limit is 15.</b>',
      defaultValue: { summary: 'EXPLORE' },
      table: { category: 'Story Highlight' },
      control: { type: 'text' },
    },
    StoryHighlightImageAsset1: {
      name:'Image asset 1',
      description: 'Change the first image of the story. Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b>Story item title maximum character limit is 300.</b> ',
      table: { category: 'Story Highlight' },
      control: { type: 'object' },
    },
    StoryHighlightImageAsset2: {
      name:'Image asset 2',
      description: 'Change the second image of the story. Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b>Story item title maximum character limit is 300.</b> ',
      table: { category: 'Story Highlight' },
      control: { type: 'object' },
    },
    StoryHighlightImageAsset3: {
      name:'Image asset 3',
      description: 'Change the third image of the story. Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b>Story item title maximum character limit is 300.</b> ',
      table: { category: 'Story Highlight' },
      control: { type: 'object' },
    },
    StoryHighlightitems: {
      name: 'Stories List',
      description:
        'Change layout of story1.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b>Story item title maximum character limit is 300.</b> ',
      table: { category: 'Story Highlight' },
      control: { type: 'object' },
    },

    //Iframe
    iframe_description: {
      name: 'Source Link',
      description: 'Link for the iframe',
      table: {
        category: 'Iframe module',
      },
      control: {
        type: 'text',
      },
    },

    //polls control
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

    UserFeedbackOption:{
      name: 'User Feedback Block',
      table: {
        category: 'User Feedback Layout',
      },
      control: {
        type: 'select',
        options: ['Feedback Module'],
      },
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

  //Recipe Hero
  recipe_hero_LabelContent,
  recipe_hero_CookingTime,
  recipe_hero_NumberOfIngridents,
  recipe_hero_NumberOfServings,
  recipe_hero_RecipeDescription,
  recipe_hero_backgroundColorEnable,
  recipe_hero_backgroundColor,
  recipe_hero_video,
  recipe_hero_images,

  //Recipe Body
  recipe_body_ingridents,

  //List
  list_title, 
  list_Content,

  StoryTellingOption,

  //Full Width Media
  heading,
  media,
  content,

  //Flexible Framer
  FramerTitle,
  Frameritems,

  //flexible driver
  DriverTitle,
  DriverDescription,
  DriverButtonCTA,
  DriverLeftImage,
  DriverRightImage,

  //Contact Help Banner
  contact_Title,
  contact_Description,
  contact_callCTA,
  contact_emailCTA,
  contact_social_heading,


  // WYSIWYG
  WYSIWYG_Header,
  WYSIWYG_body,

  //Story highlight
  StoryHighlightTitle,
  StoryHighlightParagraphContent,
  StoryHighlightButtonCTA,
  StoryHighlightImageAsset1,
  StoryHighlightImageAsset2,
  StoryHighlightImageAsset3,
  StoryHighlightitems,

  //Iframe
  iframe_description,

  //polls
  PollImage,
  PollHeading,
  PollContent,
  PollOptions,

  UserFeedbackOption,
  
  //Feedback Module
  brandShape,
  description,
  standardHeading,
  standardChoices,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  homePollData.vote_button = defaultLink({ default_link_content: 'Submit' });

  return (
    <div
      dangerouslySetInnerHTML={{
        __html: recipeDetailTwig({
          ...footerSocial,
          ...footerMenu,
          ...secondaryMenuData,
          ...inlineSearchData,
          ...mainMenuData,
          ...legalLinksData,
          ...siteHeaderData,
          ...siteFooterData,
          ...recipeDetailData,

          ...recipeHeroModuleVideoData,
          ...recipeBodyData,
          ...articleListData,

          ...fullWidthMediaData,
          ...flexibleFramerData,
          ...flexibleDriverData,
          ...contactHelpBannerData,
          ...WYSIWYGData,
          ...storyHighlight,
          ...homePollData,
          ...defaultLink,
          ...iframeData,
          ...feedbackData,

          theme_styles: theme,

          //header
          menu_items: headerMenu,
          alert_banner: headerAlertBanner,

          //footer
          footer_menu_items: footerMenuItems,
          marketing_text: marketingMessage,
          social_menu_items: socialMenuItems,
          legal_links_menu_items: legaMenuItems,
          copyright_text: copyrighttext,
          corporate_tout_text: corporateText,

          //Recipe Hero
          background_color_override: recipe_hero_backgroundColorEnable,
          recipe_hero_module_background_color: recipe_hero_backgroundColor,
          recipe_header_text: recipe_hero_LabelContent,
          recipe_cooking_time: recipe_hero_CookingTime,
          recipe_ingredients_number: recipe_hero_NumberOfIngridents,
          recipe_number_of_servings: recipe_hero_NumberOfServings,
          recipe_description_text: recipe_hero_RecipeDescription,
          videoEnableIndicator: recipe_hero_video,
          images: recipe_hero_images,

          //Recipe Body 
          ingredients_list:recipe_body_ingridents,

          //List
          storybook_list_title: list_title,
          takeaways_list: list_Content,

          StoryTellingOption: StoryTellingOption,

          //Full Width Media
          storybook_full_width_heading: heading,
          media: media,
          storybook_full_width_content: content,

          //flexible framer
          grid_label: FramerTitle,
          storybook_flexible_framer_items: Frameritems,

          //flexible-driver
          flexible_driver_heading: DriverTitle,
          flexible_driver_text: DriverDescription,
          flexible_driver_button_text: DriverButtonCTA,
          storybook_flexible_driver_asset_1: DriverLeftImage,
          storybook_flexible_driver_asset_2: DriverRightImage,

          //Contact Help Banner
          contact_module_heading: contact_Title,
          contact_module_paragraph_content: contact_Description,
          contact_module_contact_phone: contact_callCTA,
          contact_module_contact_email_text: contact_emailCTA,
          contact_module_social_heading:contact_social_heading,


          //Wysiwyg
          storybook_wysiwyg_heading: WYSIWYG_Header,
          content: WYSIWYG_body,

          //story highlight
          heading: StoryHighlightTitle,
          story_highlight_paragraph_content: StoryHighlightParagraphContent,
          story_highlight_button_text: StoryHighlightButtonCTA,
          asset_1: StoryHighlightImageAsset1,
          asset_2: StoryHighlightImageAsset2,
          asset_3: StoryHighlightImageAsset3,
          storybook_story_highlight_items: StoryHighlightitems,

          //Iframe
          iframe_src: iframe_description,

          //polls
          polling_png_asset: PollImage,
          polling_heading: PollHeading,
          polling_paragraph_content: PollContent,
          storybook_poll_options: PollOptions,

          UserFeedbackOption:UserFeedbackOption,

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
  theme: recipeDetailData.theme_styles,

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

  //Recipe Hero Module
  recipe_hero_backgroundColorEnable:
    recipeHeroModuleVideoData.background_color_override,
  recipe_hero_backgroundColor: recipeHeroModuleVideoData.recipe_hero_module_background_color,
  recipe_hero_LabelContent: recipeHeroModuleVideoData.recipe_header_text,
  recipe_hero_CookingTime: recipeHeroModuleVideoData.recipe_cooking_time,
  recipe_hero_NumberOfIngridents:
    recipeHeroModuleVideoData.recipe_ingredients_number,
  recipe_hero_NumberOfServings:
    recipeHeroModuleVideoData.recipe_number_of_servings,
  recipe_hero_RecipeDescription:
    recipeHeroModuleVideoData.recipe_description_text,
  recipe_hero_video: recipeHeroModuleVideoData.videoEnableIndicator,
  recipe_hero_images: recipeHeroModuleVideoData.images,

  //Recipe Body 
  recipe_body_ingridents:recipeBodyData.ingredients_list,

  //List Module
  list_title: articleListData.storybook_list_title,
  list_Content: articleListData.takeaways_list,

  StoryTellingOption: recipeDetailData.StoryTellingOption,

  /* Full Width Media */
  heading: fullWidthMediaData.storybook_full_width_heading,
  media: fullWidthMediaData.media,
  content: fullWidthMediaData.storybook_full_width_content,

  /* Flexible Framer */
  FramerTitle: flexibleFramerData.grid_label,
  Frameritems: flexibleFramerData.storybook_flexible_framer_items,

  /* Flexible Driver component */
  DriverTitle: flexibleDriverData.flexible_driver_heading,
  DriverDescription: flexibleDriverData.flexible_driver_text,
  DriverButtonCTA: flexibleDriverData.flexible_driver_button_text,
  DriverLeftImage: flexibleDriverData.storybook_flexible_driver_asset_1,
  DriverRightImage: flexibleDriverData.storybook_flexible_driver_asset_2,

  //Contact Help Banner
  contact_Title: contactHelpBannerData.contact_module_heading,
  contact_Description: contactHelpBannerData.contact_module_paragraph_content,
  contact_callCTA: contactHelpBannerData.contact_module_contact_phone,
  contact_emailCTA: contactHelpBannerData.contact_module_contact_email_text,
  contact_social_heading:contactHelpBannerData.contact_module_social_heading,


  //Wysiwyg
  WYSIWYG_Header: WYSIWYGData.storybook_wysiwyg_heading,
  WYSIWYG_body: WYSIWYGData.content,

  /* Story Highlight component */

  StoryHighlightTitle: storyHighlight.heading,
  StoryHighlightParagraphContent:
    storyHighlight.story_highlight_paragraph_content,
  StoryHighlightButtonCTA: storyHighlight.story_highlight_button_text,
  StoryHighlightImageAsset1: storyHighlight.asset_1,
  StoryHighlightImageAsset2: storyHighlight.asset_2,
  StoryHighlightImageAsset3: storyHighlight.asset_3,
  StoryHighlightitems: storyHighlight.storybook_story_highlight_items,

  /* Iframe */
  iframe_description: iframeData.iframe_src,

  /* Poll component */
  PollImage: homePollData.polling_png_asset,
  PollHeading: homePollData.polling_heading,
  PollContent: homePollData.polling_paragraph_content,
  PollOptions: homePollData.storybook_poll_options,

  UserFeedbackOption: recipeDetailData.UserFeedbackOption,

  /* Feedback Module */
  brandShape: feedbackData.brand_shape,
  standardHeading: feedbackData.feedback_heading,
  description: feedbackData.feedback_paragraph_content,
  standardChoices: feedbackData.choices,
};
