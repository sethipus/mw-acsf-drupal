import React from 'react';

import flexibleFramer from './flexible-framer.twig';
import flexibleFramerData from './flexible-framer.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Flexible Framer' };

export const flexibleFramerComponent = () => {
    return <div dangerouslySetInnerHTML={{ __html: flexibleFramer(flexibleFramerData) }} />;
};
