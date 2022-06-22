import React from 'react';

import errorTwig from './error-page.twig';
import errorData from './error-page.yml';

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

import { useEffect } from '@storybook/client-api';

export default {
  title: 'Pages/[PT 12] Error Page',
  parameters: {
    componentSubtitle: `Error pages appear when users click/tap
     on a page that is broken, not linked, or malfunctioning.
    This template can be used for error page, namely 404 errors.`,
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
    title: {
      name: 'Title',
      description:
        'Title of the error page. <b>Maximum character limit is 55</b>',
      table: {
        category: 'Error Component',
      },
      control: {
        type: 'text',
      },
    },
    content: {
      name: 'Body Copy',
      description:
        'Description of the error page. <b>Maximum character limit is 100</b>',
      table: {
        category: 'Error Component',
      },
      control: {
        type: 'text',
      },
    },
    bgImage: {
      name: 'Background Image',
      description: `Bg Image of the error page.<b>trongly advise to not upload
            a portrait image in this spot, and only landscape. If the 
            scenario of a portrait image were to occur, it should be put
            inside the box and centered so it would have 542px height
            but will not be full width. 
            It should scale proportionally</b>`,
      table: {
        category: 'Error Component',
      },
      control: {
        type: 'object',
      },
    },
    error_component_links: {
      name: 'Navigate options',
      description: `Navigation option in the error page.`,
      table: {
        category: 'Error Component',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const errorPageLayout = ({
  title,
  content,
  bgImage,
  error_component_links,
  theme,
  headerMenu,
  headerAlertBanner,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: errorTwig(
          
          {
            ...footerSocial,
            ...footerMenu,
            ...secondaryMenuData,
            ...inlineSearchData,
            ...mainMenuData,
            ...legalLinksData,
            ...siteHeaderData,
            ...siteFooterData,
            ...errorData,


            error_component_heading: title,
            error_component_paragraph_content: content,
            error_component_bg_image_src: bgImage,
            error_component_links: error_component_links,

            theme_styles: theme,
            menu_items: headerMenu,
            alert_banner: headerAlertBanner,
          },
        ),
      }}
    />
  );
};
errorPageLayout.args = {

  theme: errorData.theme_styles,
  //For Header
  headerMenu: mainMenuData.menu_items,
  headerAlertBanner: siteHeaderData.alert_banner,
  //For Error Component
  title: errorData.error_component_heading,
  content: errorData.error_component_paragraph_content,
  bgImage: errorData.error_component_bg_image_src,
  error_component_links: errorData.error_component_links,
};
