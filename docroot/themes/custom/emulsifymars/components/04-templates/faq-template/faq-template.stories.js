import React from 'react';

import faqTemplateTwig from './faq-template.twig';
import faqTemplateData from './faq-template.yml';
import faqHeaderData from '../../02-molecules/parent-page-header/parent-page-header.yml';
import faqModuleData from '../../02-molecules/faq-list/faq-list.yml';
import ContactModuleData from '../../02-molecules/contact-module/contact-module.yml';

import { useEffect } from '@storybook/client-api';

// export default { title: 'Templates/FAQ Template'};

export const faqTemplate = () => {
    useEffect(() => Drupal.attachBehaviors(), []);
    return <div dangerouslySetInnerHTML={{
        __html: faqTemplateTwig({
            parent_page_media_url: '/content-feature-bg.png', 
            parent_page_media_type: 'image',
          ...faqHeaderData,
          ...faqModuleData,
          ...ContactModuleData,
          ...faqTemplateData
        })
      }}/>
}
