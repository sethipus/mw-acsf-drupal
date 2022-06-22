import React from 'react';

import landingTwig from './landing-page.twig';
import landingData from './landing-page.yml';

import landingHeaderData from '../../02-molecules/parent-page-header/parent-page-header.yml';
import landingMediaData from '../../02-molecules/article-media/full-width/full-width-media.yml';
import landingPollData from '../../02-molecules/polls/poll.yml';
import contentFeatureData from '../../02-molecules/content-feature/content-feature.yml';
import landingCarouselData from '../../02-molecules/media-carousel/media-carousel.yml';

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

// export default { title: 'Pages/Landing'};

export const landing = () => {
    useEffect(() => Drupal.attachBehaviors(), []);
    return <div dangerouslySetInnerHTML={{
        __html: landingTwig({
            parent_page_media_url: '/content-feature-bg.png', 
            parent_page_media_type: 'image',
            ...landingHeaderData,
            ...landingMediaData,
            ...landingPollData,
            ...contentFeatureData,
            ...landingCarouselData,
            ...footerSocial,
            ...footerMenu,
            ...secondaryMenuData,
            ...inlineSearchData,
            ...mainMenuData,
            ...legalLinksData,
            ...siteHeaderData,
            ...siteFooterData,
            ...landingData
        })
      }}/>
}
