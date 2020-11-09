import DesktopLayout from '../desktop-layout';
import MobileLayout from '../mobile-layout';
import { useWindowSize } from '../../hooks';

const App = () => {
	const { width } = useWindowSize();

	switch ( true ) {
		case width < 920 : {
			return <MobileLayout />;
		}
		default : {
			return <DesktopLayout />;
		}
	}
};
export default App;
