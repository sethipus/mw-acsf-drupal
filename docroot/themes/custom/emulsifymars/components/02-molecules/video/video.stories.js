import React from 'react';

import { useEffect } from '@storybook/client-api';
import video from './video.twig';

import './video';

import videoData from './video.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Video' };

export const videoWithBackground = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: video({ ...videoData }) }} />;
};
