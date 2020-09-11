import React from 'react';
import storyHighlight from './story_highlight.twig';
import storyHighlightData from './story_highlight.yml';
import { useEffect } from '@storybook/client-api';
import './story_highlight';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Story highlight' };

export const storyHighlightModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: storyHighlight(storyHighlightData) }} />
};
