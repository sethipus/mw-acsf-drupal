import React from 'react';

import freeformStory from './freeform-story.twig';
import freeformStoryLeftData from './freeform-story-left.yml';
import freeformStoryRightData from './freeform-story-right.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Freeform Story' };

export const freeformStoryExampleLeftAligned = () => {
    return <div dangerouslySetInnerHTML={{ __html: freeformStory(freeformStoryLeftData) }} />;
};
export const freeformStoryExampleRightAligned = () => {
    return <div dangerouslySetInnerHTML={{ __html: freeformStory(freeformStoryRightData) }} />;
};
