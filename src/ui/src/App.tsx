import AppRouter from './routes';
import { NotificationsProvider } from './lib/notifications';

export default function App() {
  return (
    <NotificationsProvider>
      <AppRouter />
    </NotificationsProvider>
  );
}
