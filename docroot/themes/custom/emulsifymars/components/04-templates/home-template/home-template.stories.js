import React from 'react';

import homeTwig from './home-template.twig';
import homeData from './home-template.yml';
import homeHeroData from '../../02-molecules/homepage-hero/3up/homepage-hero-3up.yml';
import homeProductContentData from '../../02-molecules/product-content-pair-up/product-content-pair-up.yml';
import homePollData from '../../02-molecules/polls/poll.yml';
import contentFeatureData from '../../02-molecules/content-feature/content-feature.yml';
import homeCarouselData from '../../02-molecules/media-carousel/media-carousel.yml';

import { useEffect } from '@storybook/client-api';

import '../../02-molecules/content-feature/content-feature';

// export default { title: 'Templates/Home Template'};

export const home = () => {
    useEffect(() => Drupal.attachBehaviors(), []);
    return <div dangerouslySetInnerHTML={{
        __html: homeTwig({
            ...homeHeroData,
            ...homeProductContentData,
            ...homePollData,
            ...contentFeatureData,
            ...homeCarouselData,
            ...homeData
        })
      }}/>
}
