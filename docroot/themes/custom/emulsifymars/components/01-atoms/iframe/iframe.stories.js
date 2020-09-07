import React from 'react';
import { useEffect } from '@storybook/client-api';
import iframe from './iframe.twig';
import iframeData from './iframe.yml';
import './iframe';

export default { title: 'Atoms/iFrame' };

export const iframeExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: iframe(iframeData) }} />
};
