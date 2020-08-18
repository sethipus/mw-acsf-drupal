import React from 'react';
import entryGate from './entry-gate.twig';
import entryGateData from './entry-gate.yml';
import { useEffect } from '@storybook/client-api';
import './entry-gate';

export default { title: 'Molecules/Entry Gate' };

export const entryGateExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: entryGate(entryGateData) }} />
};
