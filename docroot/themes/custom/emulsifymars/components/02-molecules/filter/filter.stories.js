import React from 'react';

import filter from '../filter/filter.twig';
import filterData from '../filter/filter.yml';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Filter' };

export const filters = () => {
    return <div dangerouslySetInnerHTML={{ __html: filter(filterData) }} />;
};
