import React from 'react';

import landingTwig from './landing-template.twig';
import landingData from './landing-template.yml';
import landingHeaderData from '../../02-molecules/parent-page-header/parent-page-header.yml';
import landingMediaData from '../../02-molecules/article-media/full-width/full-width-media.yml';
import landingPollData from '../../02-molecules/polls/poll.yml';
import contentFeatureData from '../../02-molecules/content-feature/content-feature.yml';
import landingCarouselData from '../../02-molecules/media-carousel/media-carousel.yml';

import { useEffect } from '@storybook/client-api';

// export default { title: 'Templates/Landing Template'};

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
            ...landingData
        })
      }}/>
}
