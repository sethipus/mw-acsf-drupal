import React, { useEffect } from 'react';
import slide from './slide.twig';
import slideData from './slide.yml';
import './slide';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Slide' };

export const slideExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: slide(slideData) }} />
};