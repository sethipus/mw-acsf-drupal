import React from 'react';
import { useEffect } from '@storybook/client-api';

import footerTwig from './site-footer.twig';
import footerlogoTwig from '../../../01-atoms/images/image/_footer_logo.twig';

import footerSocial from '../../../02-molecules/menus/social/social-menu.yml';
import footerMenu from '../../../02-molecules/menus/footer/footer-menu.yml';

import legalLinksData from '../../../02-molecules/menus/legal-links/legal-links-menu.yml';

import siteFooterData from './site-footer.yml';

import '../../../02-molecules/menus/main-menu/main-menu';
import '../../../02-molecules/dropdown/dropdown';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[GE 04] Header & Footer / Footer',
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
    footerLogo: {
      name: 'Logo',
      descritpion:
        'Logo Size - <b>For 1440 — 212 x 98, 768 — 141 x 53, 375 — 104 x 39 </b>',
      table: {
        category: 'Footer Section',
      },
      control: {
        type: 'object',
      },
    },
    footerMenuItems: {
      name: 'Menu Items',
      descritpion:
        'Menu Items for the footer section. <b> Contact & Help, About, Where to Buy - Maintains Max CC: 25 </b>',
      table: {
        category: 'Footer Section',
      },
      control: {
        type: 'object',
      },
    },
    marketingMessage: {
      name: 'Marketing & Copyright Message',
      description: ' Message for the marketing and copyright',
      table: {
        category: 'Footer Section',
      },
      control: {
        type: 'object',
      },
    },
    socialMenuItems: {
      name: 'Social Follow',
      description: 'Content for the social menu icons',
      table: {
        category: 'Footer Section',
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
        category: 'Footer Section',
      },
      control: {
        type: 'object',
      },
    },
    copyrighttext: {
      name: 'Copyright Text',
      table: {
        category: 'Footer Section',
      },
      control: {
        type: 'text',
      },
    },
    corporateText: {
      name: 'Corporate Text',
      table: {
        category: 'Footer Section',
      },
      control: {
        type: 'text',
      },
    },
  },
};

export const footerLayout = ({
  theme,
  footerLogo,
  footerMenuItems,
  marketingMessage,
  socialMenuItems,
  legaMenuItems,
  copyrighttext,
  corporateText,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: footerTwig({
          ...footerSocial,
          ...footerMenu,
          ...siteFooterData,
          ...legalLinksData,

          theme_styles: theme,
          logo_src: footerLogo,
          footer_menu_items: footerMenuItems,
          marketing_text: marketingMessage,
          social_menu_items: socialMenuItems,
          legal_links_menu_items: legaMenuItems,
          copyright_text: copyrighttext,
          corporate_tout_text: corporateText,
        }),
      }}
    />
  );
};
footerLayout.args = {
  theme: siteFooterData.theme_styles,
  footerLogo: siteFooterData.logo_src,
  footerMenuItems: footerMenu.footer_menu_items,
  marketingMessage: siteFooterData.marketing_text,
  socialMenuItems: footerSocial.social_menu_items,
  legaMenuItems: legalLinksData.legal_links_menu_items,
  copyrighttext: siteFooterData.copyright_text,
  corporateText: siteFooterData.corporate_tout_text,
};
