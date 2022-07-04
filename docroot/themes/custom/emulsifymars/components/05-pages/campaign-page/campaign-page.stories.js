import React from 'react';
import { useEffect } from '@storybook/client-api';
import ReactDOMServer from 'react-dom/server';

import campaignTwig from './campaign-page.twig';
import campaignData from './campaign-page.yml';

//Imports for Header and Footer
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

//Imports for All modules
import homeHero3upData from '../../02-molecules/homepage-hero/3up/homepage-hero-3up.yml';
import homeHeroBasicData from '../../02-molecules/homepage-hero/basic/homepage-hero-basic.yml';
import homeHeroStandardData from '../../02-molecules/homepage-hero/standard/homepage-hero-standard.yml';
import homeHeroVideoData from '../../02-molecules/homepage-hero/video/homepage-hero-video.yml';
import freeformData from '../../02-molecules/freeform-story/freeform-story-center.yml';
import homeProductContentData from '../../02-molecules/product-content-pair-up/product-content-pair-up.yml';
import productCard from '../../02-molecules/card/product-card/product-card.twig';
import productCardData from '../../02-molecules/card/product-card/product-card.yml';
import productRatingData from '../../02-molecules/card/product-card/product-rating.yml';
import storyHighlight from '../../02-molecules/story-highlight/story_highlight.yml';
import recpieFeature from '../../02-molecules/recipe-feature-module/recipe-feature-module.yml';
import productFeatureData from '../../02-molecules/product-feature/product-feature.yml';
import flexibleDriverData from '../../02-molecules/flexible-driver/flexible-driver.yml';
import flexibleFramerData from '../../02-molecules/flexible-framer/flexible-framer.yml';
import parentPageHeaderData from '../../02-molecules/parent-page-header/parent-page-header.yml';
import fullWidthMediaData from '../../02-molecules/article-media/full-width/full-width-media.yml';
import socialFeedData from '../../02-molecules/social-feed/social-feed.yml';
import mediaCarouselData from '../../02-molecules/media-carousel/media-carousel.yml';
import listData from '../../02-molecules/article-media/list/article-list.yml';
import recommendationModuleData from '../../02-molecules/recommendations-module/recommendations-module.yml';
import { recipeCardLayout } from '../../02-molecules/card/recipe-card/recipe-card.stories';
import pdpHeroData from '../../02-molecules/pdp/pdp-hero/pdp-hero.yml';
import recipeHeroModuleVideoData from '../../02-molecules/recipe-hero-module/recipe-hero-module-video.yml';
import articleHeaderImageData from '../../02-molecules/article-header/article-header-image.yml';
import contactHelpBannerData from '../../02-molecules/contact-module/contact-module.yml';
import faqListData from '../../02-molecules/faq-list/faq-list.yml';
import feedbackData from '../../02-molecules/feedback-module/feedback.yml';
import WYSIWYGData from '../../02-molecules/article-media/wysiwyg/article-wysiwyg.yml';
import newsletterSignUpFormData from '../../02-molecules/newsletter-sign-form/newsletter-form/newsletter-form.yml';

export default {
  title: 'Pages/[PT 10] Campaign Page',
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

    zone1: {
      name: 'Zone 1 Layout',
      table: {
        category: 'Zone 1 Layout',
      },
      control: {
        type: 'select',
        options: [
          'Standard HP Hero',
          '3-Up HP Hero',
          'Video HP Hero',
          'Basic HP Hero',
          'Freeform Story Block',
          'Product Content Pair Up',
          'Story Highlight',
          'Recipe Feature',
          'Product Feature',
          'Flexible Framer',
          'Flexible Driver',
          'Parent Page Header',
          'Full Width Media',
          'Social Feed',
          'Media Carousel',
          'List',
          'Recommendations Module',
          'Product Detail Hero',
          'Recipe Detail Page Hero',
          'Article Header',
          'Contact & Help Banner',
          'FAQ',
          'Feedback Module',
          'WYSIWYG',
          'NewsLetter Sign Up Form',
        ],
      },
    },

    zone2: {
      name: 'Zone 2 Layout',
      table: {
        category: 'Zone 2 Layout',
      },
      control: {
        type: 'select',
        options: [
          'Standard HP Hero',
          '3-Up HP Hero',
          'Video HP Hero',
          'Basic HP Hero',
          'Freeform Story Block',
          'Product Content Pair Up',
          'Story Highlight',
          'Recipe Feature',
          'Product Feature',
          'Flexible Framer',
          'Flexible Driver',
          'Parent Page Header',
          'Full Width Media',
          'Social Feed',
          'Media Carousel',
          'List',
          'Recommendations Module',
          'Product Detail Hero',
          'Recipe Detail Page Hero',
          'Article Header',
          'Contact & Help Banner',
          'FAQ',
          'Feedback Module',
          'WYSIWYG',
          'NewsLetter Sign Up Form',
        ],
      },
    },

    zone3: {
      name: 'Zone 3 Layout',
      table: {
        category: 'Zone 3 Layout',
      },
      control: {
        type: 'select',
        options: [
          'Standard HP Hero',
          '3-Up HP Hero',
          'Video HP Hero',
          'Basic HP Hero',
          'Freeform Story Block',
          'Product Content Pair Up',
          'Story Highlight',
          'Recipe Feature',
          'Product Feature',
          'Flexible Framer',
          'Flexible Driver',
          'Parent Page Header',
          'Full Width Media',
          'Social Feed',
          'Media Carousel',
          'List',
          'Recommendations Module',
          'Product Detail Hero',
          'Recipe Detail Page Hero',
          'Article Header',
          'Contact & Help Banner',
          'FAQ',
          'Feedback Module',
          'WYSIWYG',
          'NewsLetter Sign Up Form',
        ],
      },
    },

    zone4: {
      name: 'Zone 4 Layout',
      table: {
        category: 'Zone 4 Layout',
      },
      control: {
        type: 'select',
        options: [
          'Standard HP Hero',
          '3-Up HP Hero',
          'Video HP Hero',
          'Basic HP Hero',
          'Freeform Story Block',
          'Product Content Pair Up',
          'Story Highlight',
          'Recipe Feature',
          'Product Feature',
          'Flexible Framer',
          'Flexible Driver',
          'Parent Page Header',
          'Full Width Media',
          'Social Feed',
          'Media Carousel',
          'List',
          'Recommendations Module',
          'Product Detail Hero',
          'Recipe Detail Page Hero',
          'Article Header',
          'Contact & Help Banner',
          'FAQ',
          'Feedback Module',
          'WYSIWYG',
          'NewsLetter Sign Up Form',
        ],
      },
    },

    zone5: {
      name: 'Zone 5 Layout',
      table: {
        category: 'Zone 5 Layout',
      },
      control: {
        type: 'select',
        options: [
          'Standard HP Hero',
          '3-Up HP Hero',
          'Video HP Hero',
          'Basic HP Hero',
          'Freeform Story Block',
          'Product Content Pair Up',
          'Story Highlight',
          'Recipe Feature',
          'Product Feature',
          'Flexible Framer',
          'Flexible Driver',
          'Parent Page Header',
          'Full Width Media',
          'Social Feed',
          'Media Carousel',
          'List',
          'Recommendations Module',
          'Product Detail Hero',
          'Recipe Detail Page Hero',
          'Article Header',
          'Contact & Help Banner',
          'FAQ',
          'Feedback Module',
          'WYSIWYG',
          'NewsLetter Sign Up Form',
        ],
      },
    },

    zone6: {
      name: 'Zone 6 Layout',
      table: {
        category: 'Zone 6 Layout',
      },
      control: {
        type: 'select',
        options: [
          'Standard HP Hero',
          '3-Up HP Hero',
          'Video HP Hero',
          'Basic HP Hero',
          'Freeform Story Block',
          'Product Content Pair Up',
          'Story Highlight',
          'Recipe Feature',
          'Product Feature',
          'Flexible Framer',
          'Flexible Driver',
          'Parent Page Header',
          'Full Width Media',
          'Social Feed',
          'Media Carousel',
          'List',
          'Recommendations Module',
          'Product Detail Hero',
          'Recipe Detail Page Hero',
          'Article Header',
          'Contact & Help Banner',
          'FAQ',
          'Feedback Module',
          'WYSIWYG',
          'NewsLetter Sign Up Form',
        ],
      },
    },

    //Homepage
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
    //Recipe Feature controls
    recipe_Eyebrow: {
      name: 'Eyebrow',
      defaultValue: { summary: 'Recipe' },
      table: {
        category: 'Recipe Feature Component',
      },
      description:
        'Eyebrow text for the recipe feature.<b> Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    recipe_Title: {
      name: 'Recipe title',
      defaultValue: { summary: 'Product ABC ' },
      table: {
        category: 'Recipe Feature Component',
      },
      description:
        'Recipe title for the recipe feature.<b> Maximum character limit is 60.</b>',
      control: { type: 'text' },
    },
    recipe_cta: {
      name: 'Button CTA',
      defaultValue: { summary: 'SEE DETAILS ' },
      table: {
        category: 'Recipe Feature Component',
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
        category: 'Recipe Feature Component',
      },
      description:
        'Recipe image for the recipe.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      control: { type: 'object' },
    },
    recipe_block_title: {
      name: 'Block Title',
      table: {
        category: 'Text',
      },
      description:
        'Block title for the recipe feature.<b> Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    graphic_divider: {
      name: 'Graphic Divider',
      table: {
        category: 'Recipe Feature Component',
      },
      description: 'Graphic divider for the recipe feature',
      control: { type: 'text' },
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
    //Flexible Framer controls
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
    //Social Feed
    social_feed_title: {
      name: 'Title',
      description: 'Title text for the social feed.<b> maximum CC : 55</b>',
      table: {
        category: 'Social Feed Component',
      },
      control: {
        type: 'text',
      },
    },
    social_feed_items: {
      name: 'Items',
      description: 'Item content for the social feed.',
      table: {
        category: 'Social Feed Component',
      },
      control: {
        type: 'object',
      },
    },

    //Media Carousel
    media_carousel_Title: {
      name: 'Title',
      description: 'Title text for the media carousel.<b> maximum CC : 55</b>',
      table: {
        category: 'Media Carousel',
      },
      control: {
        type: 'text',
      },
    },
    media_carousel_Description: {
      name: 'Image/Video Description',
      description:
        'Description text for the media carousel.<b> maximum CC : 255</b>',
      table: {
        category: 'Media Carousel',
      },
      control: {
        type: 'object',
      },
    },
    //List
    title: {
      name: 'Title',
      description: 'Title',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'List Component' },
      control: { type: 'text' },
    },
    Content: {
      name: 'Content',
      description:
        'Maximum number of point that can be added is <b> 9 </b> . List image should be of ratio <b> 16X9 </b>',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'List Component' },
      control: { type: 'object' },
    },
    //PDP hero
    pdp_hero_Content: {
      name: 'Content',
      description:
        'Eyebrow of the PDP page -<b> maximum character limit is 15.</b>.Product name - <b> maximum character limit is 60.</b> Product description- <b> maximum character limit is 300 . </b>',
      defaultValue: { summary: 'LOREM' },
      table: {
        category: 'PDP Hero Component',
      },
      control: { type: 'object' },
    },
    pdp_hero_images: {
      name: 'Images',
      description:
        'Up to 5 Images can be added. Images should include: 1. Key Product Pack Image, 2. Product Open Pack Image, 3. Product Outside of Pack/No Pack Image, 4. & 5. Additional Product images if available',
      table: {
        category: 'PDP Hero Component',
      },
      control: {
        type: 'object',
      },
    },
    pdp_hero_sizes: {
      name: 'Available sizes of product',
      description: 'List down all the sizes of the product - <b>CC: 20 Max</b>',
      table: {
        category: 'PDP Hero Component',
      },
      control: {
        type: 'object',
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
    //Article Header
    article_header_backgroundImage: {
      name: 'Background Image',
      description: 'Background Image for the article header',
      table: {
        category: 'Article Header Component',
      },
      control: {
        type: 'object',
      },
    },
    article_header_eyebrow: {
      name: 'Manual Eyebrow',
      description:
        'Eyebrow text for the article header.<b> maximum CC : 15</b>',
      table: {
        category: 'Article Header Component',
      },
      control: {
        type: 'text',
      },
    },
    article_header_Title: {
      name: 'Title',
      description: 'Title text for the article header.<b> maximum CC : 60</b>',
      table: {
        category: 'Article Header Component',
      },
      control: {
        type: 'text',
      },
    },
    article_header_PublishDate: {
      name: 'Publish Date',
      description:
        'Dynamic follows format: "Published [Shortened month eg. Jun][date of month][year]',
      table: {
        category: 'Article Header Component',
      },
      control: {
        type: 'text',
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
    description: {
      name: 'Description',
      description: 'Text content for the feedback module',
      table: {
        category: 'Feedback Module Component',
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
    //Newsletter form
    newsletter_backgroundColor: {
      name: 'Background Color',
      description: 'Background Color for the form',
      table: {
        category: 'Newsletter Sign Form',
      },
      control: {
        type: 'color',
      },
    },
    newsletter_title: {
      name: 'Title',
      description: 'Title of the form. <b> Maximum character limit is 55 </b>',
      table: {
        category: 'Newsletter Sign Form',
      },
      control: {
        type: 'text',
      },
    },
    newsletter_formInput: {
      name: 'Input Types',
      description:
        'Input types for the form. <ul> <li> For text input feild - the type will be <b>text</b> </li><li>For email address input feild - the type will be <b>email</b></li><li>For phone number inut feild - the type will be <b>number</b></li> For more such input type feilds - please check https://www.w3schools.com/html/html_form_input_types.asp',
      table: {
        category: 'Newsletter Sign Form',
      },
      control: {
        type: 'object',
      },
    },
    newsletter_privacyterms: {
      name: 'Privacy Qoutes',
      description: 'Privacy guidelines of the form',
      table: {
        category: 'Newsletter Sign Form',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const campaignPageLayout = ({
  theme,
  //Header
  headerMenu,
  headerAlertBanner,
  footerMenuItems,
  marketingMessage,
  socialMenuItems,
  legaMenuItems,
  copyrighttext,
  corporateText,
  zone1,
  zone2,
  zone3,
  zone4,
  zone5,
  zone6,
  //homepage hero
  homepageEyebrow,
  homepageTitle,
  basicHomepageTitle,
  homepageButtonCTA,
  hero_images,
  heroVideoUrl,
  blocks,
  //freeform story block
  enableBackgroundColor,
  FreeFormBackgroundColor,
  FreeFormBackgroundImage,
  FreeFormSubHeadingTitle,
  FreeFormHeadingTitle,
  FreeFormContentText,
  FreeFormAlign,
  //product content pair up
  ContentTitle,
  ContentEyebrowText,
  ContentBackground,
  content_card_eyebrow,
  content_card_item,
  //Story highlight
  StoryHighlightTitle,
  StoryHighlightParagraphContent,
  StoryHighlightButtonCTA,
  StoryHighlightImageAsset1,
  StoryHighlightImageAsset2,
  StoryHighlightitems,
  // recipe feature
  RecipeTitle,
  recipe_media,
  recipe_title,
  graphic_divider,
  //Product feature
  ProductEyebrow,
  ProductTitle,
  ProductBackground,
  ProductImage,
  ProductExploreCTA,
  //flexible driver
  DriverTitle,
  DriverDescription,
  DriverButtonCTA,
  DriverLeftImage,
  DriverRightImage,
  //Flexible Framer
  framer_Title,
  framer_items,
  //parent page header
  Eyebrow,
  Title,
  Description,
  BackgroundTheme,
  parent_page_media_entities,
  //Full Width Media
  heading,
  media,
  content,
  //Social Feed
  social_feed_title,
  social_feed_items,
  //Media Carousel
  media_carousel_Title,
  media_carousel_Description,
  //List
  title,
  Content,
  //PDP Hero
  pdp_hero_Content,
  pdp_hero_images,
  pdp_hero_sizes,
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
  //Article Header
  article_header_backgroundImage,
  article_header_eyebrow,
  article_header_Title,
  article_header_PublishDate,
  //Contact Help Banner
  contact_Title,
  contact_Description,
  contact_callCTA,
  contact_emailCTA,
  contact_social_heading,
  //Faq
  faq_title,
  faq_searchLinks,
  faq_faqLists,
  //Feedback Module
  brandShape,
  description,
  standardHeading,
  standardChoices,
  // WYSIWYG
  WYSIWYG_Header,
  WYSIWYG_body,
  //Newsletter Sign Form
  newsletter_backgroundColor,
  newsletter_title,
  newsletter_formInput,
  newsletter_privacyterms,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  recommendationModuleData.recommended_items = [
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
  ];
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
        __html: campaignTwig({
          ...footerSocial,
          ...footerMenu,
          ...secondaryMenuData,
          ...inlineSearchData,
          ...mainMenuData,
          ...legalLinksData,
          ...siteHeaderData,
          ...siteFooterData,
          ...campaignData,

          ...homeHero3upData,
          ...homeHeroBasicData,
          ...homeHeroStandardData,
          ...homeHeroVideoData,
          ...freeformData,
          ...homeProductContentData,
          ...storyHighlight,
          ...recpieFeature,
          ...productFeatureData,
          ...flexibleDriverData,
          ...flexibleFramerData,
          ...parentPageHeaderData,
          ...fullWidthMediaData,
          ...socialFeedData,
          ...mediaCarouselData,
          ...listData,
          ...recommendationModuleData,
          ...pdpHeroData,
          ...recipeHeroModuleVideoData,
          ...articleHeaderImageData,
          ...contactHelpBannerData,
          ...faqListData,
          ...feedbackData,
          ...newsletterSignUpFormData,

          theme_styles: theme,

          menu_items: headerMenu,
          alert_banner: headerAlertBanner,

          footer_menu_items: footerMenuItems,
          marketing_text: marketingMessage,
          social_menu_items: socialMenuItems,
          legal_links_menu_items: legaMenuItems,
          copyright_text: copyrighttext,
          corporate_tout_text: corporateText,

          zone1: zone1,
          zone2: zone2,
          zone3: zone3,
          zone4: zone4,
          zone5: zone5,
          zone6: zone6,

          //Homepage Hero
          eyebrow: homepageEyebrow,
          title_label: homepageTitle,
          homepage_hero_basic_content: basicHomepageTitle,
          cta_title: homepageButtonCTA,
          hero_images: hero_images,
          video__src__url: heroVideoUrl,
          blocks: blocks,

          //freeform story
          use_custom_color: enableBackgroundColor,
          custom_background_color: FreeFormBackgroundColor,
          freeform_story_img_src: FreeFormBackgroundImage,
          freeform_story_header_1: FreeFormSubHeadingTitle,
          freeform_story_header_2: FreeFormHeadingTitle,
          freeform_story_paragraph_content: FreeFormContentText,
          freeform_story_align: FreeFormAlign,

          //content product pair up
          lead_card_title: ContentTitle,
          lead_card_eyebrow: ContentEyebrowText,
          background: ContentBackground,
          card__eyebrow: content_card_eyebrow,
          item: content_card_item,

          //story highlight
          heading: StoryHighlightTitle,
          story_highlight_paragraph_content: StoryHighlightParagraphContent,
          story_highlight_button_text: StoryHighlightButtonCTA,
          asset_2: StoryHighlightImageAsset1,
          asset_3: StoryHighlightImageAsset2,
          storybook_story_highlight_items: StoryHighlightitems,

          //recipe feature
          title: RecipeTitle,
          recipe_media,

          //product feature
          eyebrow_text: ProductEyebrow,
          storybook_product_feature_heading: ProductTitle,
          storybook_product_feature_background_color: ProductBackground,
          image_src: ProductImage,
          default_link_content: ProductExploreCTA,

          //flexible-driver
          flexible_driver_heading: DriverTitle,
          flexible_driver_text: DriverDescription,
          flexible_driver_button_text: DriverButtonCTA,
          storybook_flexible_driver_asset_1: DriverLeftImage,
          storybook_flexible_driver_asset_2: DriverRightImage,

          //flexible framer
          grid_label: framer_Title,
          storybook_flexible_framer_items: framer_items,

          //parent page header
          pp_header_eyebrow_text: Eyebrow,
          pp_header_heading: Title,
          pp_header_paragraph_content: Description,
          parent_page_media_type: BackgroundTheme,
          parent_page_media_entities: parent_page_media_entities,

          //Full Width Media
          storybook_full_width_heading: heading,
          media: media,
          storybook_full_width_content: content,

          //Social Feed
          storybook_social_feed_title: social_feed_title,
          storybook_social_feed_items: social_feed_items,

          //Media carousel
          storybook_media_carousel_heading: media_carousel_Title,
          storybook_media_carousel_items: media_carousel_Description,

          //List
          storybook_list_title: title,
          takeaways_list: Content,

          //PDP Hero
          pdp_common_hero_data: pdp_hero_Content,
          pdp_hero_data: pdp_hero_images,
          pdp_size_items_data: pdp_hero_sizes,

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

          //Article Header
          images: article_header_backgroundImage,
          eyebrow: article_header_eyebrow,
          storybook_article_header_heading: article_header_Title,
          date_published: article_header_PublishDate,

          //Contact Help Banner
          contact_module_heading: contact_Title,
          contact_module_paragraph_content: contact_Description,
          contact_module_contact_phone: contact_callCTA,
          contact_module_contact_email_text: contact_emailCTA,
          contact_module_social_heading:contact_social_heading,

          //Faq List
          title: faq_title,
          facetLinks: faq_searchLinks,
          faq_items: faq_faqLists,

          //Feedback Module
          brand_shape: brandShape,
          feedback_paragraph_content: description,
          feedback_heading: standardHeading,
          choices: standardChoices,

          //Wysiwyg
          storybook_wysiwyg_heading: WYSIWYG_Header,
          content: WYSIWYG_body,

          //Newsletter Sign Form
          newsletter_form_background_color: newsletter_backgroundColor,
          webform_block_label: newsletter_title,
          forms: newsletter_formInput,
          privacyfeilds: newsletter_privacyterms,
        }),
      }}
    />
  );
};
campaignPageLayout.args = {
  theme: campaignData.theme_styles,
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

  zone1: campaignData.zone1,
  zone2: campaignData.zone2,
  zone3: campaignData.zone3,
  zone4: campaignData.zone4,
  zone5: campaignData.zone5,
  zone6: campaignData.zone6,

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

  /* Freeform Story component */

  enableBackgroundColor: freeformData.use_custom_color,
  FreeFormBackgroundColor: freeformData.custom_background_color,
  FreeFormBackgroundImage: freeformData.freeform_story_img_src,
  FreeFormSubHeadingTitle: freeformData.freeform_story_header_1,
  FreeFormHeadingTitle: freeformData.freeform_story_header_2,
  FreeFormContentText: freeformData.freeform_story_paragraph_content,
  FreeFormAlign: freeformData.freeform_story_align,

  /* Product Content pair up component */
  ContentTitle: homeProductContentData.lead_card_title,
  ContentEyebrowText: homeProductContentData.lead_card_eyebrow,
  ContentBackground: homeProductContentData.background,
  content_card_eyebrow: 'MADE WITH',
  content_card_item: productCardData.item,

  /* Story Highlight component */

  StoryHighlightTitle: storyHighlight.heading,
  StoryHighlightParagraphContent:
    storyHighlight.story_highlight_paragraph_content,
  StoryHighlightButtonCTA: storyHighlight.story_highlight_button_text,
  StoryHighlightImageAsset1: storyHighlight.asset_2,
  StoryHighlightImageAsset2: storyHighlight.asset_3,
  StoryHighlightitems: storyHighlight.storybook_story_highlight_items,

  /* Recipe Feature component */
  RecipeTitle: recpieFeature.title,
  recipe_media: recpieFeature.recipe_media,

  /* Product Feature component */
  ProductEyebrow: productFeatureData.eyebrow_text,
  ProductTitle: productFeatureData.storybook_product_feature_heading,
  ProductBackground:
    productFeatureData.storybook_product_feature_background_color,
  ProductImage: productFeatureData.image_src,
  ProductExploreCTA: productFeatureData.default_link_content,

  //flexible framer
  framer_Title: flexibleFramerData.grid_label,
  framer_items: flexibleFramerData.storybook_flexible_framer_items,

  /* Flexible Driver component */
  DriverTitle: flexibleDriverData.flexible_driver_heading,
  DriverDescription: flexibleDriverData.flexible_driver_text,
  DriverButtonCTA: flexibleDriverData.flexible_driver_button_text,
  DriverLeftImage: flexibleDriverData.storybook_flexible_driver_asset_1,
  DriverRightImage: flexibleDriverData.storybook_flexible_driver_asset_2,

  // For Parent page header
  Eyebrow: parentPageHeaderData.pp_header_eyebrow_text,
  Title: parentPageHeaderData.pp_header_heading,
  Description: parentPageHeaderData.pp_header_paragraph_content,
  BackgroundTheme: parentPageHeaderData.parent_page_media_type,
  parent_page_media_entities: parentPageHeaderData.parent_page_media_entities,

  /* Full Width Media */
  heading: fullWidthMediaData.storybook_full_width_heading,
  media: fullWidthMediaData.media,
  content: fullWidthMediaData.storybook_full_width_content,

  // Social Feed
  social_feed_title: socialFeedData.storybook_social_feed_title,
  social_feed_items: socialFeedData.storybook_social_feed_items,

  //Media Carousel
  media_carousel_Title: mediaCarouselData.storybook_media_carousel_heading,
  media_carousel_Description: mediaCarouselData.storybook_media_carousel_items,

  /* List */
  title: listData.storybook_list_title,
  Content: listData.takeaways_list,

  //For PDP Hero
  pdp_hero_Content: pdpHeroData.pdp_common_hero_data,
  pdp_hero_images: pdpHeroData.pdp_hero_data,
  pdp_hero_sizes: pdpHeroData.pdp_size_items_data,

  //Recipe Hero Module
  recipe_hero_backgroundColorEnable:
    recipeHeroModuleVideoData.background_color_override,
  recipe_hero_backgroundColor:
    recipeHeroModuleVideoData.recipe_hero_module_background_color,
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

  //For article Header
  article_header_backgroundImage: articleHeaderImageData.images,
  article_header_eyebrow: articleHeaderImageData.eyebrow,
  article_header_Title: articleHeaderImageData.storybook_article_header_heading,
  article_header_PublishDate: articleHeaderImageData.date_published,

  //Contact Help Banner
  contact_Title: contactHelpBannerData.contact_module_heading,
  contact_Description: contactHelpBannerData.contact_module_paragraph_content,
  contact_callCTA: contactHelpBannerData.contact_module_contact_phone,
  contact_emailCTA: contactHelpBannerData.contact_module_contact_email_text,
  contact_social_heading:contactModuleData.contact_module_social_heading,


  //For Faq List
  faq_title: faqListData.title,
  faq_searchLinks: faqListData.facetLinks,
  faq_faqLists: faqListData.faq_items,

  /* Feedback Module */
  brandShape: feedbackData.brand_shape,
  standardHeading: feedbackData.feedback_heading,
  description: feedbackData.feedback_paragraph_content,
  standardChoices: feedbackData.choices,

  //Wysiwyg
  WYSIWYG_Header: WYSIWYGData.storybook_wysiwyg_heading,
  WYSIWYG_body: WYSIWYGData.content,

  //Newsletter Form
  newsletter_backgroundColor:
    newsletterSignUpFormData.newsletter_form_background_color,
  newsletter_title: newsletterSignUpFormData.webform_block_label,
  newsletter_formInput: newsletterSignUpFormData.forms,
  newsletter_privacyterms: newsletterSignUpFormData.privacyfeilds,
};
