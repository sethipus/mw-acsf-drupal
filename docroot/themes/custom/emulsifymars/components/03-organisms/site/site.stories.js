import React from 'react';
import { useEffect } from '@storybook/client-api';

import footerTwig from './site-footer/site-footer.twig';
import siteHeader from './site-header/site-header.twig';

import footerSocial from '../../02-molecules/menus/social/social-menu.yml';
import footerMenu from '../../02-molecules/menus/footer/footer-menu.yml';
import secondaryMenuData from '../../02-molecules/menus/inline/inline-menu-header.yml';
import mainMenuData from '../../02-molecules/menus/main-menu/main-menu.yml';

import secondaryFooterMenu from '../../02-molecules/menus/secondary-footer/secondary-footer-menu.twig';
import secondaryFooterMenuData from '../../02-molecules/menus/secondary-footer/secondary-footer-menu.yml';

import siteFooterData from './site-footer/site-footer.yml';

import '../../02-molecules/menus/main-menu/main-menu';

/**
 * Storybook Definition.
 */
export default { title: 'Organisms/Site' };

export const footer = () => (
  <div
    dangerouslySetInnerHTML={{
      __html: footerTwig({ ...footerSocial, ...footerMenu, ...siteFooterData, ...secondaryFooterMenu, secondaryFooterMenuData }),
    }}
  />
);
export const header = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: siteHeader({
          ...mainMenuData,
          ...secondaryMenuData,
        }),
      }}
    />
  );
};
