import React from 'react';

import faqList from './faq-list.twig';

import faqListData from './faq-list.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/FAQ' };

export const faqListExample = () => (
  <div dangerouslySetInnerHTML={{ __html: faqList(faqListData) }} />
);
