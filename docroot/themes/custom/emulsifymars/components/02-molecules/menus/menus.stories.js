import React from 'react';
import { useEffect } from '@storybook/client-api';

import breadcrumb from './breadcrumbs/breadcrumbs.twig';
import inlineMenu from './inline/inline-menu/inline-menu.twig';
import inlineHeaderMenu from './inline/header-inline-menu/header-inline-menu.twig';
import footerMenu from './footer/footer-menu.twig';
import legalLinksMenu from './legal-links/legal-links-menu.twig';
import mainMenu from './main-menu/main-menu.twig';
import socialMenu from './social/social-menu.twig';

import breadcrumbsData from './breadcrumbs/breadcrumbs.yml';
import inlineMenuData from './inline/inline-menu/inline-menu.yml';
import inlineHeaderMenuData from './inline/header-inline-menu/header-inline-menu.yml';
import footerMenuData from './footer/footer-menu.yml';
import legalLinksMenuData from './legal-links/legal-links-menu.yml';
import mainMenuData from './main-menu/main-menu.yml';
import socialMenuData from './social/social-menu.yml';

import './main-menu/main-menu';
import './social/social-menu';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Menus' };

export const breadcrumbs = () => (
  <div dangerouslySetInnerHTML={{ __html: breadcrumb(breadcrumbsData) }} />
);
export const inline = () => (
  <div dangerouslySetInnerHTML={{ __html: inlineMenu(inlineMenuData) }} />
);
export const inline_header = () => (
  <div dangerouslySetInnerHTML={{ __html: inlineHeaderMenu(inlineHeaderMenuData) }} />
);
export const footer = () => (
  <div dangerouslySetInnerHTML={{ __html: footerMenu(footerMenuData) }} />
);
export const legalLinks = () => (
  <div dangerouslySetInnerHTML={{ __html: legalLinksMenu(legalLinksMenuData) }} />
);
export const main = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: mainMenu(mainMenuData) }} />;
};
export const social = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: socialMenu(socialMenuData) }} />
};
