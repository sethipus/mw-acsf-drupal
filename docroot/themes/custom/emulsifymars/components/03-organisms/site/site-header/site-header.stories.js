import React from 'react';
import { useEffect } from '@storybook/client-api';

import siteHeader from './site-header.twig';
import secondaryMenuData from '../../../02-molecules/menus/inline/header-inline-menu/header-inline-menu.yml';
import inlineSearchData from '../../../02-molecules/search/inline-search/inline-search.yml';
import mainMenuData from '../../../02-molecules/menus/main-menu/main-menu.yml';

import siteHeaderData from './site-header.yml';
import '../../../02-molecules/menus/main-menu/main-menu';
import '../../../02-molecules/dropdown/dropdown';

/**
 * Storybook Definition.
 */
export default {
    title: 'Components/[GE 04] Header & Footer / Header',
  argTypes: {
    theme: {
      name: 'Theme',
      description: 'Theme for the card',
      defaultValue: {
        summary: 'Twix',
      },
      table: {
        category: 'Theme',
      },
      control: {
        type: 'select',
        options: ['twix', 'dove', 'mars', 'galaxy'],
      },
    },
    headerLogo: {
      name: 'Header Logo',
      descritpion:
        'Logo Size - <b>For 1440 — 212 x 98, 768 — 141 x 53, 375 — 104 x 39 </b>',
      table: {
        category: 'Header Section',
      },
      control: {
        type: 'object',
      },
    },

    menu_items: {
      name: 'Header Menu Items',
      table: {
        category: 'Header Section',
      },
      control: {
        type: 'object',
      },
    },
    banner: {
      name: 'Alert Banner',
      table: {
        category: 'Header Section',
      },
      control: {
        type: 'text',
      },
    },
  },
};

export const headerLayout = ({ menu_items, headerLogo, theme, banner }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: siteHeader({
          ...mainMenuData,
          ...secondaryMenuData,
          ...inlineSearchData,
          ...siteHeaderData,

          menu_items: menu_items,
          logo_src: headerLogo,
          theme_styles: theme,
          alert_banner: banner,
        }),
      }}
    />
  );
};
headerLayout.args = {
  theme: siteHeaderData.theme_styles,
  headerLogo: siteHeaderData.logo_src,
  menu_items: mainMenuData.menu_items,
  banner: siteHeaderData.alert_banner,
};
