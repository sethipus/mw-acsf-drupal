import React from 'react';

import flexibleDriver from './flexible-driver.twig';
import flexibleDriverData from './flexible-driver.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Flexible Driver' };

export const flexibleDriverComponent = () => {
    return <div dangerouslySetInnerHTML={{ __html: flexibleDriver(flexibleDriverData) }} />;
};
