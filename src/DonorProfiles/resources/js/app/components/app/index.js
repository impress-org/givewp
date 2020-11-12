import DesktopLayout from '../desktop-layout';
import MobileLayout from '../mobile-layout';
import { useWindowSize } from '../../hooks';

const App = () => {
	const { width } = useWindowSize();

	return width < 920 ? (
		<MobileLayout />
	) : (
		<DesktopLayout />
	);
};
export default App;
