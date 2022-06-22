import React from 'react';
import mediaCarouselFullWidth from './media-carousel-fullwidth.twig';
import mediaCarouselFullWidthData from './media-carousel-fullwidth.yml';
import { useEffect } from '@storybook/client-api';

export default { title: 'Components/[ML 34] Fullwidth Media Carousel'};

export const mediaCarouselFullWidthModule = () => {
    useEffect(() => Drupal.attachBehaviors(), []);
    return <div dangerouslySetInnerHTML={{ __html: mediaCarouselFullWidth(mediaCarouselFullWidthData) }} />
  };