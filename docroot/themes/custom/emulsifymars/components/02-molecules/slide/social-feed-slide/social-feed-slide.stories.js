import React, { useEffect } from 'react';
import slide from './social-feed-slide.twig';
import slideData from './social-feed-slide.yml';
import './social-feed-slide';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Slide/Social Feed Slide' };

export const slideExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: slide(slideData) }} />
};
