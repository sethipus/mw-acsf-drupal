import React from 'react';
import ReactDOMServer from 'react-dom/server';
import { useEffect } from '@storybook/client-api';

import landingTwig from './landing-page.twig';
import landingData from './landing-page.yml';

//Imports for Header and Footer component
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

//Imports for Storytelling Zone
import homeProductContentData from '../../02-molecules/product-content-pair-up/product-content-pair-up.yml';
import flexibleFramerData from '../../02-molecules/flexible-framer/flexible-framer.yml';
import productCard from '../../02-molecules/card/product-card/product-card.twig';
import productCardData from '../../02-molecules/card/product-card/product-card.yml';
import homePollData from '../../02-molecules/polls/poll.yml';
import defaultLink from './../../01-atoms/links/defaultLink/defaultLink.twig';
import freeformData from '../../02-molecules/freeform-story/freeform-story-center.yml';
import storyHighlight from '../../02-molecules/story-highlight/story_highlight.yml';
import fullWithMediaData from '../../02-molecules/article-media/full-width/full-width-media.yml';
import iframeData from '../../01-atoms/iframe/iframe.yml';

//Imports for Pathing Module
import recpieFeature from '../../02-molecules/recipe-feature-module/recipe-feature-module.yml';
import productFeatureData from '../../02-molecules/product-feature/product-feature.yml';
import flexibleDriverData from '../../02-molecules/flexible-driver/flexible-driver.yml';
import contentFeatureData from '../../02-molecules/content-feature/content-feature.yml';

//Imports for Community Module

import feedbackData from '../../02-molecules/feedback-module/feedback.yml';

export default {
  title: 'Pages/[PT 02] Landing Page',
  parameters: {
    componentSubtitle: `These pages provide a high level of flexibility
          to customize compelling stories around specific topics that are 
          important to each brand, like the Twix Pick A Side campaign for
          example. This page encourages deeper exploration throughout the site.
          This template is highly flexible, allowing editors to drive to multiple
          other initiatives or pages from landing pages, or dive deep into one
          specific initiative in detail, admin is able to edit zones and components
          options`,
  },
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


    StoryTellingOption: {
      name: 'Storytelling Block',
      table: {
        category: 'Storytelling Layout',
      },
      control: {
        type: 'select',
        options: [
          'Product Content Pair Up',
          'Flexible Framer',
          'Freeform Story Block',
          'Story Highlight',
          'Poll',
          'Full With Media',
          'Iframe',
        ],
      },
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

    //Freeform Storyblock controls
    enableBackgroundColor: {
      name: 'Background Color Usage',
      table: {
        category: 'Freeform Story Component',
      },
      description: 'Apply background color to the story',
      control: {
        type: 'boolean',
      },
    },
    FreeFormBackgroundColor: {
      name: 'Background Color',
      table: {
        category: 'Freeform Story Component',
      },
      defaultValue: { summary: '#fff' },
      description: 'Background color of the story',
      control: { type: 'color' },
    },
    FreeFormBackgroundImage: {
      name: 'Background Image',
      table: {
        category: 'Freeform Story Component',
      },
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      description:
        'Background image of the story.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      control: { type: 'object' },
    },
    FreeFormSubHeadingTitle: {
      name: 'Subheading text',
      table: {
        category: 'Freeform Story Component',
      },
      defaultValue: { summary: 'Lorem Ipsum..' },
      description:
        'Subheading title of the story. <b>Maximum character limit is 60.</b>',
      control: { type: 'text' },
    },
    FreeFormHeadingTitle: {
      name: 'Heading text',
      table: {
        category: 'Freeform Story Component',
      },
      defaultValue: { summary: 'Lorem..' },
      description:
        'Heading title of the story.<b>  Maximum character limit is 60 </b>.',
      control: { type: 'text' },
    },
    FreeFormContentText: {
      name: 'Content text',
      table: {
        category: 'Freeform Story Component',
      },
      defaultValue: { summary: 'Lorem..' },
      description:
        'Content of the story.<b> Maximum character limit is 1000.</b>',
      control: { type: 'text' },
    },
    FreeFormAlign: {
      name: 'Alignment',
      table: {
        category: 'Freeform Story Component',
      },
      defaultValue: { summary: 'Left' },
      description: 'Alignemnt of the story',
      control: 'select',
      options: ['left', 'right', 'center'],
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
      name: 'Image asset 1',
      description:
        'Change the first image of the story. Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b>Story item title maximum character limit is 300.</b> ',
      table: { category: 'Story Highlight' },
      control: { type: 'object' },
    },
    StoryHighlightImageAsset2: {
      name: 'Image asset 2',
      description:
        'Change the second image of the story. Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b>Story item title maximum character limit is 300.</b> ',
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

    //Content content pair up controls
    ContentTitle: {
      name: 'Title text',
      description:
        'Title of the layout.<b> Maximum character limit is 100.</b>',
      table: {
        category: 'Content Pair Up Component',
      },
      defaultValue: { summary: 'Lorem Ipsum..' },
      control: { type: 'text' },
    },
    ContentEyebrowText: {
      name: 'Eyebrow',
      description:
        'Eyebrow of the layout. <b>Maximum character limit is 100.</b>',
      table: {
        category: 'Content Pair Up Component',
      },
      defaultValue: { summary: 'Lorem' },
      control: { type: 'text' },
    },
    ContentBackground: {
      name: 'Background Image',
      description:
        'Background Image of the layout.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Content Pair Up Component',
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
        category: 'Content Pair Up Component',
      },
      control: {
        type: 'text',
      },
    },
    content_card_item: {
      name: 'Card Contents',
      table: {
        category: 'Content Pair Up Component',
      },
      control: {
        type: 'object',
      },
    },

    PathingOption: {
      name: 'Pathing Block',
      table: {
        category: 'Pathing Layout',
      },
      control: {
        type: 'select',
        options: [
          'Flexible Driver',
          'Flexible Framer',
          'Content Feature',
          'Recipe Feature',
          'Product Feature',
        ],
      },
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

    //Content Feature
    ContentFeatureEyebrow: {
      name: 'Eyebrow',
      description:
        'Eyebrow of the content.<b> Maximum character limit is 15. </b>',
      defaultValue: { summary: 'INITIATIVE' },
      table: {
        category: 'Content Feature Component',
      },
      control: { type: 'text' },
    },
    ContentFeatureTitle: {
      name: 'Title',
      description:
        'Title of the content. <b>Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Title..' },
      table: {
        category: 'Content Feature Component',
      },
      control: { type: 'text' },
    },
    ContentFeature_background_images: {
      name: 'Background Image',
      description:
        'Background Image of the content.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      table: {
        category: 'Content Feature Component',
      },
      control: { type: 'object' },
    },
    ContentFeatureDescription: {
      name: 'Feature Description',
      description:
        'Description of the content. <b>Maximum character limit is 300.</b>',
      defaultValue: { summary: 'lorem pisum..' },
      table: {
        category: 'Content Feature Component',
      },
      control: { type: 'text' },
    },
    ContentFeatureExploreCTA: {
      name: 'Button CTA',
      description: 'Button text. <b>Maximum character limit is 15.</b>',
      defaultValue: { summary: 'Submit' },
      table: {
        category: 'Content Feature Component',
      },
      control: { type: 'text' },
    },

    //Recipe Feature controls
    RecipeTitle: {
      name: 'Recipe ttile text',
      defaultValue: { summary: 'Product ABC ' },
      table: {
        category: 'Recipe Feature',
      },
      description:
        'Recipe title for the recipe feature.<b> Maximum character limit is 60.</b>',
      control: { type: 'text' },
    },
    Recipecta: {
      name: 'Button CTA',
      defaultValue: { summary: 'SEE DETAILS ' },
      table: {
        category: 'Recipe Feature',
      },
      description:
        'Button CTA for the recipe feature button.<b> Maximum character limit is 15.</b>',
      control: { type: 'object' },
    },
    recipe_media: {
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

    //Product Feature
    ProductFeatureEyebrow: {
      name: 'Eyebrow',
      defaultValue: { summary: 'Product ABC ' },
      table: {
        category: 'Product Feature component',
      },
      description:
        'Eyebrow text for the product feature. <b>Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    ProductFeatureTitle: {
      name: 'Title text',
      table: {
        category: 'Product Feature component',
      },
      defaultValue: { summary: 'ABC Chocolate' },
      description:
        'Title for the product feature.<b> Maximum character limit is 55.</b>',
      control: { type: 'text' },
    },
    ProductFeatureBackground: {
      name: 'Background Color',
      table: {
        category: 'Product Feature component',
      },
      defaultValue: { summary: '#fff' },
      description: 'Background color HEX value for the product feature',
      control: { type: 'color' },
    },
    ProductFeatureProductImage: {
      name: 'Image Assets',
      table: {
        category: 'Product Feature component',
      },
      description:
        'Product image for the product.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      control: { type: 'object' },
    },
    ProductFeatureExploreCTA: {
      name: 'Button CTA',
      table: {
        category: 'Product Feature component',
      },
      defaultValue: { summary: 'Explore' },
      description:
        'Button CTA text for the product feature. <b>Maximum character limit is 15.</b>',
      control: { type: 'text' },
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

    CommunityOption: {
      name: ' Community Block',
      table: {
        category: 'Community Layout',
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

export const landingPageLayout = ({
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

  StoryTellingOption,

  //polls
  PollImage,
  PollHeading,
  PollContent,
  PollOptions,

  //freeform story block
  enableBackgroundColor,
  FreeFormBackgroundColor,
  FreeFormBackgroundImage,
  FreeFormSubHeadingTitle,
  FreeFormHeadingTitle,
  FreeFormContentText,
  FreeFormAlign,

  //Story highlight
  StoryHighlightTitle,
  StoryHighlightParagraphContent,
  StoryHighlightButtonCTA,
  StoryHighlightImageAsset1,
  StoryHighlightImageAsset2,
  StoryHighlightitems,

  //product content pair up
  ContentTitle,
  ContentEyebrowText,
  ContentBackground,
  content_card_eyebrow,
  content_card_item,

  PathingOption,

  //flexible driver
  DriverTitle,
  DriverDescription,
  DriverButtonCTA,
  DriverLeftImage,
  DriverRightImage,

  //Content Feature
  ContentFeatureEyebrow,
  ContentFeatureTitle,
  ContentFeature_background_images,
  ContentFeatureDescription,
  ContentFeatureExploreCTA,

  // recipe feature
  RecipeTitle,
  Recipecta,
  recipe_media,

  //product Feature
  ProductFeatureEyebrow,
  ProductFeatureTitle,
  ProductFeatureBackground,
  ProductFeatureProductImage,
  ProductFeatureExploreCTA,

  //Flexible Framer
  FramerTitle,
  Frameritems,

  //Full Width Media
  heading,
  media,
  content,

  //Iframe
  iframe_description,
  CommunityOption,

  //Feedback Module
  brandShape,
  description,
  standardHeading,
  standardChoices,

}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  homeProductContentData.supporting_card_content = [
    ReactDOMServer.renderToStaticMarkup(
      <div
        dangerouslySetInnerHTML={{
          __html: productCard({
            ...productCardData,
            card__eyebrow: content_card_eyebrow,
            item: content_card_item,
          }),
        }}
      />,
    ),
  ];
  homePollData.vote_button = defaultLink({ default_link_content: 'Submit' });
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: landingTwig({
          ...parentPageHeaderData,

          ...homeProductContentData,
          // ...flexibleFramerData,
          ...productCardData,
          ...homePollData,
          ...freeformData,
          ...storyHighlight,
          ...fullWithMediaData,
          ...iframeData,
          ...feedbackData,

          ...footerSocial,
          ...footerMenu,
          ...secondaryMenuData,
          ...inlineSearchData,
          ...mainMenuData,
          ...legalLinksData,
          ...siteHeaderData,
          ...siteFooterData,
          ...landingData,

          ...recpieFeature,
          ...productFeatureData,
          ...flexibleDriverData,
          ...contentFeatureData,

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

          //parent page header
          pp_header_eyebrow_text: Eyebrow,
          pp_header_heading: Title,
          pp_header_paragraph_content: Description,
          parent_page_media_type: BackgroundTheme,
          parent_page_media_entities: parent_page_media_entities,
          StoryTellingOption: StoryTellingOption,

          //content product pair up
          lead_card_title: ContentTitle,
          lead_card_eyebrow: ContentEyebrowText,
          background: ContentBackground,
          card__eyebrow: content_card_eyebrow,
          item: content_card_item,

          //polls
          polling_png_asset: PollImage,
          polling_heading: PollHeading,
          polling_paragraph_content: PollContent,
          storybook_poll_options: PollOptions,

          //freeform story
          use_custom_color: enableBackgroundColor,
          custom_background_color: FreeFormBackgroundColor,
          freeform_story_img_src: FreeFormBackgroundImage,
          freeform_story_header_1: FreeFormSubHeadingTitle,
          freeform_story_header_2: FreeFormHeadingTitle,
          freeform_story_paragraph_content: FreeFormContentText,
          freeform_story_align: FreeFormAlign,

          //story highlight
          heading: StoryHighlightTitle,
          story_highlight_paragraph_content: StoryHighlightParagraphContent,
          story_highlight_button_text: StoryHighlightButtonCTA,
          asset_2: StoryHighlightImageAsset1,
          asset_3: StoryHighlightImageAsset2,
          storybook_story_highlight_items: StoryHighlightitems,

          PathingOption: PathingOption,

          //flexible-driver
          flexible_driver_heading: DriverTitle,
          flexible_driver_text: DriverDescription,
          flexible_driver_button_text: DriverButtonCTA,
          flexible_driver_asset_1: DriverLeftImage,
          flexible_driver_asset_2: DriverRightImage,

          //content feature
          storybook_content_feature_eyebrow_text: ContentFeatureEyebrow,
          storybook_content_feature_heading: ContentFeatureTitle,
          paragraph_content: ContentFeatureDescription,
          storybook_content_feature_default_link_content: ContentFeatureExploreCTA,
          background_images: ContentFeature_background_images,

          //recipe feature
          title: RecipeTitle,
          cta:Recipecta,
          recipe_media,

          //product feature
          eyebrow_text: ProductFeatureEyebrow,
          storybook_product_feature_heading: ProductFeatureTitle,
          storybook_product_feature_background_color: ProductFeatureBackground,
          image_src: ProductFeatureProductImage,
          default_link_content: ProductFeatureExploreCTA,

          //flexible framer
          grid_label: FramerTitle,
          flexible_framer_items: Frameritems,

          //Full Width Media
          full_width_heading: heading,
          media: media,
          storybook_full_width_content:content,

          //Iframe
          iframe_src: iframe_description,

          CommunityOption:CommunityOption,

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
landingPageLayout.args = {
  theme: landingData.theme_styles,

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

  //For Storytelling block
  StoryTellingOption: landingData.StoryTellingOption,

  /* Product Content pair up component */
  ContentTitle: homeProductContentData.lead_card_title,
  ContentEyebrowText: homeProductContentData.lead_card_eyebrow,
  ContentBackground: homeProductContentData.background,
  content_card_eyebrow: 'MADE WITH',
  content_card_item: productCardData.item,

  /* Poll component */
  PollImage: homePollData.polling_png_asset,
  PollHeading: homePollData.polling_heading,
  PollContent: homePollData.polling_paragraph_content,
  PollOptions: homePollData.storybook_poll_options,

  /* Freeform Story component */

  enableBackgroundColor: freeformData.use_custom_color,
  FreeFormBackgroundColor: freeformData.custom_background_color,
  FreeFormBackgroundImage: freeformData.freeform_story_img_src,
  FreeFormSubHeadingTitle: freeformData.freeform_story_header_1,
  FreeFormHeadingTitle: freeformData.freeform_story_header_2,
  FreeFormContentText: freeformData.freeform_story_paragraph_content,
  FreeFormAlign: freeformData.freeform_story_align,

  /* Story Highlight component */
  StoryHighlightTitle: storyHighlight.heading,
  StoryHighlightParagraphContent:
    storyHighlight.story_highlight_paragraph_content,
  StoryHighlightButtonCTA: storyHighlight.story_highlight_button_text,
  StoryHighlightImageAsset1: storyHighlight.asset_2,
  StoryHighlightImageAsset2: storyHighlight.asset_3,
  StoryHighlightitems: storyHighlight.storybook_story_highlight_items,

  //For Pathing
  PathingOption: landingData.PathingOption,

  /* Flexible Driver component */
  DriverTitle: flexibleDriverData.flexible_driver_heading,
  DriverDescription: flexibleDriverData.flexible_driver_text,
  DriverButtonCTA: flexibleDriverData.flexible_driver_button_text,
  DriverLeftImage: flexibleDriverData.flexible_driver_asset_1,
  DriverRightImage: flexibleDriverData.flexible_driver_asset_2,

  /* Content Feature */
  ContentFeatureEyebrow: contentFeatureData.storybook_content_feature_eyebrow_text,
  ContentFeatureTitle: contentFeatureData.storybook_content_feature_heading,
  ContentFeatureDescription: contentFeatureData.paragraph_content,
  ContentFeature_background_images: contentFeatureData.background_images,
  ContentFeatureExploreCTA: contentFeatureData.storybook_content_feature_default_link_content,

  /* Recipe Feature component */
  RecipeTitle: recpieFeature.title,
  recipe_media: recpieFeature.recipe_media,
  Recipecta:recpieFeature.cta,

  /* Product Feature */
  ProductFeatureEyebrow: productFeatureData.eyebrow_text,
  ProductFeatureTitle: productFeatureData.storybook_product_feature_heading,
  ProductFeatureBackground: productFeatureData.storybook_product_feature_background_color,
  ProductFeatureProductImage: productFeatureData.image_src,
  ProductFeatureExploreCTA: productFeatureData.default_link_content,

  /* Flexible Framer */
  FramerTitle: flexibleFramerData.grid_label,
  Frameritems: flexibleFramerData.flexible_framer_items,

  /* Full Width Media */
  heading: fullWithMediaData.full_width_heading,
  media: fullWithMediaData.media,
  content:fullWithMediaData.storybook_full_width_content,

  /* Iframe */
  iframe_description: iframeData.iframe_src,

  CommunityOption:landingData.CommunityOption,

  /* Feedback Module */
  brandShape: feedbackData.brand_shape,
  description: feedbackData.feedback_paragraph_content,
  standardHeading: feedbackData.feedback_heading,
  standardChoices: feedbackData.choices,
};
