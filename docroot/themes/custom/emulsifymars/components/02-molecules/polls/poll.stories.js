
import React from 'react';
import poll from './poll-vote.twig';
import pollSubmitted from './poll-results.twig';
import pollData from './poll.yml';
import defaultLink from './../../01-atoms/links/defaultLink/defaultLink.twig'

export default { title: 'Molecules/Polling' };

export const pollingInitialStateThreeOptions = () => {
  pollData.vote_button = defaultLink({default_link_content: 'Submit'});
  pollData.choices = {
    left: 'left side',
    right: 'right side',
    center: 'center side'
  }
  return <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: poll(pollData) }} />;
};

export const pollingVoteSubmittedThreeOptions = () => {
  pollData.choices = {
    left: 'left side',
    right: 'right side',
    center: 'center side'
  }
  pollData.results = [
    {
      '#value': 'right',
      '#choice': 'right side',
      '#percentage': 25,
      key: 0
    },
    {
      '#value': 'left',
      '#choice': 'left side',
      '#percentage': 40,
      key: 1
    },
    {
      '#value': 'center',
      '#choice': 'center side',
      '#percentage': 35,
      key: 2
    }
  ]
  return <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: pollSubmitted(pollData) }} />
};

export const pollingInitialStateFourOptions = () => {
  pollData.vote_button = defaultLink({default_link_content: 'Submit'});
  pollData.choices = {
    left: 'left side',
    right: 'right side',
    center: 'center side',
    both: 'both sides'
  }
  return <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: poll(pollData) }} />;
};

export const pollingVoteSubmittedFourOptions = () => {
  pollData.choices = {
    left: 'left side',
    right: 'right side',
    center: 'center side',
    both: 'both sides'
  }
  pollData.results = [
    {
      '#value': 'right',
      '#choice': 'right side',
      '#percentage': 25,
      key: 0
    },
    {
      '#value': 'left',
      '#choice': 'left side',
      '#percentage': 40,
      key: 1
    },
    {
      '#value': 'center',
      '#choice': 'center side',
      '#percentage': 35,
      key: 2
    },
    {
      '#value': 'both',
      '#choice': 'both side',
      '#percentage': 35,
      key: 3
    }
  ]
  return <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: pollSubmitted(pollData) }} />
};

export const pollingInitialStateFiveOptions = () => {
  pollData.vote_button = defaultLink({default_link_content: 'Submit'});
  pollData.choices = {
    left: 'left side',
    right: 'right side',
    center: 'center side',
    both: 'both side',
    neither: 'neither side'
  }
  return <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: poll(pollData) }} />;
};

export const pollingVoteSubmittedFiveOptions = () => {
  pollData.choices = {
    left: 'left side',
    right: 'right side',
    center: 'center side',
    both: 'both side',
    neither: 'neither side'
  }
  pollData.results = [
    {
      '#value': 'right',
      '#choice': 'right side',
      '#percentage': 25,
      key: 0
    },
    {
      '#value': 'left',
      '#choice': 'left side',
      '#percentage': 20,
      key: 1
    },
    {
      '#value': 'center',
      '#choice': 'center side',
      '#percentage': 35,
      key: 2
    },
    {
      '#value': 'both',
      '#choice': 'both side',
      '#percentage': 10,
      key: 3
    },
    {
      '#value': 'neither',
      '#choice': 'neither side',
      '#percentage': 15,
      key: 4
    },
  ]
  return <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: pollSubmitted(pollData) }} />
};
