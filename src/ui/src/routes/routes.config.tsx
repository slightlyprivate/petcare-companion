import { lazy } from 'react';
import RequireAuth from '../components/RequireAuth';
import RouteError from '../components/RouteError';
import AppLayout from '../layouts/AppLayout';

const Home = lazy(() => import('../pages/Home'));
const LoginOtp = lazy(() => import('../pages/LoginOtp'));
const Dashboard = lazy(() => import('../pages/Dashboard'));
const PetDetail = lazy(() => import('../pages/PetDetail'));
const Purchases = lazy(() => import('../pages/Purchases'));
const ApiPlayground = lazy(() => import('../pages/ApiPlayground'));

export const routes = [
  {
    errorElement: <RouteError />,
    children: [
      {
        path: '/',
        element: <Home />,
        errorElement: <RouteError />,
      },
      {
        path: '/login',
        element: <LoginOtp />,
        errorElement: <RouteError />,
      },
      {
        element: (
          <RequireAuth>
            <AppLayout />
          </RequireAuth>
        ),
        errorElement: <RouteError />,
        children: [
          { path: '/dashboard', element: <Dashboard />, errorElement: <RouteError /> },
          { path: '/pets/:id', element: <PetDetail />, errorElement: <RouteError /> },
          { path: '/purchases', element: <Purchases />, errorElement: <RouteError /> },
          { path: '/sandbox', element: <ApiPlayground />, errorElement: <RouteError /> },
        ],
      },
    ],
  },
];
