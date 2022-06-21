import React from 'react';

import status from './status.twig';

import statusData from './status.yml';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[GE 03] Alert Banner',
  argTypes: {
    message_list: {
      name: 'Message List',
      description: 'Diffrent types of messages.<b>	Manual, Max CC: 100</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const statusLayout = ({ message_list }) => (
  <div
    dangerouslySetInnerHTML={{
      __html: status({ ...statusData, message_list: message_list }),
    }}
  />
);
statusLayout.args = {
  message_list: statusData.message_list,
};
