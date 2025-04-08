import React from 'react';
import 'react-loading-skeleton/dist/skeleton.css';
import styles from './index.module.scss';
import {Spinner} from '@givewp/components';

const Basic = ({showHeader = true}) => {
  return (
      <div className={styles.skeletonContainer}>
          <Spinner />
      </div>
  );
};

export default Basic;
