import React, { useEffect } from 'react';
import slide from './pdp-hero-slide.twig';
import slideData from './pdp-hero-slide.yml';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Slide/PDP Hero Slide' };

export const slideExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: slide(slideData) }} />
};
