import {getBlockRegistrar} from '@givewp/form-builder/common/getWindowData';
import eventTicketsBlock from './EventTicketsBlock';

getBlockRegistrar().register(eventTicketsBlock.name, eventTicketsBlock.settings);
