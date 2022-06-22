import React from 'react';

import aboutUsTemplateTwig from './about-us-template.twig';
import aboutUsTemplateData from './about-us-template.yml';
import aboutUsHeaderData from '../../02-molecules/parent-page-header/parent-page-header.yml';
import freeformStoryData from '../../02-molecules/freeform-story/freeform-story-left.yml';
import pollsData from '../../02-molecules/polls/poll.yml';
import ContactModuleData from '../../02-molecules/contact-module/contact-module.yml';

import { useEffect } from '@storybook/client-api';

// export default { title: 'Templates/About Us Template'};

export const aboutUsTemplate = () => {
    useEffect(() => Drupal.attachBehaviors(), []);
    return <div dangerouslySetInnerHTML={{
        __html: aboutUsTemplateTwig({
            parent_page_media_entities: {
              desktop : { src: '/content-feature-bg.png'},
              tablet : { src: '/content-feature-bg.png'},
              mobile : { src: '/content-feature-bg.png'}
            },
            parent_page_media_type: 'image',
          ...aboutUsHeaderData,
          ...freeformStoryData,
          ...pollsData,
          ...ContactModuleData,
          ...aboutUsTemplateData
        })
      }}/>
}
