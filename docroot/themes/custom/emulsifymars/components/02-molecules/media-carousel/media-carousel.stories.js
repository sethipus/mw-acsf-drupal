import React from 'react';
import mediaCarousel from './media-carousel.twig';
import mediaCarouselData from './media-carousel.yml';
import { useEffect } from '@storybook/client-api';
import './media-carousel';

/**
 * Storybook Definition.
 */
export default { title: 'Components/[ML 19] Media Carousel ' };

export const mediaCarouselModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: mediaCarousel(mediaCarouselData) }} />
};
