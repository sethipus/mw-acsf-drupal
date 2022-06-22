import React from 'react';

import articleTwig from './article-page.twig';
import articleData from './article-page.yml';

import articleHeaderImageData from '../../02-molecules/article-header/article-header-image.yml';
import articleMediaData from '../../02-molecules/article-media/full-width/full-width-media.yml';
import articleInlineMediaData from '../../02-molecules/article-media/inline/inline-media.yml';

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

// export default { title: 'Pages/Article'};

export const article = () => {
    useEffect(() => Drupal.attachBehaviors(), []);
    return <div dangerouslySetInnerHTML={{
        __html: articleTwig({
            ...articleHeaderImageData,
            ...articleMediaData,
            ...articleInlineMediaData,
            ...footerSocial,
            ...footerMenu,
            ...secondaryMenuData,
            ...inlineSearchData,
            ...mainMenuData,
            ...legalLinksData,
            ...siteHeaderData,
            ...siteFooterData,
            ...articleData
        })
      }}/>
}
