import React from 'react';

import faqFiltersTwig from './faq-filters.twig';
import faqFiltersData from './faq-filters.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/FAQ' };

export const faqFiltersExample = () => (
  <div dangerouslySetInnerHTML={{ __html: faqFiltersTwig(faqFiltersData) }} />
);
