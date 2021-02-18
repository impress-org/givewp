import DesktopLayout from '../desktop-layout';
import MobileLayout from '../mobile-layout';
import Auth from '../auth';
import { useWindowSize } from '../../hooks';

const App = () => {
	const { width } = useWindowSize();

	return (
		<Auth>
			{ width < 920 ? (
				<MobileLayout />
			) : (
				<DesktopLayout />
			) }
		</Auth>
	);
};
export default App;
