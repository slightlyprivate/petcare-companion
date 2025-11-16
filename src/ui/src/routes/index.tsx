import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import Home from '../pages/Home';
import LoginOtp from '../pages/LoginOtp';
import PetDetail from '../pages/PetDetail';
import Dashboard from '../pages/Dashboard';
import Purchases from '../pages/Purchases';
import AppShell from '../components/AppShell';
import RequireAuth from '../components/RequireAuth';

/**
 * Application routes configuration.
 */
const router = createBrowserRouter([
  {
    path: '/',
    element: <AppShell />,
    children: [
      { index: true, element: <Home /> },
      { path: 'login', element: <LoginOtp /> },
      { path: 'pets/:id', element: <PetDetail /> },
      {
        path: 'dashboard',
        element: (
          <RequireAuth>
            <Dashboard />
          </RequireAuth>
        ),
      },
      {
        path: 'purchases',
        element: (
          <RequireAuth>
            <Purchases />
          </RequireAuth>
        ),
      },
    ],
  },
]);

export default function AppRoutes() {
  return <RouterProvider router={router} />;
}
