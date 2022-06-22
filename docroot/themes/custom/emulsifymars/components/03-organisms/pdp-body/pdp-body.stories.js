import React from 'react';

import pdpBodyTwig from './pdp-body.twig';
import pdpBodyData from './pdp-body.yml';
import './pdp-body';
import { useEffect } from '@storybook/client-api';

/**
 * Storybook Definition.
 */
// export default { title: 'Organisms/PDP Body' };

export const pdpBodyTemplate = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: pdpBodyTwig({ ...pdpBodyData }) }} />;
};
