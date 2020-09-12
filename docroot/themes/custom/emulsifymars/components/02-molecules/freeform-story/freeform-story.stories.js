import React from 'react';

import freeformStory from './freeform-story.twig';
import freeformStoryData from './freeform-story.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Freeform Story' };

export const freeformStoryExample = () => {
    return <div dangerouslySetInnerHTML={{ __html: freeformStory(freeformStoryData) }} />;
};
