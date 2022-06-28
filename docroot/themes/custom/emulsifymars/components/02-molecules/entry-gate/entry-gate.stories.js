import React from 'react';
import entryGate from './entry-gate.twig';
import entryGateData from './entry-gate.yml';
import { useEffect } from '@storybook/client-api';
import './entry-gate';

export default {
  title: 'Components/[GE 06] Entry Gate',
  argTypes: {
    heading: {
      control: {
        type: 'text',
      },
    },
  },
};

export const entryGateExample = ({ heading }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: entryGate({ ...entryGateData, entry_gate_heading: heading }),
      }}
    />
  );
};
entryGateExample.args ={
  heading:entryGateData.entry_gate_heading
}