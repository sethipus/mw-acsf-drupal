import React from 'react';
import ReactDOMServer from 'react-dom/server';
import { useEffect } from '@storybook/client-api';

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


import { header, footer } from '../../03-organisms/site/site.stories.js';
import { searchResultsModule } from '../../03-organisms/search/search-results/search-results.stories';
import { searchPageHeaderModule } from '../../02-molecules/search-page-header/search-page-header.stories';
import searchTwig from './search-page.twig';
import searchData from './search-page.yml';

export default {
  title: 'Pages/[PT 13] Search Results',
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
    regions: {
      name: 'Regions',
      description: 'Regions link - <b>Max CC: 30 </b>',
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
  },
};

export const searchResultPageLayout = ({
  theme,
  headerMenu,
  headerAlertBanner,
  footerMenuItems,
  marketingMessage,
  socialMenuItems,
  legaMenuItems,
  regions,
  copyrighttext,
  corporateText,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);

  // const components = [
  //   ReactDOMServer.renderToString(header()),
  //   ReactDOMServer.renderToString(searchPageHeaderModule()),
  //   ReactDOMServer.renderToString(searchResultsModule()),
  //   ReactDOMServer.renderToString(footer())
  // ];

  return (
    <div
      dangerouslySetInnerHTML={{
        __html: searchTwig({
          ...footerSocial,
          ...footerMenu,
          ...secondaryMenuData,
          ...inlineSearchData,
          ...mainMenuData,
          ...legalLinksData,
          ...siteHeaderData,
          ...siteFooterData,
          ...searchData,

          theme_styles: theme,
  
          menu_items: headerMenu,
          alert_banner: headerAlertBanner,

          footer_menu_items: footerMenuItems,
          marketing_text: marketingMessage,
          social_menu_items: socialMenuItems,
          legal_links_menu_items: legaMenuItems,
          regions: regions,
          copyright_text: copyrighttext,
          corporate_tout_text: corporateText,
        }),
      }}
    />
  );
};
searchResultPageLayout.args = {
  theme: searchData.theme_styles,
  //For Header
  headerMenu: mainMenuData.menu_items,
  headerAlertBanner: siteHeaderData.alert_banner,
  //For Footer
  footerMenuItems: footerMenu.footer_menu_items,
  marketingMessage: siteFooterData.marketing_text,
  socialMenuItems: footerSocial.social_menu_items,
  legaMenuItems: legalLinksData.legal_links_menu_items,
  regions: siteFooterData.regions,
  copyrighttext: siteFooterData.copyright_text,
  corporateText: siteFooterData.corporate_tout_text,
}