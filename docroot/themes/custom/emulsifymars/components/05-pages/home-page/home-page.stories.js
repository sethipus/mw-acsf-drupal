import React from 'react';
import { useEffect } from '@storybook/client-api';
import ReactDOMServer from 'react-dom/server';

import homeTwig from './home-page.twig';
import homeData from './home-page.yml';

//Imports for Header and Footer components
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

//Imports for Homepage Hero Module
import homeHero3upData from '../../02-molecules/homepage-hero/3up/homepage-hero-3up.yml';
import homeHeroBasicData from '../../02-molecules/homepage-hero/basic/homepage-hero-basic.yml';
import homeHeroStandardData from '../../02-molecules/homepage-hero/standard/homepage-hero-standard.yml';
import homeHeroVideoData from '../../02-molecules/homepage-hero/video/homepage-hero-video.yml';

//Imports for Product Module
import homeProductContentData from '../../02-molecules/product-content-pair-up/product-content-pair-up.yml';
import flexibleDriverData from '../../02-molecules/flexible-driver/flexible-driver.yml';
import flexibleFramerData from '../../02-molecules/flexible-framer/flexible-framer.yml';
import productFeatureData from '../../02-molecules/product-feature/product-feature.yml';
import productCard from '../../02-molecules/card/product-card/product-card.twig';
import productCardData from '../../02-molecules/card/product-card/product-card.yml';

//Imports for Storytelling Module
import homePollData from '../../02-molecules/polls/poll.yml';
import freeformData from '../../02-molecules/freeform-story/freeform-story-center.yml';
import storyHighlight from '../../02-molecules/story-highlight/story_highlight.yml';
import recpieFeature from '../../02-molecules/recipe-feature-module/recipe-feature-module.yml';
import defaultLink from './../../01-atoms/links/defaultLink/defaultLink.twig'
import fullWidthMediaData from '../../02-molecules/article-media/full-width/full-width-media.yml';
import iframeData from '../../01-atoms/iframe/iframe.yml';
import WYSIWYGData from '../../02-molecules/article-media/wysiwyg/article-wysiwyg.yml';

export default {
  title: 'Pages/[PT 01] Homepage',
  parameters: {
    componentSubtitle: `Each brand site will only have 1
                 Homepage. The modules allowed on the Homepage
                 and the areas where they are allowed is listed
                 below but admin as able to add any module on page
                 template`,
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
    //HomepageHero Controls
    homepageOption: {
      name: 'HomePage Modules',
      description: 'Choose any desired homepage.',
      table: {
        category: 'Homepage Layout',
      },
      control: {
        type: 'select',
        options: [
          'Standard HP Hero',
          'Video HP Hero',
          '3-Up HP Hero',
          'Basic HP Hero',
        ],
      },
    },
    homepageEyebrow: {
      name: 'Eyebrow',
      description: 'Eyebrow text for the homepage hero block.',
      defaultValue: {
        summary: 'Lorem..',
      },
      table: {
        category: 'Homepage Component',
      },
      control: {
        type: 'text',
      },
    },
    homepageTitle: {
      name: 'Title',
      description: 'Title text for the homepage hero block.',
      defaultValue: {
        summary: 'Lorem..',
      },
      table: {
        category: 'Homepage Component',
      },
      control: {
        type: 'text',
      },
    },
    basicHomepageTitle: {
      name: 'Title(Only applicable to Basic HP)',
      description: 'Title text for the homepage hero block.',
      defaultValue: {
        summary: 'Lorem..',
      },
      table: {
        category: 'Homepage Component',
      },
      control: {
        type: 'text',
      },
    },
    homepageButtonCTA: {
      name: 'Button CTA',
      description: 'Button CTA text for the homepage hero block.',
      defaultValue: {
        summary: 'Lorem..',
      },
      table: {
        category: 'Homepage Component',
      },
      control: {
        type: 'text',
      },
    },
    hero_images: {
      name: 'Background Media',
      description: 'Background media for the homepage block.',
      defaultValue: {
        summary:
          'For video - "https://lhcdn.mars.com/adaptivemedia/rendition/id_88cf808674c1c085869db727c492e1cba40b0dc8/name_88cf808674c1c085869db727c492e1cba40b0dc8.jpg"',
      },
      table: {
        category: 'Homepage Component',
      },
      control: {
        type: 'object',
      },
    },
    heroVideoUrl: {
      name: 'Background Video(Only applicable to Video HP)',
      description: 'Background video for the homepage block.',
      table: {
        category: 'Homepage Component',
      },
      control: { type: 'object' },
    },
    blocks: {
      name: 'Blocks(only applicable to block layout)',
      description: 'Edit block layout for 3up block homepage hero layout.',
      table: {
        category: 'Homepage Component',
      },
      control: {
        type: 'object',
      },
    },
    productOption: {
      name: 'Product Block',
      table: {
        category: 'Product Layout',
      },
      control: {
        type: 'select',
        options: [
          'Flexible Framer',
          'Product Content Pair Up',
          'Product Feature',
          'Flexible Driver',
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
    content_card_eyebrow:{
      name:'Card Eyebrow',
      table: {
        category: 'Content Pair Up Component',
      },
      control:{
        type:'text'
      }
    },
    content_card_item:{
      name:'Card Contents',
      table: {
        category: 'Content Pair Up Component',
      },
      control:{
        type:'object'
      }
    },
    //Product Feature Controls
    ProductEyebrow: {
      name: 'Eyebrow',
      defaultValue: { summary: 'Product ABC ' },
      table: {
        category: 'Product Feature Component',
      },
      description:
        'Eyebrow text for the product feature. <b>Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    ProductTitle: {
      name: 'Title text',
      table: {
        category: 'Product Feature Component',
      },
      defaultValue: { summary: 'ABC Chocolate' },
      description:
        'Title for the product feature.<b> Maximum character limit is 55.</b>',
      control: { type: 'text' },
    },
    ProductBackground: {
      name: 'Background Color',
      table: {
        category: 'Product Feature Component',
      },
      defaultValue: { summary: '#fff' },
      description: 'Background color HEX value for the product feature',
      control: { type: 'color' },
    },
    ProductImage: {
      name: 'Image Assets',
      table: {
        category: 'Product Feature Component',
      },
      description:
        'Product image for the product.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      control: { type: 'object' },
    },
    ProductExploreCTA: {
      name: 'Button CTA',
      table: {
        category: 'Product Feature Component',
      },
      defaultValue: { summary: 'Explore' },
      description:
        'Button CTA text for the product feature. <b>Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    // //Flexible Framer controls
    framer_Title: {
      description:
        'Change the title of the content.<b> Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Lorem' },
      table: { category: 'Flexible Framer component' },
      control: { type: 'text' },
    },
    framer_items: {
      name: 'Stories',
      description:
        'Change the stories of the content.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b> Maximum character for the Item title and Item description and button CTA is 60, 255 and 15 respectively.</b>',
        table: { category: 'Flexible Framer component' },
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
          'Product Feature',
          'Flexible Driver',
          'Freeform Story Block',
          'Story Highlight',
          'Poll',
          'Recipe Feature',
          'Iframe',
          'Full Width Media',
          'WYSIWYG'
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


    //Polls control
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
    enableBackgroundColor:{
      name:'Background Color Usage',
      table:{
        category: 'Freeform Story Component',
      },
      description:'Apply background color to the story',
      control:{
          type:'boolean'
      }
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
    }
  },
};

export const homePageLayout = ({
  theme,
  //Header
  headerMenu,
  headerAlertBanner,
  //footer
  footerMenuItems,
  marketingMessage,
  socialMenuItems,
  legaMenuItems,
  copyrighttext,
  corporateText,
  //homepage hero
  homepageOption,
  homepageEyebrow,
  homepageTitle,
  basicHomepageTitle,
  homepageButtonCTA,
  hero_images,
  heroVideoUrl,
  blocks,
  productOption,
  //flexible driver
  DriverTitle,
  DriverDescription,
  DriverButtonCTA,
  DriverLeftImage,
  DriverRightImage,
  //product content pair up
  ContentTitle,
  ContentEyebrowText,
  ContentBackground,
  ProductEyebrow,
  content_card_eyebrow,
  content_card_item,
  //Product feature
  ProductTitle,
  ProductBackground,
  ProductImage,
  ProductExploreCTA,
  //Flexible Framer
  framer_Title,
  framer_items,

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
  // recipe feature
  RecipeTitle,
  Recipecta,
  recipe_media,
  //Story highlight
  StoryHighlightTitle,
  StoryHighlightParagraphContent,
  StoryHighlightButtonCTA,
  StoryHighlightImageAsset1,
  StoryHighlightImageAsset2,
  StoryHighlightitems,
  //Full Width Media
  heading,
  media,
  content,
  // WYSIWYG
  WYSIWYG_Header,
  WYSIWYG_body,
  //Iframe
  iframe_description,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  homeProductContentData.supporting_card_content = [
    ReactDOMServer.renderToStaticMarkup(
      <div
        dangerouslySetInnerHTML={{
          __html: productCard({ ...productCardData,card__eyebrow: content_card_eyebrow,item:content_card_item }),
        }}
      />,
    ),
  ];
  homePollData.vote_button = defaultLink({default_link_content: 'Submit'});
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: homeTwig({
          ...footerSocial,
          ...footerMenu,
          ...secondaryMenuData,
          ...inlineSearchData,
          ...mainMenuData,
          ...legalLinksData,
          ...siteHeaderData,
          ...siteFooterData,

          ...homeHero3upData,
          ...homeHeroBasicData,
          ...homeHeroStandardData,
          ...homeHeroVideoData,

          ...homeProductContentData,
          ...flexibleDriverData,
          ...flexibleFramerData,
          ...productFeatureData,

          ...homePollData,
          ...freeformData,
          ...storyHighlight,
          ...recpieFeature,
          ...WYSIWYGData,
          ...iframeData,
          ...fullWidthMediaData,

          theme_styles: theme,

          menu_items: headerMenu,
          alert_banner: headerAlertBanner,

          footer_menu_items: footerMenuItems,
          marketing_text: marketingMessage,
          social_menu_items: socialMenuItems,
          legal_links_menu_items: legaMenuItems,
          copyright_text: copyrighttext,
          corporate_tout_text: corporateText,
          //Homepage Zone
          homepageOption: homepageOption,

          eyebrow: homepageEyebrow,
          title_label: homepageTitle,
          homepage_hero_basic_content: basicHomepageTitle,
          cta_title: homepageButtonCTA,
          hero_images: hero_images,
          video__src__url: heroVideoUrl,
          blocks: blocks,

          productOption: productOption,

          //flexible-driver
          flexible_driver_heading: DriverTitle,
          flexible_driver_text: DriverDescription,
          flexible_driver_button_text: DriverButtonCTA,
          flexible_driver_asset_1: DriverLeftImage,
          flexible_driver_asset_2: DriverRightImage,

          //content product pair up
          lead_card_title: ContentTitle,
          lead_card_eyebrow: ContentEyebrowText,
          background: ContentBackground,
          card__eyebrow: content_card_eyebrow,
          item:content_card_item,

          //product feature
          eyebrow_text: ProductEyebrow,
          storybook_product_feature_heading: ProductTitle,
          storybook_product_feature_background_color: ProductBackground,
          image_src: ProductImage,
          default_link_content: ProductExploreCTA,

          //flexible framer 
          grid_label: framer_Title,
          flexible_framer_items: framer_items,

          //Storytelling Zone
          StoryTellingOption: StoryTellingOption,

          //polls
          polling_png_asset: PollImage,
          polling_heading: PollHeading,
          polling_paragraph_content: PollContent,
          storybook_poll_options: PollOptions,

          //freeform story 
          use_custom_color:enableBackgroundColor,
          custom_background_color: FreeFormBackgroundColor,
          freeform_story_img_src: FreeFormBackgroundImage,
          freeform_story_header_1: FreeFormSubHeadingTitle,
          freeform_story_header_2: FreeFormHeadingTitle,
          freeform_story_paragraph_content: FreeFormContentText,
          freeform_story_align:FreeFormAlign,

          //recipe feature
          title: RecipeTitle,
          Recipecta,
          recipe_media,

          //story highlight
          heading: StoryHighlightTitle,
          story_highlight_paragraph_content: StoryHighlightParagraphContent,
          story_highlight_button_text: StoryHighlightButtonCTA,
          asset_2: StoryHighlightImageAsset1,
          asset_3: StoryHighlightImageAsset2,
          storybook_story_highlight_items: StoryHighlightitems,

          //Full Width Media
          full_width_heading: heading,
          media: media,
          storybook_full_width_content: content,

          //Wysiwyg
          storybook_wysiwyg_heading: WYSIWYG_Header,
          content: WYSIWYG_body,

          //Iframe
          iframe_src: iframe_description,

        }),
      }}
    />
  );
};
homePageLayout.args = {
  theme: homeData.theme_styles,
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

  //For Homepage Layout
  homepageOption: homeData.homepageOption,

  /* 3-UP HP hero component */
  homepageEyebrow: homeHero3upData.eyebrow,
  homepageTitle: homeHero3upData.title_label,
  homepageButtonCTA: homeHero3upData.cta_title,
  hero_images: homeHero3upData.hero_images,
  blocks: homeHero3upData.blocks,

  /* Basic HP hero component */
  basicHomepageTitle: homeHeroBasicData.homepage_hero_basic_content,
  hero_images: homeHeroBasicData.hero_images,

  /*Standard HP hero component */
  homepageEyebrow: homeHeroStandardData.eyebrow,
  homepageTitle: homeHeroStandardData.title_label,
  homepageButtonCTA: homeHeroStandardData.cta_title,
  hero_images: homeHeroStandardData.hero_images,

  /* Video hero component */
  homepageEyebrow: homeHeroVideoData.eyebrow,
  homepageTitle: homeHeroVideoData.title_label,
  homepageButtonCTA: homeHeroVideoData.cta_title,
  heroVideoUrl: homeHeroVideoData.video__src__url,

  //For Product Layout
  productOption: homeData.productOption,

  /* Flexible Driver component */
  DriverTitle: flexibleDriverData.flexible_driver_heading,
  DriverDescription: flexibleDriverData.flexible_driver_text,
  DriverButtonCTA: flexibleDriverData.flexible_driver_button_text,
  DriverLeftImage: flexibleDriverData.flexible_driver_asset_1,
  DriverRightImage: flexibleDriverData.flexible_driver_asset_2,

  /* Product Content pair up component */
  ContentTitle: homeProductContentData.lead_card_title,
  ContentEyebrowText: homeProductContentData.lead_card_eyebrow,
  ContentBackground: homeProductContentData.background,
  content_card_eyebrow: 'MADE WITH',
  content_card_item:productCardData.item,

  /* Product Feature component */
  ProductEyebrow: productFeatureData.eyebrow_text,
  ProductTitle: productFeatureData.storybook_product_feature_heading,
  ProductBackground: productFeatureData.storybook_product_feature_background_color,
  ProductImage: productFeatureData.image_src,
  ProductExploreCTA: productFeatureData.default_link_content,



  //For Storytelling Layout
  StoryTellingOption: homeData.StoryTellingOption,
  /* Poll component */ 
  PollImage: homePollData.polling_png_asset,
  PollHeading: homePollData.polling_heading,
  PollContent: homePollData.polling_paragraph_content,
  PollOptions: homePollData.storybook_poll_options,

  /* Freeform Story component */ 
  enableBackgroundColor:freeformData.use_custom_color,
  FreeFormBackgroundColor: freeformData.custom_background_color,
  FreeFormBackgroundImage: freeformData.freeform_story_img_src,
  FreeFormSubHeadingTitle: freeformData.freeform_story_header_1,
  FreeFormHeadingTitle: freeformData.freeform_story_header_2,
  FreeFormContentText: freeformData.freeform_story_paragraph_content,
  FreeFormAlign:freeformData.freeform_story_align ,

 /* Recipe Feature component */
  RecipeTitle: recpieFeature.title,
  recipe_media: recpieFeature.recipe_media,

  /* Story Highlight component */ 
  StoryHighlightTitle: storyHighlight.heading,
  StoryHighlightParagraphContent:
    storyHighlight.story_highlight_paragraph_content,
  StoryHighlightButtonCTA: storyHighlight.story_highlight_button_text,
  StoryHighlightImageAsset1: storyHighlight.asset_2,
  StoryHighlightImageAsset2: storyHighlight.asset_3,
  StoryHighlightitems: storyHighlight.storybook_story_highlight_items,

  /* Full Width Media */
  heading: fullWidthMediaData.full_width_heading,
  media: fullWidthMediaData.media,
  content: fullWidthMediaData.storybook_full_width_content,

  //Wysiwyg
  WYSIWYG_Header: WYSIWYGData.storybook_wysiwyg_heading,
  WYSIWYG_body: WYSIWYGData.content,

  /* Iframe */
  iframe_description: iframeData.iframe_src,

  //flexible framer
  framer_Title: flexibleFramerData.grid_label,
  framer_items: flexibleFramerData.flexible_framer_items,
};
