import React from 'react';
import socialFeed from './social-feed.twig';
import socialFeedData from './social-feed.yml';
import { useEffect } from '@storybook/client-api';
import './social-feed';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Social feed' };

export const socialFeedModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: socialFeed(socialFeedData) }} />
};