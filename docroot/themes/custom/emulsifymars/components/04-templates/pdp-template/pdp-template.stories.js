import React from 'react';

import pdpTemplateTwig from './pdp-template.twig';
import pdpTemplateData from './pdp-template.yml';
import pdpBodyModuleData from '../../03-organisms/pdp-body/pdp-body.yml';
import '../../03-organisms/pdp-body/pdp-body';
import { useEffect } from '@storybook/client-api';

/**
 * Storybook Definition.
 */
// export default { title: 'Templates/PDP Template' };

export const pdpTemplate = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: pdpTemplateTwig({
      ...pdpTemplateData,
      ...pdpBodyModuleData,
    }) }} />
  };


